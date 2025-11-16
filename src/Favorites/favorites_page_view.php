<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Preferiti</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=box,favorite,view_cozy" />
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="header">I miei Preferiti</div>

<div class="content">
    <div id="recipe-results">
        <div style="text-align:center; color:#999; padding:2rem;">Caricamento preferiti...</div>
    </div>
    <div id="loading-fav">Caricamento...</div>
</div>

<!-- CSRF TOKEN -->
<input type="hidden" id="csrf-token" value="<?php echo $_SESSION['csrf_token']; ?>">

<div class="bottom-nav">
    <div class="nav-item" onclick="location.href='../Dashboard/dashboard.php'">
        <span class="material-symbols-outlined">view_cozy</span>
        Home
    </div>
    <div class="nav-item" onclick="location.href='inventory_view.php'">
        <span class="material-symbols-outlined">box</span>
        Inventory
    </div>
    <div class="nav-item active">
        <span class="material-symbols-outlined">favorite</span>
        Favorites
    </div>
</div>

<script>
    const els = {
        results: document.getElementById('recipe-results'),
        loading: document.getElementById('loading-fav'),
        csrf: document.getElementById('csrf-token')
    };

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

    function reset() {
        currentPage = 1;
        hasMore = true;
        els.results.innerHTML = '<div style="text-align:center; color:#999; padding:2rem;">Caricamento preferiti...</div>';
    }

    function search(page = 1) {
        if (isLoading || !hasMore) return;
        if (page === 1) {
            els.results.innerHTML = '<div style="text-align:center; color:#999; padding:2rem;">Caricamento...</div>';
        } else {
            showLoading();
        }

        fetch(`favorites.php?ajax=search&page=${page}`)
            .then(r => r.json())
            .then(data => {
                hideLoading();

                if (!data.recipes?.length) {
                    if (page === 1) {
                        els.results.innerHTML = '<div class="no-favorites">Nessun preferito salvato.</div>';
                    }
                    hasMore = false;
                    return;
                }

                let html = '';
                if (page === 1) {
                    html += `<div class="recipes">`;
                }

                data.recipes.forEach(r => {
                    html += `
                        <div class="recipe" style="position:relative;">
                            <button class="recipe-remove" onclick="removeFav(${r.id}, event)">×</button>
                            <img src="${r.image_url || 'img/garlic_bread.png'}" alt="${r.name}">
                            <div class="recipe-content">
                                <form action="recipe_detail.php" method="POST" style="margin:0;padding:0;">
                                    <input type="hidden" name="recipe_id" value="${r.id}">
                                    <button type="submit" style="all:unset; cursor:pointer; width:100%; text-align:center;">
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
                    els.results.innerHTML = '<div class="no-favorites">Errore di connessione.</div>';
                }
            });
    }

    function removeFav(recipeId, e) {
        e.preventDefault();
        e.stopPropagation();

        if (!confirm('Rimuovere dai preferiti?')) return;

        const form = new FormData();
        form.append('recipe_id', recipeId);
        form.append('csrf_token', els.csrf.value);

        fetch('favorites.php?ajax=remove', { method: 'POST', body: form })
            .then(r => r.json())
            .then(res => {
                if (res.error === 'Invalid CSRF token') {
                    alert('Token di sicurezza non valido. Ricaricamento...');
                    location.reload();
                    return;
                }
                if (res.success) {
                    reset();
                    search(1);
                }
            });
    }

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

    search(1);
</script>

</body>
</html>