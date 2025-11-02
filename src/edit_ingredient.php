<?php
require 'DbConnector.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['ingredient_id'])) {
    header('Location: login.php');
}

$conn = new DbConnector('localhost', 'root', 'root', 'MySecretChef');

$ingredient_id = $_GET['ingredient_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT i.quantity, i.expiration_date, ing.name FROM inventory i JOIN ingredient ing ON i.ingredient_id = ing.id WHERE i.user_id = :user_id AND i.ingredient_id = :ingredient_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: inventory_view.php');
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Ingrediente - MySecretChef</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="main-container">
    <nav class="sidebar">
        <ul>
            <li><a href="home.php"><span>&#x1F3E0;</span> Home</a></li>
            <li><a href="handle_inventory.php" class="active"><span>&#x1F4CB;</span> Inventario</a></li>
            <li><a href="recipes.php"><span>&#x1F372;</span> Ricette</a></li>
            <li><a href="favorites.php"><span>&#x2764;&#xFE0F;</span> Preferiti</a></li>
            <li><a href="profile.php"><span>&#x1F464;</span> Profilo</a></li>
        </ul>
    </nav>
    <main>
        <h1>Modifica <?php echo htmlspecialchars($item['name']); ?></h1>
        <form method="post" action="handle_inventory.php" class="edit-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="ingredient_id" value="<?php echo $ingredient_id; ?>">
            <input name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" placeholder="Quantità" required>
            <input type="date" name="expiration_date" value="<?php echo htmlspecialchars($item['expiration_date']); ?>">
            <button type="submit">Salva Modifiche</button>
        </form>
    </main>
</div>
</body>
</html>
