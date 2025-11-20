<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - <?= htmlspecialchars($item['recipe_name']) ?></title>

    <!-- Icone Material -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <!-- Stile unificato (usa lo stesso file della dashboard per coerenza totale) -->
    <link rel="stylesheet" href="styles_recipe_details.css">
</head>
<body>

<!-- ==================== HEADER IDENTICO ALLA DASHBOARD ==================== -->
<div class="header">
    <div class="logo-container">
        <img src="../img/MySecretChef_Logo.png" alt="My Secret Chef">
    </div>
    <div class="page-title">Recipe details</div>
    <div class="logout-icon" onclick="location.href='../logout.php'">
        <span class="material-symbols-outlined">logout</span>
    </div>
</div>

<!-- Immagine principale della ricetta -->
<div class="image-container">
    <img src="<?= htmlspecialchars($item['image_url']) ?>" class="recipe-img" alt="<?= htmlspecialchars($item['recipe_name']) ?>">
    <span class="favorite-icon material-symbols-outlined">favorite</span>
</div>

<div>
    <p style= "text-align: center" class="recipe-title"><?= htmlspecialchars($item['recipe_name']) ?></p>
</div>

<!-- Tabs -->
<div class="tabs">
    <div class="tab active">Ingredients</div>
    <div class="tab">Procedure</div>
    <div class="tab">Nutrition</div>
</div>

<!-- Contenuto -->
<div class="content">

    <!-- ==================== INGREDIENTI ==================== -->
    <div id="ingredients-section" class="tab-section active-tab">
        <div class="ingredients-title">Ingredienti</div>

        <?php foreach ($ingredients as $ing):
            $ing = trim($ing);
            preg_match('/^([\d.,\s]+(?:\s*(di|g|kg|ml|l|cl|dl|cucchiaio|cucchiaini|pizzico|q\.?b\.?|qb|fogli[ae]|spicchi?o|teste?))?)\s*-?\s*(.+)$/i', $ing, $m);
            $quantita = $m[1] ?? '';
            $nome     = trim($m[3] ?? $ing);

            $found = false;
            foreach ($inventory as $inv_item) {
                if (strcasecmp($inv_item, $nome) === 0 ||
                    stripos($inv_item, $nome) !== false ||
                    stripos($nome, $inv_item) !== false) {
                    $found = true;
                    break;
                }
            }
            ?>
            <div class="ingredient-item">
                <input type="checkbox" class="ingredient-checkbox" <?= $found ? 'checked' : 'disabled' ?>>
                <?php if ($quantita): ?>
                    <p><?= htmlspecialchars($nome) ?> — <?= htmlspecialchars($quantita) ?></p>
                <?php else: ?>
                    <p><?= htmlspecialchars($ing) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ==================== PROCEDURA ==================== -->
    <div id="procedure-section" class="tab-section">
        <h3>Procedure</h3>
        <div class="prep-time">
            <strong>Prep Time: <?= htmlspecialchars($item['prep_time']) ?> mins</strong>
        </div>
        <br>
        <div style="line-height:1.8; color:#444;">
            <?= nl2br(htmlspecialchars($item['instructions'])) ?>
        </div>
    </div>

    <!-- ==================== NUTRIZIONE ==================== -->
    <div id="nutrition-section" class="tab-section">
        <h3>Valori Nutrizionali</h3>
        <?php foreach ($nutrients as $nutrient): ?>
            <p><?= htmlspecialchars($nutrient) ?></p>
        <?php endforeach; ?>
    </div>

</div>

<!-- ==================== BOTTOM NAV IDENTICA ==================== -->
<div class="bottom-nav">
    <div class="nav-item" onclick="location.href='../Dashboard/dashboard.php'">
        <span class="material-symbols-outlined">view_cozy</span>
        Home
    </div>
    <div class="nav-item" onclick="location.href='../Inventory/inventory.php'">
        <span class="material-symbols-outlined">box</span>
        Inventory
    </div>
    <div class="nav-item" onclick="location.href='../Favorites/favorites.php'">
        <span class="material-symbols-outlined">favorite</span>
        Favorites
    </div>
</div>

<script>
    // Gestione tab (identica alla dashboard)
    const tabs = document.querySelectorAll('.tab');
    const sections = document.querySelectorAll('.tab-section');

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active-tab'));
            tab.classList.add('active');
            sections[index].classList.add('active-tab');
        });
    });

    // Checkbox con effetto barrato
    document.querySelectorAll('.ingredient-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const p = this.nextElementSibling;
            if (this.checked) {
                p.style.textDecoration = 'line-through';
                p.style.color = '#999';
            } else {
                p.style.textDecoration = 'none';
                p.style.color = '#333';
            }
        });
    });
</script>

</body>
</html>