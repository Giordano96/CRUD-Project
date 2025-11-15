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

        <div class="ingredient-search">
            <input type="text" id="ingredient-input" placeholder="Cerca ingrediente…" autocomplete="off">
            <button type="button" id="add-btn" class="btn-inline">Add</button>
            <button type="button" id="add-inventory-btn" class="btn-inline">Add from Inventory</button>
            <div id="suggestions" class="suggestions-box"></div>
        </div>

        <div class="selected-ingredients" id="selected-tags">
            <?php if (empty($user_ingredients)): ?>
                <p class="no-ingredients">Aggiungi ingredienti per cercare ricette!</p>
            <?php else: foreach ($user_ingredients as $ing): ?>
                <span class="tag" data-name="<?php echo htmlspecialchars($ing); ?>">
                    <?php echo htmlspecialchars($ing); ?> <span class="remove-tag">×</span>
                </span>
            <?php endforeach; endif; ?>
        </div>

        <div style="margin: 1.5rem 0;">
            <button id="search-btn" class="btn-dark" <?php echo empty($user_ingredients) ? 'disabled' : ''; ?>>
                Cerca Ricette
            </button>
        </div>

        <div class="recipe-box" id="recipe-results">
            <p style="text-align:center; color:#a17f45; padding:1rem;">Premi "Cerca Ricette" per vedere i risultati!</p>
        </div>
    </main>
</div>

<script>
    const els = {
        input: document.getElementById('ingredient-input'),
        suggestions: document.getElementById('suggestions'),
        addBtn: document.getElementById('add-btn'),
        addInvBtn: document.getElementById('add-inventory-btn'),
        searchBtn: document.getElementById('search-btn'),
        results: document.getElementById('recipe-results'),
        tags: document.getElementById('selected-tags')
    };

    let timer;

    // Suggerimenti
    els.input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = els.input.value.trim();
        if (!q) return els.suggestions.innerHTML = '';

        timer = setTimeout(() => {
            fetch(`dashboard.php?ajax=suggest&q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    els.suggestions.innerHTML = data.length
                        ? data.map(ing => `<div class="suggestion" onclick="selectIng('${ing}')">${ing}</div>`).join('')
                        : '<div class="suggestion">Nessun risultato</div>';
                });
        }, 200);
    });

    window.selectIng = (name) => {
        els.input.value = name;
        els.suggestions.innerHTML = '';
    };

    // Azioni
    els.addBtn.onclick = () => {
        const name = els.input.value.trim();
        if (!name) return;
        post('add', { ingredient: name }, () => els.input.value = '');
    };

    els.tags.onclick = (e) => {
        if (e.target.classList.contains('remove-tag')) {
            const name = e.target.parentElement.dataset.name;
            post('remove', { ingredient: name });
        }
    };

    els.addInvBtn.onclick = () => post('load_inventory', {});

    els.searchBtn.onclick = () => search(1);

    // Ricerca con paginazione
    function search(page = 1) {
        els.results.innerHTML = '<p style="text-align:center; color:#a17f45;">Caricamento...</p>';

        fetch(`dashboard.php?ajax=search&page=${page}`)
            .then(r => r.json())
            .then(data => {
                if (!data.recipes?.length) {
                    els.results.innerHTML = '<p class="no-recipes">Nessuna ricetta trovata con questi ingredienti.</p>';
                    return;
                }

                let html = `<h2>Ricette suggerite <span class="recipe-count">(${data.total} trovate)</span></h2>`;
                html += '<div class="recipe-grid">';

                data.recipes.forEach(r => {
                    html += `
                        <div class="recipe-card">
                            <img src="${r.image_url || 'img/placeholder.png'}" loading="lazy" alt="${r.name}">
                            <h3>${r.name}</h3>
                            <p>Pronta in ${r.prep_time} min</p>
                            <a href="recipe_detail.php?id=${r.id}" style="text-decoration:none;">
                                <button class="btn-details">Dettagli</button>
                            </a>
                        </div>`;
                });

                html += '</div>';

                if (data.pages > 1) {
                    html += '<div class="pagination">';
                    if (data.page > 1) {
                        html += `<button class="page-btn" onclick="search(${data.page - 1})">Indietro</button>`;
                    }
                    html += `<span class="page-info">Pagina ${data.page} di ${data.pages}</span>`;
                    if (data.page < data.pages) {
                        html += `<button class="page-btn" onclick="search(${data.page + 1})">Avanti</button>`;
                    }
                    html += '</div>';
                }

                els.results.innerHTML = html;
            })
            .catch(() => {
                els.results.innerHTML = '<p class="no-recipes">Errore di connessione. Riprova.</p>';
            });
    }

    // POST generico
    function post(action, data, callback) {
        const form = new FormData();
        for (const key in data) form.append(key, data[key]);

        fetch(`dashboard.php?ajax=${action}`, { method: 'POST', body: form })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    updateTags(res.ingredients);
                    els.searchBtn.disabled = res.ingredients.length === 0;
                    if (callback) callback();
                }
            });
    }

    // Aggiorna tag
    function updateTags(ingredients) {
        if (!ingredients.length) {
            els.tags.innerHTML = '<p class="no-ingredients">Aggiungi ingredienti per cercare ricette!</p>';
            return;
        }
        els.tags.innerHTML = ingredients.map(ing =>
            `<span class="tag" data-name="${ing}">${ing} <span class="remove-tag">×</span></span>`
        ).join('');
    }

    // Chiudi suggerimenti
    document.addEventListener('click', e => {
        if (!els.input.contains(e.target) && !els.suggestions.contains(e.target)) {
            els.suggestions.innerHTML = '';
        }
    });
</script>

</body>
</html>