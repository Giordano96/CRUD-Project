<?php
require "../DbConnector.php";

session_start();

// Redirect se già loggato
if (isset($_SESSION["user_id"])) {
    header("Location: ../Dashboard/dashboard.php");
    exit;
}

// === GENERA CSRF TOKEN SOLO SE NON ESISTE ===
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(16)); // Più sicuro di crc32b + rand()
}

$csrf_token = $_SESSION["csrf_token"];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica CSRF
    if (empty($_POST["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
        $error = "Invalid CSRF token.";
    } else {
        // Token valido → rimuovilo per sicurezza (opzionale, ma consigliato)
        unset($_SESSION["csrf_token"]);

        $email = trim($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Email and password are required.";
        } else {
            try {
                $statement = $pdo->prepare("SELECT id, password FROM user WHERE email = :email");
                $statement->bindParam(":email", $email, PDO::PARAM_STR);
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                if ($result && password_verify($password, $result["password"])) {
                    $_SESSION["user_id"] = $result["id"];
                    // Rigenera token per la prossima richiesta
                    $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
                    header("Location: ../Dashboard/dashboard.php");
                    exit;
                } else {
                    $error = "Invalid email or password.";
                    // Rigenera token anche in caso di errore
                    $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
                }
            } catch (Exception $e) {
                $error = "Database error. Please try again.";
            }
        }
    }
}

// === PASSA IL TOKEN ALLA VISTA (senza rigenerarlo) ===
include "login_view.php";