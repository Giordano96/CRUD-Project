<?php

require "DbConnector.php";

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: home.php");
    exit; // Ensure script stops after redirect
}

$csrf_token = hash("crc32b", rand());
$_SESSION["csrf_token"] = $csrf_token;

$connector = new DbConnector("127.0.0.1", "root", "", "mysecretchef");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid CSRF token.";
    } else {
        unset($_SESSION["csrf_token"]);

        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

        $statement = $connector->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
        $statement->bindParam(":username", $username);
        $statement->bindParam(":email", $email);
        $statement->bindParam(":password", $password);
        $statement->execute();

        $user_statement = $connector->prepare("SELECT id FROM user WHERE email = :email");
        $user_statement->bindParam(":email", $email);
        $user_statement->execute();
        $result = $user_statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION["user_id"] = $result["id"];
            header("Location: home.php");
            exit;
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}

include "signup_view.php";
