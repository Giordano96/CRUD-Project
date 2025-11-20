<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Home</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="styles_dashboard.css">
</head>
<body>

<div class="header">
    <div class="logo-container">
        <img src="../img/MySecretChef_Logo.png" alt="My Secret Chef">
    </div>
    <div class="page-title">Home</div>
    <div class="logout-icon" onclick="location.href='../logout.php'">
        <span class="material-symbols-outlined">logout</span>
    </div>
</div>

<div class="content">
    <div class="cooking-question">What are we cooking today?</div>

    <!-- Ingredient search bar -->
    <div class="search-container">
        <input type="text" id="ingredientInput" class="search-input" placeholder="Enter ingredients..." autocomplete="off">
        <div id="suggestionsList" class="suggestions-box"></div>
    </div>

    <div class="buttons">
        <button id="addButton" class="button disabled">Add Ingredient</button>
        <button id="loadInventoryButton" class="button secondary">Add from Inventory</button>
    </div>

    <!-- Selected ingredients tags -->
    <div class="tags" id="tagsContainer">
        <?php if (empty($currentIngredients)): ?>
            <div class="no-ingredients">Add ingredients to search for recipes!</div>
        <?php else: ?>
            <?php foreach ($currentIngredients as $ing): ?>
                <div class="tag" data-name="<?= htmlspecialchars($ing) ?>">
                    <?= htmlspecialchars($ing) ?> <span class="tag-close">×</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recipe results -->
    <div id="recipeResults">
        <div style="text-align:center; color:#999; padding:2rem;">
            Add ingredients to see suggested recipes!
        </div>
    </div>

    <div id="loadingSpinner" style="display:none; text-align:center; padding:1.5rem; color:#999;">
        Loading recipes...
    </div>
</div>

<!-- Hidden CSRF token -->
<input type="hidden" id="csrfToken" value="<?= $_SESSION['csrf_token'] ?>">

<!-- Bottom navigation -->
<div class="bottom-nav">
    <div class="nav-item active">
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
    // ==================== DOM ELEMENTS ====================
    const ingredientInput = document.getElementById('ingredientInput');
    const suggestionsList = document.getElementById('suggestionsList');
    const addButton = document.getElementById('addButton');
    const loadInventoryButton = document.getElementById('loadInventoryButton');
    const recipeResults = document.getElementById('recipeResults');
    const tagsContainer = document.getElementById('tagsContainer');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const csrfToken = document.getElementById('csrfToken').value;

    // ==================== STATE VARIABLES ====================
    let currentPage = 1;
    let hasMoreRecipes = true;
    let isLoading = false;
    let searchTimer;
    let isIngredientValid = false;

    // ==================== GESTIONE STATO BOTTONE ====================
    function updateAddButtonState() {
        if (isIngredientValid && ingredientInput.value.trim() !== '') {
            addButton.disabled = false;
            addButton.classList.remove('disabled');
        } else {
            addButton.disabled = true;
            addButton.classList.add('disabled');
        }
    }
    updateAddButtonState();

    // ==================== AUTOCOMPLETE ====================
    ingredientInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = ingredientInput.value.trim();

        if (query === '') {
            suggestionsList.innerHTML = '';
            isIngredientValid = false;
            updateAddButtonState();
            return;
        }

        isIngredientValid = false;
        updateAddButtonState();

        searchTimer = setTimeout(() => {
            fetch(`dashboard.php?ajax=suggest&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(list => {
                    if (list.length === 0) {
                        suggestionsList.innerHTML = '<div class="suggestion">No ingredients found</div>';
                    } else {
                        suggestionsList.innerHTML = list.map(name =>
                            `<div class="suggestion" onclick="selectIngredient('${name.replace(/'/g, "\\'")}')">${name}</div>`
                        ).join('');
                    }
                })
                .catch(() => {
                    suggestionsList.innerHTML = '<div class="suggestion">Connection error</div>';
                });
        }, 250);
    });

    window.selectIngredient = function(name) {
        ingredientInput.value = name;
        suggestionsList.innerHTML = '';
        isIngredientValid = true;
        updateAddButtonState();
    };

    document.addEventListener('click', (e) => {
        if (!ingredientInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.innerHTML = '';
        }
    });

    // ==================== BUTTON ACTIONS ====================
    addButton.addEventListener('click', () => {
        const ingredient = ingredientInput.value.trim();
        if (!ingredient || !isIngredientValid) return;

        sendPostRequest('add', { ingredient }, () => {
            ingredientInput.value = '';
            isIngredientValid = false;
            updateAddButtonState();
            resetSearch();
            searchRecipes();
        });
    });

    loadInventoryButton.addEventListener('click', () => {
        sendPostRequest('load_inventory', {}, () => {
            resetSearch();
            searchRecipes();
        });
    });

    tagsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('tag-close')) {
            const tag = e.target.parentElement;
            const name = tag.dataset.name;
            sendPostRequest('remove', { ingredient: name }, () => {
                resetSearch();
                searchRecipes();
            });
        }
    });

    // ==================== UTILITY FUNCTIONS ====================
    function sendPostRequest(action, data = {}, callback = null) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        for (const key in data) formData.append(key, data[key]);

        fetch(`dashboard.php?ajax=${action}`, {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(response => {
                if (response.error === 'Invalid CSRF token') {
                    alert('Session expired. Reloading...');
                    location.reload();
                    return;
                }
                if (response.success) {
                    updateTags(response.ingredients || []);
                    if (callback) callback();
                }
            })
            .catch(() => alert('Network error. Please try again.'));
    }

    function updateTags(ingredients) {
        ingredients.sort();
        if (ingredients.length === 0) {
            tagsContainer.innerHTML = '<div class="no-ingredients">Add ingredients to search for recipes!</div>';
            return;
        }
        tagsContainer.innerHTML = ingredients.map(name => `
            <div class="tag" data-name="${name}">
                ${name} <span class="tag-close">×</span>
            </div>
        `).join('');
    }

    function showLoading() {
        loadingSpinner.style.display = 'block';
        isLoading = true;
    }
    function hideLoading() {
        loadingSpinner.style.display = 'none';
        isLoading = false;
    }

    function resetSearch() {
        currentPage = 1;
        hasMoreRecipes = true;
        recipeResults.innerHTML = '';
    }

    // ==================== SEARCH RECIPES ====================
    function searchRecipes(page = 1) {
        if (isLoading || !hasMoreRecipes) return;

        if (page === 1) {
            recipeResults.innerHTML = '<div style="text-align:center; color:#999; padding:2rem;">Loading...</div>';
        } else {
            showLoading();
        }

        fetch(`dashboard.php?ajax=search&page=${page}`)
            .then(r => r.json())
            .then(data => {
                hideLoading();

                if (!data.recipes || data.recipes.length === 0) {
                    if (page === 1) {
                        recipeResults.innerHTML = tagsContainer.querySelectorAll('.tag').length === 0
                            ? '<div style="text-align:center; color:#999; padding:2rem;">Add ingredients to see suggested recipes!</div>'
                            : '<div class="no-recipes">No recipes found with these ingredients.</div>';
                    }
                    hasMoreRecipes = false;
                    return;
                }

                let html = '';
                if (page === 1) {
                    html += `<div class="recommended">Recommended Recipes <span style="font-weight:normal; font-size:14px; color:#EF8A37;">(${data.total} found)</span></div>`;
                    html += '<div class="recipes">';
                }

                data.recipes.forEach(recipe => {
                    const url = `../Recipe_Details/recipe_details.php?id=${recipe.id}`;
                    html += `
                    <a href="${url}" class="recipe-link">
                        <div class="recipe">
                            <img src="${recipe.image_url}" alt="${recipe.name}">
                            <div class="recipe-content">
                                <div class="recipe-title">${recipe.name}</div>
                                <div class="recipe-subtitle">Ready in ${recipe.prep_time} min</div>
                            </div>
                        </div>
                    </a>`;
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
                hideLoading();
                if (page === 1) {
                    recipeResults.innerHTML = '<div class="no-recipes">Connection error. Please try again.</div>';
                }
            });
    }

    // Infinite scroll
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            const distanceFromBottom = document.documentElement.scrollHeight - window.innerHeight - window.scrollY;
            if (distanceFromBottom < 400 && hasMoreRecipes && !isLoading) {
                searchRecipes(currentPage + 1);
            }
        }, 100);
    });

    // Caricamento iniziale
    document.addEventListener('DOMContentLoaded', () => {
        if (tagsContainer.querySelectorAll('.tag').length > 0) {
            resetSearch();
            searchRecipes(1);
        }
    });
</script>

</body>
</html>