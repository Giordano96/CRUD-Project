<?php
// dashboard.php - Versione FULL AJAX + RICERCA "ALMENOUNO"
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../DbConnector.php";
global $pdo;

session_start();

// --- AUTENTICAZIONE ---
if (!isset($_SESSION["user_id"])) {
    if (isset($_GET['ajax'])) {
        http_response_code(401);
        exit(json_encode(['error' => 'Non autenticato']));
    }
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$_SESSION['selected_ingredients'] ??= [];

// --- ENDPOINT AJAX ---
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    try {
        // 1. SUGGERIMENTI
        if ($action === 'suggest' && !empty($_GET['q'])) {
            $q = "%" . trim($_GET['q']) . "%";
            $stmt = $pdo->prepare("SELECT name FROM ingredient WHERE name LIKE ? ORDER BY name LIMIT 10");
            $stmt->execute([$q]);
            exit(json_encode($stmt->fetchAll(PDO::FETCH_COLUMN)));
        }

        // 2. AGGIUNGI INGREDIENTE
        if ($action === 'add' && !empty($_POST['ingredient'])) {
            $name = trim($_POST['ingredient']);
            $stmt = $pdo->prepare("SELECT 1 FROM ingredient WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch() && !in_array($name, $_SESSION['selected_ingredients'])) {
                $_SESSION['selected_ingredients'][] = $name;
            }
            exit(json_encode(['success' => true, 'ingredients' => $_SESSION['selected_ingredients']]));
        }

        // 3. RIMUOVI INGREDIENTE
        if ($action === 'remove' && !empty($_POST['ingredient'])) {
            $name = $_POST['ingredient'];
            $_SESSION['selected_ingredients'] = array_values(array_filter(
                $_SESSION['selected_ingredients'],
                fn($i) => $i !== $name
            ));
            exit(json_encode(['success' => true, 'ingredients' => $_SESSION['selected_ingredients']]));
        }

        // 4. CARICA DA INVENTARIO
        if ($action === 'load_inventory') {
            $stmt = $pdo->prepare("
                SELECT i.name 
                FROM inventory inv 
                JOIN ingredient i ON inv.ingredient_id = i.id 
                WHERE inv.user_id = ? AND inv.quantity > 0
                ORDER BY i.name
            ");
            $stmt->execute([$user_id]);
            $inventory = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $_SESSION['selected_ingredients'] = array_values(array_unique(
                array_merge($_SESSION['selected_ingredients'], $inventory)
            ));

            exit(json_encode(['success' => true, 'ingredients' => $_SESSION['selected_ingredients']]));
        }

        // 5. CERCA RICETTE - ALMENO UN INGREDIENTE
        if ($action === 'search') {
            $ingredients = $_SESSION['selected_ingredients'];
            if (empty($ingredients)) {
                exit(json_encode(['recipes' => [], 'total' => 0, 'pages' => 1, 'page' => 1]));
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $per_page = 15;
            $offset = ($page - 1) * $per_page;
            $placeholders = str_repeat('?,', count($ingredients) - 1) . '?';

            // Conta totale ricette con almeno un ingrediente
            $count_stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT r.id)
                FROM recipe r
                JOIN recipe_ingredient ri ON r.id = ri.recipe_id
                JOIN ingredient i ON ri.ingredient_id = i.id
                WHERE i.name IN ($placeholders)
            ");
            $count_stmt->execute($ingredients);
            $total = (int)$count_stmt->fetchColumn();
            $pages = max(1, ceil($total / $per_page));

            // Ricette
            $stmt = $pdo->prepare("
                SELECT DISTINCT r.id, r.name, r.image_url, COALESCE(r.prep_time, 0) AS prep_time
                FROM recipe r
                JOIN recipe_ingredient ri ON r.id = ri.recipe_id
                JOIN ingredient i ON ri.ingredient_id = i.id
                WHERE i.name IN ($placeholders)
                ORDER BY r.id DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute(array_merge($ingredients, [$per_page, $offset]));
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            exit(json_encode([
                'recipes' => $recipes,
                'total' => $total,
                'pages' => $pages,
                'page' => $page
            ]));
        }

    } catch (Exception $e) {
        http_response_code(500);
        exit(json_encode(['error' => $e->getMessage()]));
    }
}

// --- DATI INIZIALI ---
$stmt = $pdo->prepare("SELECT username FROM user WHERE id = ?");
$stmt->execute([$user_id]);
$username = $stmt->fetchColumn() ?: "Utente";

$user_ingredients = $_SESSION['selected_ingredients'];
sort($user_ingredients);

include "dashboard_view.php";
?>