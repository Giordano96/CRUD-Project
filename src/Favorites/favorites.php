<?php
// favorites.php - User's favorite recipes page

ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../DbConnector.php";
global $pdo;

session_start();

// --- Authentication check ---
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
        // 1. Load favorite recipes with pagination
        if ($action === 'search') {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 12;
            $offset = ($page - 1) * $perPage;

            // Count total
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :userId");
            $countStmt->bindValue(':userId', $userId);
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();
            $totalPages = max(1, ceil($total / $perPage));

            // Fetch current page
            $stmt = $pdo->prepare("
                SELECT r.id, r.name, r.image_url, COALESCE(r.prep_time, 0) AS prep_time 
                FROM favorites f 
                JOIN recipe r ON f.recipe_id = r.id 
                WHERE f.user_id = :userId 
                ORDER BY r.name ASC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':userId', $userId);
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

        // 2. Remove recipe from favorites
        if ($action === 'remove' && !empty($_POST['recipe_id'])) {
            if (empty($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }

            $recipeId = (int)$_POST['recipe_id'];
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = :userId AND recipe_id = :recipeId");
            $stmt->bindValue(':userId', $userId);
            $stmt->bindValue(':recipeId', $recipeId);
            $stmt->execute();

            echo json_encode(['success' => true]);
            exit;
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

// Load the view
include "favorites_page_view.php";
?>