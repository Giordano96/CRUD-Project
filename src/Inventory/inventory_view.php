<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Inventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="styles_inventory.css">
    <link href='https://fonts.googleapis.com/css?family=Plus Jakarta Sans' rel='stylesheet'>
</head>
<body>

<div class="header">
    <div class="logo-container">
        <img src="../img/MySecretChef_Logo.png" alt="My Secret Chef" onclick="location.href='../Dashboard/dashboard.php'">
    </div>
    <div class="page-title">My Inventory</div>
    <div class="logout-icon" onclick="location.href='../utility/logout.php'">
        <span class="material-symbols-outlined">logout</span>
    </div>
</div>

<div class="content">

    <!-- BARRA DI RICERCA (identica alla dashboard) -->
    <div class="search-container">
        <input type="text" id="ingredientInput" class="search-input" placeholder="Enter ingredients..." autocomplete="off">
        <div id="suggestionsList" class="suggestions-box"></div>
    </div>

    <!-- FORM DATA + BOTTONE ADD → SEMPRE VISIBILE -->
    <div class="add-ingredient-section">
        <div class="expiration-container">
            <label for="expirationDate" class="expiration-label">Expiration date (optional)</label>
            <input type="date" id="expirationDate" class="add-date">
        </div>

        <div class="buttons">
            <button id="addButton" class="button" disabled>Add Ingredient</button>
        </div>
    </div>

    <!-- Form nascosto per invio POST -->
    <form method="POST" id="hiddenAddForm" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="ingredient_id" id="hiddenIngredientId">
        <input type="hidden" name="expiration_date" id="hiddenExpirationDate">
    </form>

    <!-- Lista ingredienti -->
    <div class="ingredients-list">
        <?php if (empty($inventoryItems)): ?>
            <div class="no-ingredients">
                Your inventory is empty<br>Add ingredients using the search bar above
            </div>
        <?php else: foreach ($inventoryItems as $item): ?>
            <div class="ingredient-row">
                <span class="ingredient-name"><?= htmlspecialchars($item['ingredient_name']) ?></span>
                <div class="delete-and-expire-container">
                    <span class="expiry-value">
                        <?= $item['expiration_date'] ? date('d/m/Y', strtotime($item['expiration_date'])) : 'No expiration' ?>
                    </span>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ingredient_id" value="<?= $item['ingredient_id'] ?>">
                        <button type="submit" class="material-symbols-outlined delete-inline-btn">delete</button>
                    </form>
                </div>
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
    const ingredientInput = document.getElementById('ingredientInput');
    const suggestionsList = document.getElementById('suggestionsList');
    const addButton = document.getElementById('addButton');
    const expirationDateInput = document.getElementById('expirationDate');
    const hiddenIngredientId = document.getElementById('hiddenIngredientId');
    const hiddenExpirationDate = document.getElementById('hiddenExpirationDate');
    const hiddenForm = document.getElementById('hiddenAddForm');

    let searchTimer;

    // RICERCA DALLA PRIMA LETTERA (come Dashboard)
    ingredientInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = ingredientInput.value.trim();

        if (query === '') {
            suggestionsList.innerHTML = '';
            return;
        }

        searchTimer = setTimeout(() => {
            fetch(`inventory.php?ajax=search&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(items => {
                    suggestionsList.innerHTML = '';
                    if (!items || items.length === 0) {
                        suggestionsList.innerHTML = '<div class="suggestion">No ingredients found</div>';
                        return;
                    }

                    items.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'suggestion';
                        div.textContent = item.name;
                        div.onclick = () => {
                            ingredientInput.value = item.name;
                            hiddenIngredientId.value = item.id;
                            suggestionsList.innerHTML = '';
                            addButton.disabled = false;  // Abilita bottone solo dopo selezione
                            expirationDateInput.focus();
                        };
                        suggestionsList.appendChild(div);
                    });
                })
                .catch(() => {
                    suggestionsList.innerHTML = '<div class="suggestion">Connection error</div>';
                });
        }, 250);
    });

    // Chiudi suggerimenti cliccando fuori
    document.addEventListener('click', (e) => {
        if (!ingredientInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.innerHTML = '';
        }
    });

    // Aggiungi ingrediente
    addButton.addEventListener('click', () => {
        if (!hiddenIngredientId.value) {
            alert('Please select an ingredient from the list');
            return;
        }

        hiddenExpirationDate.value = expirationDateInput.value || '';
        hiddenForm.submit();
    });

    // Permetti Invio sulla data per aggiungere
    expirationDateInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !addButton.disabled) {
            addButton.click();
        }
    });

    // Se l'utente cancella manualmente l'input → disabilita bottone
    ingredientInput.addEventListener('input', () => {
        if (ingredientInput.value === '' || !hiddenIngredientId.value) {
            addButton.disabled = true;
        }
    });
</script>

</body>
</html>