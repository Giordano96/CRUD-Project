<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Home</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=box,favorite,view_cozy" />
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="header">MySecretChef</div>

<div class="content">
    <div class="cooking-question">What are we cooking today?</div>

    <div class="search-container">
        <span class="material-symbols-outlined search-icon"></span>
        <input type="text" id="ingredient-input" class="search-input" placeholder="Insert Ingredients" autocomplete="off">
        <div id="suggestions" class="suggestions-box"></div>
    </div>

    <div class="buttons">
        <button id="add-btn" class="button">Add Ingredient</button>
        <button id="add-inventory-btn" class="button secondary">Add from Inventory</button>
    </div>

    <div class="tags" id="selected-tags">
        <?php if (empty($user_ingredients)): ?>
            <div class="no-ingredients">Aggiungi ingredienti per cercare ricette!</div>
        <?php else: foreach ($user_ingredients as $ing): ?>
            <div class="tag" data-name="<?php echo htmlspecialchars($ing); ?>">
                <?php echo htmlspecialchars($ing); ?> <span class="tag-close">×</span>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div id="recipe-results">
        <div style="text-align:center; color:#999; padding:1rem;">Aggiungi ingredienti e vedi le ricette suggerite!</div>
    </div>

    <div id="loading" style="display:none; text-align:center; padding:1.5rem; color:#999;">
        Caricamento ricette...
    </div>
</div>

<div class="bottom-nav">
    <div class="nav-item active" onclick="location.href='dashboard.php'">
        <span class="material-symbols-outlined">view_cozy</span>
        Home
    </div>
    <div class="nav-item" onclick="location.href='inventory_view.php'">
        <span class="material-symbols-outlined">box</span>
        Inventory
    </div>
    <div class="nav-item" onclick="location.href='favorites.php'">
        <span class="material-symbols-outlined">favorite</span>
        Favorites
    </div>
</div>

<script>
    const els = {
        input: document.getElementById('ingredient-input'),
        suggestions: document.getElementById('suggestions'),
        addBtn: document.getElementById('add-btn'),
        addInvBtn: document.getElementById('add-inventory-btn'),
        results: document.getElementById('recipe-results'),
        tags: document.getElementById('selected-tags'),
        loading: document.getElementById('loading')
    };

    let timer;
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;

    function showLoading() {
        els.loading.style.display = 'block';
        isLoading = true;
    }

    function hideLoading() {
        els.loading.style.display = 'none';
        isLoading = false;
    }

    function resetInfiniteScroll() {
        currentPage = 1;
        hasMore = true;
        els.results.innerHTML = '';
    }

    // --- RICERCA INGREDIENTI ---
    els.input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = els.input.value.trim();
        if (!q) {
            els.suggestions.innerHTML = '';
            return;
        }

        timer = setTimeout(() => {
            fetch(`dashboard.php?ajax=suggest&q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    els.suggestions.innerHTML = data.length
                        ? data.map(ing => `<div class="suggestion" onclick="selectIng('${ing}')">${ing}</div>`).join('')
                        : '<div class="suggestion">Nessun ingrediente trovato</div>';
                })
                .catch(() => {
                    els.suggestions.innerHTML = '<div class="suggestion">Errore di connessione</div>';
                });
        }, 200);
    });

    window.selectIng = (name) => {
        els.input.value = name;
        els.suggestions.innerHTML = '';
    };

    // --- AZIONI INGREDIENTI ---
    els.addBtn.onclick = () => {
        const name = els.input.value.trim();
        if (!name) return;
        post('add', { ingredient: name }, () => {
            els.input.value = '';
            resetInfiniteScroll();
            search();
        });
    };

    els.tags.onclick = (e) => {
        if (e.target.classList.contains('tag-close')) {
            const name = e.target.parentElement.dataset.name;
            post('remove', { ingredient: name }, () => {
                resetInfiniteScroll();
                search();
            });
        }
    };

    els.addInvBtn.onclick = () => {
        post('load_inventory', {}, () => {
            resetInfiniteScroll();
            search();
        });
    };

    // --- RICERCA RICETTE ---
    function search(page = 1) {
        if (isLoading || !hasMore) return;
        if (page === 1) {
            els.results.innerHTML = '<div style="text-align:center; color:#999; padding:2rem;">Caricamento...</div>';
        } else {
            showLoading();
        }

        fetch(`dashboard.php?ajax=search&page=${page}`)
            .then(r => r.json())
            .then(data => {
                hideLoading();

                if (!data.recipes?.length) {
                    if (page === 1) {
                        els.results.innerHTML = '<div class="no-recipes">Nessuna ricetta trovata con questi ingredienti.</div>';
                    }
                    hasMore = false;
                    return;
                }

                let html = '';
                if (page === 1) {
                    html += `<div class="recommended">Recommended Recipes <span style="font-weight:normal; font-size:14px; color:#EF8A37;">(${data.total} found)</span></div>`;
                    html += '<div class="recipes">';
                }

                data.recipes.forEach(r => {

                    html += `
        <div class="recipe">
            <img src="${r.image_url || 'img/garlic_bread.png'}" alt="${r.name}">
            <div class="recipe-content">
                <form action="recipe_detail.php" method="POST" style="margin:0; padding:0;">
                    <input type="hidden" name="recipe_id" value="${r.id}">
                    <?php foreach ($_SESSION['selected_ingredients'] as $ing): ?>
                        <input type="hidden" name="selected_ingredients[]" value="<?php echo htmlspecialchars($ing); ?>">
                    <?php endforeach; ?>
                    <button type="submit" style="all:unset; cursor:pointer; display:block; width:100%; text-align:center;">
                        <div class="recipe-title">${r.name}</div>
                    </button>
                </form>
                <div class="recipe-subtitle">Ready in ${r.prep_time} min</div>
            </div>
        </div>`;
                });

                if (page === 1) {
                    els.results.innerHTML = html + '</div>';
                } else {
                    const container = els.results.querySelector('.recipes');
                    container.insertAdjacentHTML('beforeend', html);
                }

                hasMore = data.page < data.pages;
                currentPage = data.page;
            })
            .catch(() => {
                hideLoading();
                if (page === 1) {
                    els.results.innerHTML = '<div class="no-recipes">Errore di connessione. Riprova.</div>';
                }
            });
    }

    // --- INFINITE SCROLL ---
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
            if (scrollTop + clientHeight >= scrollHeight - 300 && hasMore && !isLoading) {
                search(currentPage + 1);
            }
        }, 100);
    });

    // --- POST GENERICO ---
    function post(action, data, callback) {
        const form = new FormData();
        for (const key in data) form.append(key, data[key]);

        fetch(`dashboard.php?ajax=${action}`, { method: 'POST', body: form })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    updateTags(res.ingredients);
                    if (callback) callback();
                }
            })
            .catch(() => alert('Errore di rete. Riprova.'));
    }

    function updateTags(ingredients) {
        if (!ingredients.length) {
            els.tags.innerHTML = '<div class="no-ingredients">Aggiungi ingredienti per cercare ricette!</div>';
            return;
        }
        els.tags.innerHTML = ingredients.map(ing =>
            `<div class="tag" data-name="${ing}">${ing} <span class="tag-close">×</span></div>`
        ).join('');
    }

    document.addEventListener('click', e => {
        if (!els.input.contains(e.target) && !els.suggestions.contains(e.target)) {
            els.suggestions.innerHTML = '';
        }
    });

    if (els.tags.querySelectorAll('.tag').length > 0) {
        resetInfiniteScroll();
        search(1);
    }
</script>

</body>
</html>