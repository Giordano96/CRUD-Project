<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferiti – MySecretChef</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="main-container">

    <nav class="sidebar">
        <ul>
            <li><a href="dashboard_view.php"><span>🏠</span> Home</a></li>
            <li><a href="inventory_view.php"><span>📋</span> Inventario</a></li>
            <li><a href="recipes.php"><span>🍝</span> Ricette</a></li>
            <li><a href="favorites_page_view.php" class="active"><span>❤️</span> Preferiti</a></li>
        </ul>
    </nav>

    <main>
        <h1>I tuoi Preferiti</h1>
        <div class="recipe-box">
            <h2>Ricette salvate</h2>
            <div class="recipe-grid">
                <div class="recipe-card">
                    <img src="img/pomo_basilico.png" alt="">
                    <h3>Pasta al Pomodoro</h3>
                    <p>Pronta in 20 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/garlic_bread.png" alt="">
                    <h3>Baracca</h3>
                    <p>Pronta in 15 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/tiramisu.png" alt="">
                    <h3>Tiramisù</h3>
                    <p>Dolce classico italiano</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/pesto.png" alt="">
                    <h3>Pasta al Pesto</h3>
                    <p>Pronta in 18 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
