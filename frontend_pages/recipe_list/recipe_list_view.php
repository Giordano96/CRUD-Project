<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ricette – MySecretChef</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="main-container">
    <nav class="sidebar">
        <ul>
            <li><a href="dashboard_view.php"><span>🏠</span> Home</a></li>
            <li><a href="inventory_view.php"><span>📋</span> Inventario</a></li>
            <li><a href="recipe_list.php" class="active"><span>🍝</span> Ricette</a></li>
            <li><a href="favorites_page_view.php"><span>❤️</span> Preferiti</a></li>
        </ul>
    </nav>
    <main>
        <h1>Ricette</h1>

        <form class="add-form" style="margin-bottom:1.2rem;">
            <input type="text" placeholder="Cerca ricetta…" />
            <select>
                <option value="">Tutte le categorie</option>
                <option value="pasta">Pasta</option>
                <option value="antipasti">Antipasti</option>
                <option value="dolci">Dolci</option>
            </select>
            <button type="submit">Filtra</button>
        </form>

        <div class="recipe-box">
            <h2>Scopri nuove ricette</h2>
            <div class="recipe-grid">
                <div class="recipe-card">
                    <img src="img/spaghetti_pomo.png">
                    <h3>Spaghetti al Pomodoro</h3>
                    <p>20 min</p>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/riso_funghi.png">
                    <h3>Riso fonghi</h3>
                    <p>18 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/spaghetti_carbo.png">
                    <h3>Spaghetti carbonara</h3>
                    <p>8 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/tiramisu.png">
                    <h3>Tiramisù</h3>
                    <p>15 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
