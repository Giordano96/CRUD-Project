<?php
require "../DbConnector.php";

session_start();

// Redirect se già loggato
if (isset($_SESSION["user_id"])) {
    header("Location: ../login.php"); // Percorso relativo corretto
    exit;
}

// === GENERA CSRF TOKEN SOLO SE NON ESISTE ===
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(16)); // Token forte
}
$csrf_token = $_SESSION["csrf_token"];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica CSRF
    if (empty($_POST["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
        $error = "Invalid CSRF token.";
    } else {
        // Token valido → rimuovilo
        unset($_SESSION["csrf_token"]);

        $username = trim($_POST["username"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $password_plain = $_POST["password"] ?? '';

        // Validazione base
        if (empty($username) || empty($email) || empty($password_plain)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif (strlen($password_plain) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            try {
                // Controlla se email esiste già
                $check = $pdo->prepare("SELECT id FROM user WHERE email = :email");
                $check->bindParam(":email", $email);
                $check->execute();
                if ($check->fetch()) {
                    $error = "Email already registered.";
                } else {
                    $password = password_hash($password_plain, PASSWORD_BCRYPT);

                    $statement = $pdo->prepare(
                        "INSERT INTO user (username, email, password) VALUES (:username, :email, :password)"
                    );
                    $statement->bindParam(":username", $username);
                    $statement->bindParam(":email", $email);
                    $statement->bindParam(":password", $password);
                    $statement->execute();

                    // Recupera ID utente
                    $user_statement = $pdo->prepare("SELECT id FROM user WHERE email = :email");
                    $user_statement->bindParam(":email", $email);
                    $user_statement->execute();
                    $result = $user_statement->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        $_SESSION["user_id"] = $result["id"];
                        // Rigenera token per sicurezza
                        $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
                        header("Location: ../Dashboard/dashboard.php");
                        exit;
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Database error. Please try again.";
                error_log("Signup error: " . $e->getMessage());
            }
        }
        // Rigenera token in caso di errore
        $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
    }
}

// === Includi vista con token stabile ===
include "signup_view.php";