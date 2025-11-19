<?php
// dashboard.php - Main page of MySecretChef

ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../DbConnector.php";
global $pdo;

session_start();

// --- Check if user is logged in ---
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

// List of currently selected ingredients (stored in session)
if (!isset($_SESSION['selected_ingredients'])) {
    $_SESSION['selected_ingredients'] = [];
}
$selectedIngredients = &$_SESSION['selected_ingredients'];

// --- CSRF Token (one per session) ---
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION["csrf_token"];

// -------------------------------------------------
// AJAX HANDLER
// -------------------------------------------------
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    try {
        // 1. Autocomplete suggestions
        if ($action === 'suggest' && !empty($_GET['q'])) {
            $query = "%" . trim($_GET['q']) . "%";
            $stmt = $pdo->prepare("SELECT name FROM ingredient WHERE name LIKE :query ORDER BY name LIMIT 10");
            $stmt->bindValue(':query', $query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode($results);
            exit;
        }

        // All POST actions require CSRF validation
        $postActions = ['add', 'remove', 'load_inventory'];
        if (in_array($action, $postActions)) {  // CORRETTO: era "recurse(in_array(...))" → errore!
            if (empty($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
        }

        // 2. Add manually typed ingredient
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

        // 3. Remove ingredient from selection
        if ($action === 'remove' && !empty($_POST['ingredient'])) {
            $name = $_POST['ingredient'];
            $selectedIngredients = array_values(array_filter($selectedIngredients, fn($i) => $i !== $name));

            echo json_encode([
                'success' => true,
                'ingredients' => $selectedIngredients
            ]);
            exit;
        }

        // 4. Load all ingredients from user's inventory
        if ($action === 'load_inventory') {
            $stmt = $pdo->prepare("
                SELECT i.name 
                FROM inventory inv
                JOIN ingredient i ON inv.ingredient_id = i.id 
                WHERE inv.user_id = :userId AND inv.quantity > 0
                ORDER BY i.name
            ");
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
            $inventory = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $selectedIngredients = array_values(array_unique(array_merge($selectedIngredients, $inventory)));

            echo json_encode([
                'success' => true,
                'ingredients' => $selectedIngredients
            ]);
            exit;
        }

        // 5. Search recipes using selected ingredients
        if ($action === 'search') {
            if (empty($selectedIngredients)) {
                echo json_encode(['recipes' => [], 'total' => 0, 'pages' => 1, 'page' => 1]);
                exit;
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 15;
            $offset = ($page - 1) * $perPage;

            // Build dynamic IN clause with named placeholders
            $inPlaceholders = '';
            $params = [];
            foreach ($selectedIngredients as $index => $ingredient) {
                $key = ":ing$index";
                $inPlaceholders .= ($index === 0 ? '' : ', ') . $key;
                $params[$key] = $ingredient;
            }

            // Count total matching recipes
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

            // Fetch recipes for current page
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

// Prepare data for view
$currentIngredients = $selectedIngredients;
sort($currentIngredients);

include "dashboard_view.php";
?>