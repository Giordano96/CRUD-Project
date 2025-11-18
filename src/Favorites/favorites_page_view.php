<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset=" bUTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Favorites</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="header">
    <div class="logo-container">
        <img src="../img/MySecretChef_Logo.png" alt="My Secret Chef" class="logo">
    </div>
    <div class="logout-icon">
        <span class="material-symbols-outlined">logout</span>
    </div>
</div>


<div class="content">
    <!-- Recipe grid container -->
    <div id="recipeResults">
        <div style="text-align:center; color:#999; padding:2rem;">Loading your favorites...</div>
    </div>
</div>

<!-- Hidden CSRF token -->
<input type="hidden" id="csrfToken" value="<?= $_SESSION['csrf_token'] ?>">

<!-- Bottom navigation -->
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
    // ==================== DOM ELEMENTS ====================
    const recipeResults = document.getElementById('recipeResults');
    const csrfToken = document.getElementById('csrfToken').value;

    // ==================== STATE ====================
    let currentPage = 1;
    let hasMoreRecipes = true;
    let isLoading = false;

    // ==================== RESET SEARCH ====================
    function resetSearch() {
        currentPage = 1;
        hasMoreRecipes = true;
        recipeResults.innerHTML = '<div style="text-align:center; color:#999; padding:2rem;">Loading favorites...</div>';
    }

    // ==================== LOAD FAVORITES ====================
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

                // No favorites
                if (!data.recipes || data.recipes.length === 0) {
                    if (page === 1) {
                        recipeResults.innerHTML = '<div class="no-favorites">You have no saved favorites yet.</div>';
                    }
                    hasMoreRecipes = false;
                    return;
                }

                let html = '';
                if (page === 1) {
                    html += '<div class="recipes">';
                }

                data.recipes.forEach(recipe => {
                    const imageUrl = recipe.image_url || 'img/garlic_bread.png';
                    html += `
                        <div class="recipe" style="position:relative;">
                            <button class="recipe-remove" onclick="removeFavorite(${recipe.id}, event)" title="Remove from favorites">×</button>
                            <img src="${imageUrl}" alt="${recipe.name}">
                            <div class="recipe-content">
                                <form action="recipe_detail.php" method="POST">
                                    <input type="hidden" name="recipe_id" value="${recipe.id}">
                                    <button type="submit" style="all:unset; cursor:pointer; width:100%; text-align:left;">
                                        <div class="recipe-title">${recipe.name}</div>
                                    </button>
                                </form>
                                <div class="recipe-subtitle">Ready in ${recipe.prep_time} min</div>
                            </div>
                        </div>`;
                });

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

    // ==================== REMOVE FAVORITE ====================
    function removeFavorite(recipeId, event) {
        event.preventDefault();
        event.stopPropagation();

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
                    loadFavorites(1);
                }
            })
            .catch(() => alert('Failed to remove. Please try again.'));
    }

    // ==================== INFINITE SCROLL ====================
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

    // ==================== START ====================
    loadFavorites(1);
</script>

</body>
</html>