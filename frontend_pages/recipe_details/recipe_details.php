<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Ricetta – MySecretChef</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="main-container">

    <nav class="sidebar">
        <ul>
            <li><a href="dashboard_view.php"><span>🏠</span> Home</a></li>
            <li><a href="inventory_view.php"><span>📋</span> Inventario</a></li>
            <li><a href="recipes.php"><span>🍝</span> Ricette</a></li>
            <li><a href="favorites_page_view.php"><span>❤️</span> Preferiti</a></li>
        </ul>
    </nav>

    <main>
        <h1>Pasta al Pomodoro</h1>
        <div class="recipe-box">
            <img src="img/spaghetti_pomo.png" class="recipe-hero" alt="">

            <p class="prep-time">Tempo di preparazione: 20 min</p>

            <h2>Ingredienti</h2>
            <ul class="ingredients-list">
                <li><input type="checkbox"> Spaghetti 200g</li>
                <li><input type="checkbox"> Pomodoro pelato 300g</li>
                <li><input type="checkbox"> Basilico fresco</li>
                <li><input type="checkbox"> Aglio</li>
                <li><input type="checkbox"> Olio d'oliva</li>
                <li><input type="checkbox"> Sale q.b.</li>
            </ul>

            <h2>Procedimento</h2>
            <ol class="procedure-list">
                <li>Bollire l'acqua e salarla</li>
                <li>Soffriggere aglio e olio</li>
                <li>Aggiungere pomodoro e cuocere 10 min</li>
                <li>Cuocere la pasta</li>
                <li>Mantecare con basilico fresco</li>
            </ol>
        </div>

        <div class="recipe-box" style="margin-top:2rem;">
            <h2>Ti potrebbero piacere</h2>
            <div class="recipe-grid">

                <div class="recipe-card">
                    <img src="img/spaghetti_fung.png">
                    <h3>Insalata di Pomodoro</h3>
                    <p>10 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/spaghetti_vongo.png">
                    <h3>Pasta al Pesto</h3>
                    <p>18 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>

                <div class="recipe-card">
                    <img src="img/ChatGPT%20Image%2024%20ott%202025,%2014_55_56.png">
                    <h3>Bruschetta</h3>
                    <p>8 min</p>
                    <br>
                    <button>Dettagli</button>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
