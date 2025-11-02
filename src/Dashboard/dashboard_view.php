<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – MySecretChef</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="main-container">
    <nav class="sidebar">
        <ul>
            <li><a href="dashboard_view.php" class="active"><span>🏠</span> Home</a></li>
            <li><a href="inventory_view.php"><span>📋</span> Inventario</a></li>
            <li><a href="recipes.php"><span>🍝</span> Ricette</a></li>
            <li><a href="favorites.php"><span>❤️</span> Preferiti</a></li>
        </ul>
    </nav>

    <main>

        <h1>Benvenuto, Clara!</h1>
        <form class="add-form">
            <input list="ingredients" placeholder="Cerca ingrediente…" required>
            <datalist id="ingredients">
                <option value="Pomodoro">
                <option value="Basilico">
                <option value="Aglio">
                <option value="Olio d’oliva">
            </datalist>
            <button type="submit">+ Aggiungi ingrediente</button>
        </form>

        <div class="tags" style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <span class="tag">Pomodoro</span>
            <span class="tag">Basilico</span>
            <span class="tag">Aglio</span>
            <span class="tag">Olio d’oliva</span>
        </div>

        <div class="recipe-box">
            <h2>Ricette suggerite❗</h2>
            <div class="recipe-grid">
                <div class="recipe-card">
                    <img src="img/pomo_basilico.png">
                    <h3>Pasta al Pomodoro</h3>
                    <p>Pronta in 20 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/garlic_bread.png">
                    <h3>Pane all’Aglio</h3>
                    <p>Pronta in 15 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/insalata_pomo.png">
                    <h3>Insalata di Pomodoro</h3>
                    <p>Pronta in 10 min</p>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/zuppa_pomo.png">
                    <h3>Zuppa di Pomodoro</h3>
                    <p>Pronta in 30 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>
            </div>
            <br>
            <h2>Ricette popolari⭐</h2>
            <div class="recipe-grid">
                <div class="recipe-card">
                    <img src="img/pomo_basilico.png">
                    <h3>Pasta al Pomodoro</h3>
                    <p>Pronta in 20 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/garlic_bread.png">
                    <h3>Pane all’Aglio</h3>
                    <p>Pronta in 15 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/insalata_pomo.png">
                    <h3>Insalata di Pomodoro</h3>
                    <p>Pronta in 10 min</p>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/zuppa_pomo.png">
                    <h3>Zuppa di Pomodoro</h3>
                    <p>Pronta in 30 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
