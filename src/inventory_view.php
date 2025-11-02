<?php
require 'DbConnector.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

$conn = new DbConnector('localhost', 'root', 'root', 'MySecretChef');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = hash('sha256', rand());
}

$stmt = $conn->prepare("SELECT id, name FROM ingredient ORDER BY name");
$stmt->execute();
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT i.ingredient_id, ing.name, i.quantity, i.expiration_date FROM inventory i JOIN ingredient ing ON i.ingredient_id = ing.id WHERE i.user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il tuo Inventario - MySecretChef</title>
    <link rel="stylesheet" href="styleInventory.css">
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
        <h1>Il tuo Inventario</h1>
        <form method="post" action="handle_inventory.php" class="add-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="add">
            <input list="ingredients_list" name="ingredient_id" placeholder="Cerca ingrediente" required>
            <datalist id="ingredients_list">
                <?php foreach ($ingredients as $ingredient): ?>
                    <option value="<?php echo $ingredient['id']; ?>"><?php echo htmlspecialchars($ingredient['name']); ?></option>
                <?php endforeach; ?>
            </datalist>
            <input name="quantity" placeholder="Quantità" required>
            <input type="date" name="expiration_date" placeholder="Data di scadenza">
            <button type="submit">+ Aggiungi ingrediente</button>
        </form>
        <select class="category-filter">
            <option>Tutte le categorie</option>
        </select>
        <table class="inventory-table">
            <thead>
            <tr>
                <th>Nome ingrediente</th>
                <th>Quantità</th>
                <th>Scadenza</th>
                <th>Azioni</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($inventory)): ?>
                <tr><td colspan="4">Nessun ingrediente nell'inventario.</td></tr>
            <?php else: ?>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['expiration_date']); ?></td>
                        <td>
                            <a href="edit_inventory.php?ingredient_id=<?php echo $item['ingredient_id']; ?>">Modifica</a> /
                            <form method="post" action="handle_inventory.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="ingredient_id" value="<?php echo $item['ingredient_id']; ?>">
                                <button type="submit">Elimina</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>