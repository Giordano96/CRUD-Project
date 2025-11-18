<?php
// Inventory/inventory.php - Logica backend per la dispensa

ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../DbConnector.php";
global $pdo;

session_start();

// --- Controllo login ---
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Login_Sign/login_sign.php");
    exit;
}

$userId = $_SESSION["user_id"];

// --- CSRF Token unico per sessione (come dashboard) ---
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION["csrf_token"];

// -------------------------------------------------
// GESTIONE AJAX
// -------------------------------------------------
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    // Ricerca autocomplete ingredienti
    if ($_GET['ajax'] === 'search' && !empty($_GET['q'])) {
        $q = "%" . trim($_GET['q']) . "%";
        $stmt = $pdo->prepare("SELECT id, name FROM ingredient WHERE name LIKE :q ORDER BY name LIMIT 15");
        $stmt->bindValue(':q', $q);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
}

// -------------------------------------------------
// GESTIONE POST (add / delete)
// -------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST"){
    if (empty($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $action = $_POST['action'] ?? '';

    // === ELIMINA INGREDIENTE ===
    if ($action === 'delete' && !empty($_POST['ingredient_id'])) {
        $ingredientId = (int)$_POST['ingredient_id'];
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE user_id = :userId AND ingredient_id = :ingId");
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':ingId', $ingredientId);
        $stmt->execute();
    }

    // === AGGIUNGI INGREDIENTE ===
    if ($action === 'add' && !empty($_POST['ingredient_id'])) {
        $ingredientId = (int)$_POST['ingredient_id'];
        $expiration = $_POST['expiration_date'] ?? null;
        if ($expiration === '') $expiration = null;

        // Se esiste già → aggiorna scadenza, altrimenti inserisci
        $check = $pdo->prepare("SELECT 1 FROM inventory WHERE user_id = :userId AND ingredient_id = :ingId");
        $check->execute([':userId' => $userId, ':ingId' => $ingredientId]);

        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE inventory SET expiration_date = :exp WHERE user_id = :userId AND ingredient_id = :ingId");
        } else {
            $stmt = $pdo->prepare("INSERT INTO inventory (user_id, ingredient_id, expiration_date) VALUES (:userId, :ingId, :exp)");
        }
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':ingId', $ingredientId);
        $stmt->bindValue(':exp', $expiration);
        $stmt->execute();
    }

    // Redirect per evitare doppi invii
    header("Location: inventory.php");
    exit;
}

// -------------------------------------------------
// CARICA INVENTARIO UTENTE
// -------------------------------------------------
$stmt = $pdo->prepare("
    SELECT inv.ingredient_id, inv.expiration_date, i.name AS ingredient_name
    FROM inventory inv
    JOIN ingredient i ON inv.ingredient_id = i.id
    WHERE inv.user_id = :userId
    ORDER BY i.name ASC
");
$stmt->bindValue(':userId', $userId);
$stmt->execute();
$inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Passa alla vista
include "inventory_view.php";
?>