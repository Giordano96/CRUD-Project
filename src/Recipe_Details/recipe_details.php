<?php
require "../utility/DbConnector.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Gestione AJAX per il cuore (deve stare all'inizio!)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    header('Content-Type: application/json');

    $recipe_id = (int)($_POST['recipe_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($recipe_id <= 0) {
        echo json_encode(['success' => false]);
        exit;
    }

    // Controlla se esiste già
    $check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $check->execute([$user_id, $recipe_id]);
    $exists = $check->fetchColumn() !== false;

    if ($exists) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?")
            ->execute([$user_id, $recipe_id]);
        $new_state = false;
    } else {
        $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)")
            ->execute([$user_id, $recipe_id]);
        $new_state = true;
    }

    echo json_encode(['success' => true, 'is_favorite' => $new_state]);
    exit;
}

// --- Qui continua la logica normale della pagina ---
$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipe_id <= 0) {
    die('ID ricetta non valido');
}

$user_id = $_SESSION['user_id'];

// Verifica se è già nei preferiti
$is_favorite = false;
$check_fav = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?");
$check_fav->execute([$user_id, $recipe_id]);
if ($check_fav->fetchColumn()) {
    $is_favorite = true;
}

// Query principale: dettagli ricetta + ingredienti + nutrienti
$stmt = $pdo->prepare("
    SELECT 
        r.name AS recipe_name,
        GROUP_CONCAT(DISTINCT CONCAT(ri.quantity, ' ', ri.unit, ' ', i.name) 
            ORDER BY i.name SEPARATOR ', ') AS ingredients,
        r.prep_time AS prep_time,
        r.image_url AS image_url,
        r.instructions AS instructions,
        GROUP_CONCAT(DISTINCT CONCAT(n.name, ': ', rn.value, 'g') 
            ORDER BY n.id SEPARATOR ', ') AS nutrients
    FROM recipe r
    LEFT JOIN recipe_ingredient ri ON r.id = ri.recipe_id
    LEFT JOIN ingredient i ON ri.ingredient_id = i.id
    LEFT JOIN recipe_nutrient rn ON r.id = rn.recipe_id
    LEFT JOIN nutrient n ON rn.nutrient_id = n.id
    WHERE r.id = ?
    GROUP BY r.id, r.name, r.prep_time, r.image_url, r.instructions
");

$stmt->execute([$recipe_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die('Ricetta non trovata');
}

// Suddivide ingredienti e nutrienti
$ingredients = $item['ingredients'] ? explode(', ', $item['ingredients']) : [];
$nutrients   = $item['nutrients']   ? explode(', ', $item['nutrients'])   : [];

// Inventario utente
$stmt = $pdo->prepare("
    SELECT i.name AS ingrediente
    FROM inventory inv
    JOIN ingredient i ON inv.ingredient_id = i.id
    WHERE inv.user_id = ?
");
$stmt->execute([$user_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

// Passa tutto alla vista
require 'recipe_details_view.php';