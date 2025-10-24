<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef - Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <nav class="navbar">
        <div class="logo">My Secret Chef</div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#" class="active">My Fridge</a></li>
            <li><a href="#">Recipes</a></li>
            <li><a href="#">Meal Plans</a></li>
        </ul>
        <div class="search-bar">
            <input type="text" placeholder="Search for ingredients">
        </div>
        <div class="profile-icon">👤</div>
    </nav>

    <div class="welcome-section">
        <h1>Welcome, Clara!</h1>
        <p>Add the ingredients you have and discover what you can cook.</p>
        <button class="add-button">+ Add</button>
        <div class="ingredient-tags">
            <span class="tag">Tomato</span>
            <span class="tag">Basil</span>
            <span class="tag">Garlic</span>
            <span class="tag">Olive Oil</span>
        </div>
    </div>

    <div class="recommended-recipes">
        <h2>Recommended Recipes</h2>
        <div class="recipe-grid">
            <div class="card">
                <img src="img/pomo_basilico.png" alt="Pasta with Tomato and Basil">
                <p>Pasta with Tomato and Basil</p>
            </div>
            <div class="card">
                <img src="img/garlic_bread.png" alt="Garlic Bread">
                <p>Garlic Bread</p>
            </div>
            <div class="card">
                <img src="olive_oil_salad.jpg" alt="Olive Oil Salad">
                <p>Olive Oil Salad</p>
            </div>
            <div class="card">
                <img src="tomato_soup.jpg" alt="Tomato Soup">
                <p>Tomato Soup</p>
            </div>
        </div>
        <div class="popular-section">
            <span class="popular-label">★ Popular this week</span>
            <div class="recipe-grid">
                <div class="card">
                    <img src="img/pomo_basilico.png" alt="Pasta with Tomato and Basil">
                    <p>Pasta with Tomato and Basil</p>
                </div>
                <div class="card">
                    <img src="img/garlic_bread.png" alt="Garlic Bread">
                    <p>Garlic Bread</p>
                </div>
                <div class="card">
                    <img src="olive_oil_salad_popular.jpg" alt="Olive Oil Salad">
                    <p>Olive Oil Salad</p>
                </div>
                <div class="card">
                    <img src="tomato_soup_popular.jpg" alt="Tomato Soup">
                    <p>Tomato Soup</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>