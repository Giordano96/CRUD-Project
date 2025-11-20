<?php
require '../DbConnector.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Leggi e valida l'ID dalla URL
$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipe_id <= 0) {
    die('ID ricetta non valido');
}

$user_id = $_SESSION['user_id'];
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

// Inventario dell'utente (user_id 1 per ora - da rendere dinamico)
$user_id = 1; // <-- $_SESSION['user_id'] quando autenticato

$stmt = $pdo->prepare("
    SELECT i.name AS ingrediente
    FROM inventory inv
    JOIN ingredient i ON inv.ingredient_id = i.id
    WHERE inv.user_id = ?
");
$stmt->execute([$user_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);  // solo nomi ingredienti in inventario

// Passa tutto alla vista
require 'recipe_details_view.php';