<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – MySecretChef</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="main-container">
    <nav class="sidebar">
        <ul>
            <li><a href="dashboard.php" class="active">Home</a></li>
            <li><a href="inventory_view.php">Inventario</a></li>
            <li><a href="recipes.php">Ricette</a></li>
            <li><a href="favorites.php">Preferiti</a></li>
            <li><a href="../logout.php" style="margin-top: 2rem; color: #a17f45;">Esci</a></li>
        </ul>
    </nav>

    <main>
        <h1>Benvenuto, <?php echo htmlspecialchars($username); ?>!</h1>

        <!-- DEBUG: Mostra ingredienti (rimuovi in produzione) -->
        <?php if (isset($_GET['debug'])): ?>
            <pre><?php var_dump($user_ingredients); ?></pre>
        <?php endif; ?>

        <!-- AGGIUNGI INGREDIENTE -->
        <div class="ingredient-search">
            <input type="text" id="ingredient-input" placeholder="Cerca ingrediente…" autocomplete="off">
            <div id="suggestions" class="suggestions-box"></div>
        </div>

        <!-- INGREDIENTI SELEZIONATI -->
        <div class="selected-ingredients">
            <?php if (empty($user_ingredients)): ?>
                <p class="no-ingredients">Aggiungi ingredienti per cercare ricette!</p>
            <?php else: ?>
                <?php foreach ($user_ingredients as $ing): ?>
                    <span class="tag"><?php echo htmlspecialchars($ing); ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- BOTTONE CERCA -->
        <?php if (!empty($user_ingredients)): ?>
            <form method="post" style="margin: 1.5rem 0;">
                <button type="submit" name="search" class="search-btn">Cerca Ricette</button>
            </form>
        <?php endif; ?>

        <!-- RISULTATI RICERCA -->
        <div class="recipe-box">
            <h2>Ricette suggerite
                <?php if ($search_triggered): ?>
                    <span class="recipe-count">(<?php echo $total_recipes; ?> trovate)</span>
                <?php endif; ?>
            </h2>

            <?php if ($search_triggered && empty($suggested_recipes)): ?>
                <p class="no-recipes">
                    Nessuna ricetta trovata con questi ingredienti.
                </p>
            <?php elseif ($search_triggered): ?>
                <div class="recipe-grid">
                    <?php foreach ($suggested_recipes as $recipe): ?>
                        <div class="recipe-card">
                            <img src="<?php echo htmlspecialchars($recipe['image_url'] ?? 'img/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>">
                            <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
                            <p>Pronta in <?php echo (int)$recipe['prep_time']; ?> min</p>
                            <br>
                            <a href="recipe_detail.php?id=<?php echo $recipe['id']; ?>" style="text-decoration:none;">
                                <button>Dettagli</button>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- PAGINAZIONE -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">Indietro</a>
                        <?php endif; ?>

                        <span class="page-info">
                            Pagina <?php echo $page; ?> di <?php echo $total_pages; ?>
                        </span>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">Avanti</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="no-recipes" style="background:none; padding:1rem 0;">
                    Premi "Cerca Ricette" per vedere i risultati!
                </p>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- JAVASCRIPT: TENDINA + AGGIUNTA -->
<script>
    const input = document.getElementById('ingredient-input');
    const suggestionsBox = document.getElementById('suggestions');
    let timeout = null;

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();

        if (query.length === 0) {
            suggestionsBox.innerHTML = '';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`api_ingredients.php?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    suggestionsBox.innerHTML = '';
                    if (data.length === 0) {
                        suggestionsBox.innerHTML = '<div class="suggestion">Nessun ingrediente trovato</div>';
                        return;
                    }
                    data.forEach(ing => {
                        const div = document.createElement('div');
                        div.className = 'suggestion';
                        div.textContent = ing;
                        div.onclick = () => addIngredient(ing);
                        suggestionsBox.appendChild(div);
                    });
                })
                .catch(err => {
                    console.error('Errore fetch:', err);
                });
        }, 200);
    });

    function addIngredient(name) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = `dashboard.php?page=<?php echo $page; ?>`;
        form.innerHTML = `<input type="hidden" name="ingredient" value="${name}">`;
        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.innerHTML = '';
        }
    });
</script>

</body>
</html>