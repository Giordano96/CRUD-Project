<?php
require 'DbConnector.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

$conn = new DbConnector('localhost', 'root', 'root', 'MySecretChef');

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token.');
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $ingredient_id = $_POST['ingredient_id'];
        $quantity      = $_POST['quantity'];
        $expiration_date = $_POST['expiration_date'] ?: null;

        $stmt = $conn->prepare("INSERT INTO inventory (user_id, ingredient_id, quantity, expiration_date) VALUES (:user_id, :ingredient_id, :quantity, :expiration_date)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_STR);
        $stmt->bindParam(':expiration_date', $expiration_date, PDO::PARAM_STR);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $ingredient_id = $_POST['ingredient_id'];

        $stmt = $conn->prepare("DELETE FROM inventory WHERE user_id = :user_id AND ingredient_id = :ingredient_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
        $stmt->execute();
    } elseif ($action === 'update') {
        $ingredient_id = $_POST['ingredient_id'];
        $quantity      = $_POST['quantity'];
        $expiration_date = $_POST['expiration_date'] ?: null;

        $stmt = $conn->prepare("UPDATE inventory SET quantity = :quantity, expiration_date = :expiration_date WHERE user_id = :user_id AND ingredient_id = :ingredient_id");
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_STR);
        $stmt->bindParam(':expiration_date', $expiration_date, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

header('Location: inventory_view.php');