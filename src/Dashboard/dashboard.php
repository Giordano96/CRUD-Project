<?php
// DEBUG: Abilita errori
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "../DbConnector.php";
global $pdo; // CRITICO: $pdo ora è accessibile

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: Login/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// === PAGINAZIONE ===
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// === AGGIUNGI INGREDIENTE ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ingredient"])) {
    $ingredient_name = trim($_POST["ingredient"]);

    if (!empty($ingredient_name)) {
        try {
            // Cerca ID ingrediente
            $stmt = $pdo->prepare("SELECT id FROM ingredient WHERE name = :name");
            $stmt->bindParam(":name", $ingredient_name, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result) {
                $ingredient_id = $result["id"];

                // Aggiungi o incrementa quantità
                $stmt = $pdo->prepare("
                    INSERT INTO inventory (user_id, ingredient_id, quantity) 
                    VALUES (:user_id, :ingredient_id, 1)
                    ON DUPLICATE KEY UPDATE quantity = quantity + 1
                ");
                $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam(":ingredient_id", $ingredient_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Errore aggiunta ingrediente: " . $e->getMessage());
        }
    }
    // Redirect per evitare duplicati
    header("Location: dashboard.php?page=$page");
    exit;
}

// === AVVIA RICERCA RICETTE ===
$search_triggered = ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["search"]));
$suggested_recipes = [];
$total_recipes = 0;
$total_pages = 1;

if ($search_triggered) {
    try {
// === CONTA RICETTE TOTALI ===
        $count_stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT r.id) 
    FROM recipe r
    JOIN recipe_ingredient ri ON r.id = ri.recipe_id
    JOIN inventory inv ON ri.ingredient_id = inv.ingredient_id
    WHERE inv.user_id = :user_id AND inv.quantity > 0
");
        $count_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $count_stmt->execute();
        $total_recipes = (int)$count_stmt->fetchColumn();
        $total_pages = max(1, ceil($total_recipes / $per_page));

// === CARICA RICETTE PAGINATE ===
        $stmt = $pdo->prepare("
    SELECT DISTINCT r.id, r.name, r.image_url, COALESCE(r.prep_time, 0) AS prep_time
    FROM recipe r
    JOIN recipe_ingredient ri ON r.id = ri.recipe_id
    JOIN inventory inv ON ri.ingredient_id = inv.ingredient_id
    WHERE inv.user_id = :user_id AND inv.quantity > 0
    ORDER BY r.id DESC
    LIMIT :limit OFFSET :offset
");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $per_page, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        $suggested_recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Errore ricerca ricette: " . $e->getMessage());
    }
}
// === CARICA SEMPRE: username + ingredienti utente ===
try {
    $stmt = $pdo->prepare("SELECT username FROM user WHERE id = :id");
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();
    $username = $user["username"] ?? "Utente";

    $stmt = $pdo->prepare("
        SELECT DISTINCT i.name 
        FROM inventory inv 
        JOIN ingredient i ON inv.ingredient_id = i.id 
        WHERE inv.user_id = :user_id AND inv.quantity > 0
        ORDER BY i.name
    ");
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user_ingredients = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $username = "Utente";
    $user_ingredients = [];
    error_log("Errore caricamento dati utente: " . $e->getMessage());
}

include "dashboard_view.php";
?>