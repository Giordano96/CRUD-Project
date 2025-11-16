<?php
// favorites.php - TOKEN RIUTILIZZABILE
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../DbConnector.php";
global $pdo;

session_start();

if (!isset($_SESSION["user_id"])) {
    if (isset($_GET['ajax'])) {
        http_response_code(401);
        exit(json_encode(['error' => 'Non autenticato']));
    }
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// --- CSRF TOKEN (una volta per sessione) ---
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION["csrf_token"];

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    try {
        if ($action === 'search') {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $per_page = 12;
            $offset = ($page - 1) * $per_page;

            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
            $count_stmt->execute([$user_id]);
            $total = (int)$count_stmt->fetchColumn();
            $pages = max(1, ceil($total / $per_page));

            $stmt = $pdo->prepare("SELECT r.id, r.name, r.image_url, COALESCE(r.prep_time, 0) AS prep_time FROM favorites f JOIN recipe r ON f.recipe_id = r.id WHERE f.user_id = ? ORDER BY r.name ASC LIMIT ? OFFSET ?");
            $stmt->execute([$user_id, $per_page, $offset]);
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            exit(json_encode([
                'recipes' => $recipes,
                'total' => $total,
                'pages' => $pages,
                'page' => $page
            ]));
        }

        if ($action === 'remove' && !empty($_POST['recipe_id'])) {
            if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                exit(json_encode(['error' => 'Invalid CSRF token']));
            }
            // TOKEN NON VIENE RIGENERATO → RIUTILIZZABILE

            $recipe_id = (int)$_POST['recipe_id'];
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$user_id, $recipe_id]);
            exit(json_encode(['success' => true]));
        }

    } catch (Exception $e) {
        http_response_code(500);
        exit(json_encode(['error' => $e->getMessage()]));
    }
}

include "favorites_page_view.php";
?>