<?php
require "../DbConnector.php";

header('Content-Type: application/json');

if (!isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['q']);
if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

global $pdo;

try {
    $stmt = $pdo->prepare("
        SELECT name 
        FROM ingredient 
        WHERE name LIKE :query 
        ORDER BY name 
        LIMIT 10
    ");
    $like = "%$query%";
    $stmt->bindParam(":query", $like, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Errore API ingredienti: " . $e->getMessage());
    echo json_encode([]);
}
?>