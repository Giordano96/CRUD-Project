<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef- Lista Ricette</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <nav class="navbar">
        <div class="logo">My Secret Chef</div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#">My Fridge</a></li>
            <li><a href="#" class="active">Recipes</a></li>
            <li><a href="#">Meal Plans</a></li>
        </ul>
        <div class="search-bar">
            <input type="text" placeholder="Search for recipes">
        </div>
        <div class="profile-icon">👤</div>
    </nav>

    <div class="recipe-list">
        <div class="filters">
            <select class="category-filter">
                <option value="">Tutte le categorie</option>
                <option value="primi">Primi</option>
                <option value="secondi">Secondi</option>
                <option value="dolci">Dolci</option>
                <option value="vegetariano">Vegetariano</option>
            </select>
        </div>

        <div class="recipes">
            <h2>Ricette Disponibili</h2>
            <div class="recipe-grid">
                <div class="card">
                    <img src="img/spaghetti_pomo.png" alt="Spaghetti al Pomodoro">
                    <p>Spaghetti al Pomodoro</p>
                    <span class="prep-time">20 min</span>
                    <button class="favorite-btn">★</button>
                </div>
                <div class="card">
                    <img src="img/riso_funghi.png" alt="Risotto ai Funghi">
                    <p>Risotto ai Funghi</p>
                    <span class="prep-time">30 min</span>
                    <button class="favorite-btn">★</button>
                </div>
                <div class="card">
                    <img src="tiramisu.jpg" alt="Tiramisù">
                    <p>Tiramisù</p>
                    <span class="prep-time">40 min</span>
                    <button class="favorite-btn">★</button>
                </div>
                <div class="card">
                    <img src="insalata_caprese.jpg" alt="Insalata Caprese">
                    <p>Insalata Caprese</p>
                    <span class="prep-time">15 min</span>
                    <button class="favorite-btn">★</button>
                </div>
            </div>
        </div>

        <div class="favorites">
            <h2>Ricette Preferite</h2>
            <div class="recipe-grid">
                <div class="card">
                    <img src="spaghetti_aglio.jpg" alt="Spaghetti Aglio e Olio">
                    <p>Spaghetti Aglio e Olio</p>
                    <span class="prep-time">20 min</span>
                    <button class="favorite-btn active">★</button>
                </div>
                <div class="card">
                    <img src="img/spaghetti_carbo.png" alt="Spaghetti alla Carbonara">
                    <p>Spaghetti alla Carbonara</p>
                    <span class="prep-time">25 min</span>
                    <button class="favorite-btn active">★</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>