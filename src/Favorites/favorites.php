<?php
// favorites.php - SENZA JSON, solo PHP + ricaricamento pagina
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../DbConnector.php";
global $pdo;

session_start();

// --- AUTENTICAZIONE ---
if (!isset($_SESSION["user_id"])) {
    header("Location: Login/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// --- AZIONI ---
$action = $_GET['action'] ?? '';
$recipe_id = (int)($_GET['recipe_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Rimuovi dai preferiti
if ($action === 'remove' && $recipe_id > 0) {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $stmt->execute([$user_id, $recipe_id]);
    // Ricarica la stessa pagina
    header("Location: favorites.php?page=$page");
    exit;
}

// --- Conta totale preferiti ---
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total = (int)$count_stmt->fetchColumn();
$pages = max(1, ceil($total / $per_page));

// --- Carica ricette preferite ---
$stmt = $pdo->prepare("
    SELECT r.id, r.name, r.image_url, COALESCE(r.prep_time, 0) AS prep_time
    FROM favorites f
    JOIN recipe r ON f.recipe_id = r.id
    WHERE f.user_id = ?
    ORDER BY r.name ASC
    LIMIT ? OFFSET ?
");
$stmt->execute([$user_id, $per_page, $offset]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Includi vista ---
include "favorites_view.php";
?>