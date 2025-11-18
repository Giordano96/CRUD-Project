<?php
// inventory_view.php
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=box,favorite,view_cozy" />
    <title>Inventory - MySecretChef</title>
    <link rel="stylesheet" href="styles_inventory.css">
</head>
<body>

<div class="header">
    MySecretChef
</div>

<div class="inventory-wrapper">

    <div class="search-bar">
        <input type="text" placeholder="Search ingredients">
    </div>

    <form class="add-form" method="POST" action="add_ingredient.php">
        <input type="date" name="expiry_date" class="add-date" required>
        <button class="add-btn" type="submit">Add</button>
    </form>

    <div class="table-header">
        <span>Ingredients</span>
        <span class="expiry-title">Expiring date</span>
    </div>

    <div class="ingredients-list">
        <?php
        $ingredients = [
            ["name" => "Tomatoes", "expiry" => "03/12/2025"],
            ["name" => "Milk", "expiry" => "10/11/2025"]
        ];

        foreach ($ingredients as $item): ?>

        <div class="ingredient-row">
            <div class="ingredient-left">
                <span class="ingredient-name"><?= $item["name"] ?></span>
                <button class="delete-inline-btn">Delete</button>
            </div>
            <span class="expiry-value"><?= $item["expiry"] ?></span>
        </div>

        <?php endforeach; ?>
    </div>
</div>

<div class="bottom-nav">
    <div class="nav-item" onclick="location.href='../Dashboard/dashboard.php'">
        <span class="material-symbols-outlined">view_cozy</span>
        Home
    </div>
    <div class="nav-item active">
        <span class="material-symbols-outlined">box</span>
        Inventory
    </div>
    <div class="nav-item" onclick="location.href='../Favorites/favorites.php'">
        <span class="material-symbols-outlined">favorite</span>
        Favorites
    </div>
</div>

</body>
</html>
