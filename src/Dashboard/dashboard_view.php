<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=box,favorite,view_cozy" />    <title>My Secret Chef - Home</title>
    <style>
        /* Unified with login CSS: predictable sizing */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #fff; /* keep white as requested */
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background-color: #f9f9f9;
            padding: 12px 16px;
            text-align: center;
            font-size: 20px;
            color: #EF8A37; /* orange consistent with login */
            border-bottom: 1px solid #eee;
        }

        .content {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        .cooking-question {
            font-size: 18px;
            color: #173822; /* dark green */
            margin-bottom: 10px;
            text-align: left;
        }

        .search-container {
            position: relative;
            margin-bottom: 14px;
        }

        .search-input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px;
            font-size: 16px;
            background-color: rgba(255,255,255,0.95);
            box-sizing: border-box;
            outline: none;
        }

        .search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            pointer-events: none;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .button {
            padding: 10px 16px;
            background-color: #EF8A37;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            flex: 1 1 200px;
            text-align: center;
        }

        .button:hover {
            background-color: #e67300;
        }

        .button.secondary {
            background-color: #173822;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tag {
            background-color: #f0f0f0;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            color: #333;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .tag-close {
            cursor: pointer;
            color: #999;
            font-weight: 600;
        }

        .recommended {
            font-size: 18px;
            color: #173822;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .recipes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
        }

        .recipe {
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .recipe img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }

        .recipe-title {
            font-size: 16px;
            color: #333;
            margin: 10px 8px 4px;
        }

        .recipe-subtitle {
            font-size: 13px;
            color: #EF8A37;
            margin-bottom: 12px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            border-top: 1px solid #eee;
            z-index: 50;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #173822;
            font-size: 12px;
            cursor: pointer;
            gap: 4px;
        }

        .nav-icon {
            font-size: 22px;
            line-height: 1;
        }

        /* Responsiveness and small-screen tweaks */
        @media (max-width: 480px) {
            .content {
                padding: 14px;
            }
            .cooking-question {
                font-size: 16px;
            }
            .search-input {
                padding: 10px 36px 10px 10px;
                font-size: 15px;
            }
            .button {
                font-size: 15px;
                padding: 10px;
                flex-basis: 100%;
            }
            .recipe img {
                height: 110px;
            }
            .recipes {
                padding-bottom: 70px;
            }
        }
    </style>
</head>
<body>
<div class="header">MySecretChef</div>
<div class="content">
    <div class="cooking-question">What are we cooking today?</div>
    <div class="search-container">

        <input type="text" class="search-input" placeholder="Search Ingredients">
    </div>
    <div class="buttons">
        <button class="button">Add Ingredient</button>
        <button class="button secondary">Add from Inventory</button>
    </div>
    <div class="tags">
        <div class="tag">Tomato <span class="tag-close">&times;</span></div>
        <div class="tag">Basil <span class="tag-close">&times;</span></div>
    </div>
    <div class="recommended">Recommended Recipes</div>
    <div class="recipes">
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Caprese Salad">
            <div class="recipe-title">Caprese Salad</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Margherita Pizza">
            <div class="recipe-title">Margherita Pizza</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Spaghetti Carbonara"> <!-- Replace with actual image -->
            <div class="recipe-title">Spaghetti Carbonara</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Tiramisu"> <!-- Replace with actual image -->
            <div class="recipe-title">Tiramisu</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Risotto"> <!-- Replace with actual image -->
            <div class="recipe-title">Risotto</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Bruschetta"> <!-- Replace with actual image -->
            <div class="recipe-title">Bruschetta</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Spaghetti Carbonara"> <!-- Replace with actual image -->
            <div class="recipe-title">Spaghetti Carbonara</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Tiramisu"> <!-- Replace with actual image -->
            <div class="recipe-title">Tiramisu</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Risotto"> <!-- Replace with actual image -->
            <div class="recipe-title">Risotto</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
        <div class="recipe">
            <img src="img/garlic_bread.png" alt="Bruschetta"> <!-- Replace with actual image -->
            <div class="recipe-title">Bruschetta</div>
            <div class="recipe-subtitle">All ingredients available</div>
        </div>
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
</body>
</html>