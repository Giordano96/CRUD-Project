<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Inventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=box,favorite,view_cozy" />
    <link rel="stylesheet" href="styles_inventory.css">
</head>
<body>

<div class="header">
    My Inventory
</div>

<div class="inventory-wrapper">

    <!-- BARRA DI RICERCA IDENTICA AL DASHBOARD -->
    <div class="search-container">
        <input type="text" id="ingredientInput" class="search-input" placeholder="Search ingredient..." autocomplete="off">
        <div id="suggestionsList" class="suggestions-box"></div>
    </div>

    <!-- Form aggiunta -->
    <form method="POST" class="add-form" id="addForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="ingredient_id" id="selectedIngredientId" value="">

        <input type="date" name="expiration_date" class="add-date">

        <button type="submit" class="add-btn" id="addBtn" disabled>Add to Pantry</button>
    </form>

    <!-- Lista ingredienti -->
    <div class="ingredients-list">
        <?php if (empty($inventoryItems)): ?>
            <div style="text-align:center; padding:80px 20px; color:#999;">
                <p>Your pantry is empty</p>
                <p>Add ingredients using the search bar above</p>
            </div>
        <?php else: foreach ($inventoryItems as $item): ?>
            <div class="ingredient-row">
                <div class="ingredient-left">
                    <span class="ingredient-name"><?= htmlspecialchars($item['ingredient_name']) ?></span>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove from pantry?')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ingredient_id" value="<?= $item['ingredient_id'] ?>">
                        <button type="submit" class="delete-inline-btn">Delete</button>
                    </form>
                </div>
                <span class="expiry-value">
                    <?= $item['expiration_date'] ? date('d/m/Y', strtotime($item['expiration_date'])) : 'No expiration' ?>
                </span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Bottom Navigation -->
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

<script>
    const input = document.getElementById('ingredientInput');
    const list = document.getElementById('suggestionsList');
    const hiddenId = document.getElementById('selectedIngredientId');
    const addBtn = document.getElementById('addBtn');

    input.addEventListener('input', function() {
        const q = this.value.trim();
        if (q.length < 2) {
            list.innerHTML = '';
            list.style.display = 'none';
            hiddenId.value = '';
            addBtn.disabled = true;
            return;
        }

        fetch(`inventory.php?ajax=search&q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                list.innerHTML = '';
                if (!data.length) {
                    list.style.display = 'none';
                    return;
                }
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.textContent = item.name;
                    div.onclick = () => {
                        input.value = item.name;
                        hiddenId.value = item.id;
                        list.style.display = 'none';
                        addBtn.disabled = false;
                    };
                    list.appendChild(div);
                });
                list.style.display = 'block';
            });
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.search-container')) {
            list.style.display = 'none';
        }
    });

    document.getElementById('addForm').addEventListener('submit', e => {
        if (!hiddenId.value) {
            e.preventDefault();
            alert('Please select an ingredient from the list');
        }
    });
</script>

</body>
</html>