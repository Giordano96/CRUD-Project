<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Favorites</title>
    <!-- Icone Material Design -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <!-- Stili specifici per la pagina preferiti -->
    <link rel="stylesheet" href="styles_favorites.css">
    <!-- Font principale -->
    <link href='https://fonts.googleapis.com/css?family=Plus Jakarta Sans' rel='stylesheet'>
</head>
<body>

<!-- ==================== HEADER ==================== -->
<div class="header">
    <div class="logo-container">
        <img src="../img/MySecretChef_Logo.png" alt="My Secret Chef" onclick="location.href='../Dashboard/dashboard.php'">
    </div>
    <div class="page-title">My Favorites</div>
    <div class="logout-icon" onclick="location.href='../utility/logout.php'">
        <span class="material-symbols-outlined">logout</span>
    </div>
</div>

<!-- ==================== CONTENUTO PRINCIPALE ==================== -->
<div class="content">
    <!-- Contenitore griglia ricette (popolato via JavaScript) -->
    <div id="recipeResults">
        <div style="text-align:center; color:#999; padding:2rem;">Loading your favorites...</div>
    </div>
</div>

<!-- Token CSRF nascosto per richieste POST -->
<input type="hidden" id="csrfToken" value="<?= $_SESSION['csrf_token'] ?>">

<!-- ==================== NAVIGAZIONE INFERIORE ==================== -->
<div class="bottom-nav">
    <div class="nav-item" onclick="location.href='../Dashboard/dashboard.php'">
        <span class="material-symbols-outlined">view_cozy</span>
        Home
    </div>
    <div class="nav-item" onclick="location.href='../Inventory/inventory.php'">
        <span class="material-symbols-outlined">box</span>
        Inventory
    </div>
    <div class="nav-item active">
        <span class="material-symbols-outlined">favorite</span>
        Favorites
    </div>
</div>

<script>
    // ==================== RIFERIMENTI DOM ====================
    const recipeResults = document.getElementById('recipeResults');
    const csrfToken = document.getElementById('csrfToken').value;

    // ==================== STATO PAGINAZIONE ====================
    let currentPage = 1;
    let hasMoreRecipes = true;
    let isLoading = false;

    // ==================== RESET RICERCA ====================
    function resetSearch() {
        currentPage = 1;
        hasMoreRecipes = true;
        recipeResults.innerHTML = '<div style="text-align:center; color:#999; padding:2rem;">Loading favorites...</div>';
    }

    // ==================== CARICA PREFERITI ====================
    function loadFavorites(page = 1) {
        if (isLoading || !hasMoreRecipes) return;

        if (page === 1) {
            recipeResults.innerHTML = '<div style="text-align:center; color:#999; padding:2rem;">Loading...</div>';
        }

        isLoading = true;

        fetch(`favorites.php?ajax=search&page=${page}`)
            .then(r => r.json())
            .then(data => {
                isLoading = false;

                // Nessun preferito salvato
                if (!data.recipes || data.recipes.length === 0) {
                    if (page === 1) {
                        recipeResults.innerHTML = '<div class="no-favorites">You have no saved favorites yet.</div>';
                    }
                    hasMoreRecipes = false;
                    return;
                }

                let html = '';
                if (page === 1) {
                    html += '<div class="recipes">'; // Inizia griglia
                }

                // Genera card per ogni ricetta
                data.recipes.forEach(recipe => {
                    const imageUrl = recipe.image_url || '../img/default_recipe.jpg';
                    const detailUrl = `../Recipe_Details/recipe_details.php?id=${recipe.id}`;

                    html += `
                        <div class="recipe" style="position:relative;">
                            <!-- Pulsante rimuovi preferito -->
                            <button class="recipe-remove" onclick="removeFavorite(${recipe.id}, event)" title="Remove from favorites">
                                <span class="material-symbols-outlined">cancel</span>
                            </button>
                            <!-- Link alla pagina dettaglio ricetta -->
                            <a href="${detailUrl}" class="recipe-link">
                                <img src="${imageUrl}" alt="${recipe.name}">
                                <div class="recipe-content">
                                    <div class="recipe-title">${recipe.name}</div>
                                    <div class="recipe-subtitle">Ready in ${recipe.prep_time} min</div>
                                </div>
                            </a>
                        </div>`;
                });

                // Inserisci HTML (nuova griglia o append)
                if (page === 1) {
                    recipeResults.innerHTML = html + '</div>';
                } else {
                    document.querySelector('.recipes').insertAdjacentHTML('beforeend', html);
                }

                hasMoreRecipes = data.page < data.pages;
                currentPage = data.page;
            })
            .catch(() => {
                isLoading = false;
                if (page === 1) {
                    recipeResults.innerHTML = '<div class="no-favorites">Connection error. Please try again.</div>';
                }
            });
    }

    // ==================== RIMUOVI DAI PREFERITI ====================
    function removeFavorite(recipeId, event) {
        event.preventDefault();
        event.stopPropagation(); // Evita apertura link

        const formData = new FormData();
        formData.append('recipe_id', recipeId);
        formData.append('csrf_token', csrfToken);

        fetch('favorites.php?ajax=remove', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(response => {
                if (response.error === 'Invalid CSRF token') {
                    alert('Session expired. Reloading page...');
                    location.reload();
                    return;
                }
                if (response.success) {
                    resetSearch();
                    loadFavorites(1); // Ricarica da capo
                }
            })
            .catch(() => alert('Failed to remove. Please try again.'));
    }

    // ==================== SCROLL INFINITO ====================
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            const distanceFromBottom = document.documentElement.scrollHeight - window.innerHeight - window.scrollY;
            if (distanceFromBottom < 400 && hasMoreRecipes && !isLoading) {
                loadFavorites(currentPage + 1);
            }
        }, 100);
    });

    // ==================== AVVIO AUTOMATICO ====================
    loadFavorites(1);
</script>

</body>
</html>