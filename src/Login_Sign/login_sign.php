<?php
require "../utility/DbConnector.php";
session_start();

// Redirect se già loggato
if (isset($_SESSION["user_id"])) {
    header("Location: ../Dashboard/dashboard.php");
    exit;
}

// Genera CSRF token se non esiste
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION["csrf_token"];

// Variabili per il template
$error = "";
$success = "";           // <-- Nuovo: messaggio di successo
$active_tab = "login"; // default

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica CSRF
    if (empty($_POST["csrf_token"]) || !hash_equals($csrf_token, $_POST["csrf_token"])) {
        $error = "Invalid security token.";
    } else {
        $action = $_POST["action"] ?? "login";

        if ($action === "signup") {
            // ====================== REGISTRAZIONE ======================
            $active_tab = "signup";

            $username = trim($_POST["username"] ?? '');
            $email    = trim($_POST["email"] ?? '');
            $password = $_POST["password"] ?? '';
            $confirm  = $_POST["confirm_password"] ?? '';

            if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
                $error = "All fields are required.";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                try {
                    // Controlla se email già esiste
                    $check = $pdo->prepare("SELECT id FROM user WHERE email = :email");
                    $check->bindValue(':email', $email);
                    $check->execute();

                    if ($check->fetch()) {
                        $error = "This email is already registered.";
                    } else {
                        // Registrazione riuscita
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
                        $stmt->bindValue(':username', $username);
                        $stmt->bindValue(':email', $email);
                        $stmt->bindValue(':password', $hashed);
                        $stmt->execute();

                        $success = "Registration successful! You can now log in.";
                        $active_tab = "login";  // Torna al login

                        // Rigenera token di sicurezza
                        $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
                        $csrf_token = $_SESSION["csrf_token"];
                    }
                } catch (Exception $e) {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
        else {
            // ====================== LOGIN ======================
            $active_tab = "login";

            $email    = trim($_POST["email"] ?? '');
            $password = $_POST["password"] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Email and password are required.";
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT id, password FROM user WHERE email = :email");
                    $stmt->bindValue(':email', $email);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user["password"])) {
                        $_SESSION["user_id"] = $user["id"];
                        $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
                        header("Location: ../Dashboard/dashboard.php");
                        exit;
                    } else {
                        $error = "Incorrect email or password.";
                    }
                } catch (Exception $e) {
                    $error = "Login failed. Please try again.";
                }
            }
        }

        // In caso di errore nel login/signup, rigenera comunque il token
        if (empty($success)) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(16));
            $csrf_token = $_SESSION["csrf_token"];
        }
    }
}

// Passa le variabili al template
include "login_sign_view.php";
?>