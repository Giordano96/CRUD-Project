<?php
// dashboard.php - Pagina principale dell'app MySecretChef (Home)

require "../utility/DbConnector.php"; // Connessione al database

session_start();

// --- Controllo autenticazione utente ---
if (!isset($_SESSION["user_id"])) {
    if (isset($_GET['ajax'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }
    header("Location: ../Login_Sign/login_sign.php");
    exit;
}

$userId = $_SESSION["user_id"];

// --- Gestione ingredienti selezionati (sessione) ---
if (!isset($_SESSION['selected_ingredients'])) {
    $_SESSION['selected_ingredients'] = [];
}
$selectedIngredients = &$_SESSION['selected_ingredients'];

// --- Generazione CSRF Token (uno per sessione) ---
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION["csrf_token"];

// -------------------------------------------------
// GESTORE RICHIESTE AJAX
// -------------------------------------------------
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    try {
        // 1. Suggerimenti autocomplete ingredienti
        if ($action === 'suggest' && !empty($_GET['q'])) {
            $query = "%" . trim($_GET['q']) . "%";
            $stmt = $pdo->prepare("SELECT name FROM ingredient WHERE name LIKE :query ORDER BY name LIMIT 10");
            $stmt->bindValue(':query', $query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode($results);
            exit;
        }

        // Validazione CSRF per tutte le azioni POST
        $postActions = ['add', 'remove', 'load_inventory'];
        if (in_array($action, $postActions)) {
            if (empty($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
        }

        // 2. Aggiungi ingrediente digitato manualmente
        if ($action === 'add' && !empty($_POST['ingredient'])) {
            $name = trim($_POST['ingredient']);

            $stmt = $pdo->prepare("SELECT 1 FROM ingredient WHERE name = :name");
            $stmt->bindValue(':name', $name);
            $stmt->execute();
            if ($stmt->fetchColumn() && !in_array($name, $selectedIngredients)) {
                $selectedIngredients[] = $name;
            }

            echo json_encode([
                'success' => true,
                'ingredients' => $selectedIngredients
            ]);
            exit;
        }

        // 3. Rimuovi ingrediente dalla selezione
        if ($action === 'remove' && !empty($_POST['ingredient'])) {
            $name = $_POST['ingredient'];
            $selectedIngredients = array_values(array_filter($selectedIngredients, fn($i) => $i !== $name));

            echo json_encode([
                'success' => true,
                'ingredients' => $selectedIngredients
            ]);
            exit;
        }

        // 4. Carica tutti gli ingredienti dall'inventario utente
        if ($action === 'load_inventory') {
            $stmt = $pdo->prepare("
                SELECT i.name 
                FROM inventory inv
                JOIN ingredient i ON inv.ingredient_id = i.id 
                WHERE inv.user_id = :userId
                ORDER BY i.name
            ");
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
            $inventory = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Unisce inventario con ingredienti già selezionati (senza duplicati)
            $selectedIngredients = array_values(array_unique(array_merge($selectedIngredients, $inventory)));

            echo json_encode([
                'success' => true,
                'ingredients' => $selectedIngredients
            ]);
            exit;
        }

        // 5. Ricerca ricette basate sugli ingredienti selezionati
        if ($action === 'search') {
            if (empty($selectedIngredients)) {
                echo json_encode(['recipes' => [], 'total' => 0, 'pages' => 1, 'page' => 1]);
                exit;
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 15;
            $offset = ($page - 1) * $perPage;

            // Costruisce dinamicamente la clausola IN con placeholder nominati
            $inPlaceholders = '';
            $params = [];
            foreach ($selectedIngredients as $index => $ingredient) {
                $key = ":ing$index";
                $inPlaceholders .= ($index === 0 ? '' : ', ') . $key;
                $params[$key] = $ingredient;
            }

            // Conta totale ricette trovate
            $countSql = "
                SELECT COUNT(DISTINCT r.id) 
                FROM recipe r 
                JOIN recipe_ingredient ri ON r.id = ri.recipe_id 
                JOIN ingredient i ON ri.ingredient_id = i.id 
                WHERE i.name IN ($inPlaceholders)
            ";
            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();
            $totalPages = max(1, ceil($total / $perPage));

            // Recupera ricette per la pagina corrente
            $sql = "
                SELECT DISTINCT r.id, r.name, r.image_url, COALESCE(r.prep_time, 0) AS prep_time
                FROM recipe r 
                JOIN recipe_ingredient ri ON r.id = ri.recipe_id 
                JOIN ingredient i ON ri.ingredient_id = i.id 
                WHERE i.name IN ($inPlaceholders)
                ORDER BY r.id DESC 
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'recipes' => $recipes,
                'total'   => $total,
                'pages'   => $totalPages,
                'page'    => $page
            ]);
            exit;
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

// Prepara dati per la vista HTML
$currentIngredients = $selectedIngredients;
sort($currentIngredients);

// Include la vista (HTML + JS)
include "dashboard_view.php";
?>