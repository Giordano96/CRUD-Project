<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <title>My Secret Chef - Recipe details</title>
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

    <img src="img/pasta.png" class="recipe-img" alt="Recipe Image">

    <div class="recipe-header">
        <h2>Pasta rossa</h2>
        <div class="recipe-info">
            10 min
        </div>
    </div>

    <div class="tabs">
        <div class="tab active">Ingredients</div>
        <div class="tab">Procedure</div>
        <div class="tab">Nutrition</div>
    </div>

    <div class="content">

        <div id="ingredients-section" class="tab-section active-tab">
            <div class="ingredients-title">Ingredients</div>

            <div class="ingredient-item">
                <input type="checkbox" class="ingredient-checkbox">
                Pasta (spaghetti o penne) — 200 g
            </div>

            <div class="ingredient-item">
                <input type="checkbox" class="ingredient-checkbox">
                Pomodori pelati o passata di pomodoro — 300 g
            </div>

            <div class="ingredient-item">
                <input type="checkbox" class="ingredient-checkbox">
                Basilico fresco — circa 6-8 foglie
            </div>

            <div class="ingredient-item">
                <input type="checkbox" class="ingredient-checkbox">
                Olio extravergine d’oliva — 2 cucchiai
            </div>

            <div class="ingredient-item">
                <input type="checkbox" class="ingredient-checkbox">
                Sale — quanto basta
            </div>
        </div>

        <div id="procedure-section" class="tab-section">
            <h3>Procedure</h3>
            <p>Bollire la pasta in acqua salata.</p>
            <p>Preparare un soffritto leggero con olio e aglio.</p>
            <p>Aggiungere la passata di pomodoro, sale e pepe.</p>
            <p>Cuocere per 10 minuti e aggiungere basilico fresco.</p>
            <p>Scolare la pasta e unirla al sugo.</p>
        </div>

        <div id="nutrition-section" class="tab-section">
            <h3>Nutrition</h3>
            <p>Calorie: ~450 kcal a porzione</p>
            <p>Carboidrati: 75 g</p>
            <p>Proteine: 12 g</p>
            <p>Grassi: 10 g</p>
        </div>

    </div>

    <div class="bottom-nav">
        <div class="nav-item">
            <span class="material-symbols-outlined">view_cozy</span>
            Home
        </div>
        <div class="nav-item">
            <span class="material-symbols-outlined">box</span>
            Inventory
        </div>
        <div class="nav-item">
            <span class="material-symbols-outlined">favorite</span>
            Favorites
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
