<?php
$host = 'localhost';
$dbname = 'MySecretChef';
$user = 'root';
$password = '';

try {
    global $pdo;
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // In produzione, logga l'errore, non mostrarlo
    error_log("Errore connessione DB: " . $e->getMessage());
    die("Errore di connessione al database. Riprova più tardi.");
}
?>