<?php

require "DbConnector.php";

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: home.php");
    exit; // Ensure script stops after redirect
}

$csrf_token = hash("crc32b", rand());
$_SESSION["csrf_token"] = $csrf_token;

$connector = new DbConnector('localhost', 'root', 'root', 'MySecretChef');
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid CSRF token.";
    } else {
        unset($_SESSION["csrf_token"]);

        $email = $_POST["email"];
        $password = $_POST["password"];

        $statement = $connector->prepare("SELECT id, password FROM user WHERE email = :email");
        $statement->bindParam(":email", $email);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result["password"])) {
            $_SESSION["user_id"] = $result["id"];
            header("Location: home.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}


include "login_view.php";
