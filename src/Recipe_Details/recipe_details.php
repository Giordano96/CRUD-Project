<?php
require "../utility/DbConnector.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipe_id <= 0) {
    die('ID ricetta non valido');
}

$user_id = $_SESSION['user_id'];

// ==================== GESTIONE AJAX TOGGLE FAVORITE ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_favorite') {
    header('Content-Type: application/json');

    $recipe_id_ajax = (int)($_POST['recipe_id'] ?? 0);
    if ($recipe_id_ajax <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID non valido']);
        exit;
    }

    try {
        $check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $check->execute([$user_id, $recipe_id_ajax]);
        $exists = $check->fetchColumn();

        if ($exists) {
            $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?")
                ->execute([$user_id, $recipe_id_ajax]);
            $added = false;
        } else {
            $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)")
                ->execute([$user_id, $recipe_id_ajax]);
            $added = true;
        }

        echo json_encode(['success' => true, 'added' => $added]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Errore database']);
    }
    exit;
}

// ==================== CARICAMENTO DETTAGLI RICETTA ====================
$stmt = $pdo->prepare("
    SELECT 
        r.name AS recipe_name,
        GROUP_CONCAT(DISTINCT CONCAT(ri.quantity, ' ', ri.unit, ' ', i.name) 
            ORDER BY i.name SEPARATOR ', ') AS ingredients,
        r.prep_time,
        r.image_url,
        r.instructions,
        GROUP_CONCAT(DISTINCT CONCAT(n.name, ': ', rn.value, ' g') 
            ORDER BY n.id SEPARATOR ', ') AS nutrients
    FROM recipe r
    LEFT JOIN recipe_ingredient ri ON r.id = ri.recipe_id
    LEFT JOIN ingredient i ON ri.ingredient_id = i.id
    LEFT JOIN recipe_nutrient rn ON r.id = rn.recipe_id
    LEFT JOIN nutrient n ON rn.nutrient_id = n.id
    WHERE r.id = ?
    GROUP BY r.id
");
$stmt->execute([$recipe_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die('Ricetta non trovata');
}

$ingredients = $item['ingredients'] ? explode(', ', $item['ingredients']) : [];
$nutrients   = $item['nutrients']   ? explode(', ', $item['nutrients'])   : [];

// Inventario utente
$stmt = $pdo->prepare("
    SELECT i.name 
    FROM inventory inv
    JOIN ingredient i ON inv.ingredient_id = i.id
    WHERE inv.user_id = ?
");
$stmt->execute([$user_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

// Controllo se è già nei preferiti
$is_favorite = false;
$check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
$check->execute([$user_id, $recipe_id]);
$is_favorite = $check->rowCount() > 0;

// ==================== VISTA ====================
require 'recipe_details_view.php';