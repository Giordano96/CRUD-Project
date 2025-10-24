<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Secret Chef- Dettaglio Ricetta</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <nav class="navbar">
        <div class="logo">My Secret Chef</div>
        <ul class="nav-links">
            <li><a href="#" class="active" style="color: #ffa200">Home</a></li>
            <li><a href="#">Frigo</a></li>
            <li><a href="#">Ricette</a></li>
            <li><a href="#">Lista della spesa</a></li>
        </ul>
        <div class="profile-icon">👤</div>
    </nav>

    <div class="recipe-details">
        <img src="img/spaghetti_pomo.png" alt="Spaghetti al Pomodoro Fresco" class="recipe-image">
        <h1>Spaghetti al Pomodoro Fresco</h1>
        <p class="prep-time">Pasta: 20 min</p>

        <div class="sections">
            <h2>Ingredienti</h2>
            <ul class="ingredients-list">
                <li><input type="checkbox" checked> Spaghetti (320g)</li>
                <li><input type="checkbox" checked> Pomodori maturi (500g)</li>
                <li><input type="checkbox" checked> Aglio (2 spicchi)</li>
                <li><input type="checkbox" checked> Basilico fresco (1 mazzetto)</li>
                <li><input type="checkbox" checked> Olio extravergine d'oliva (4 cucchiai)</li>
                <li><input type="checkbox" checked> Sale e pepe q.b.</li>
            </ul>

            <h2>Procedimento</h2>
            <ol class="procedure-list">
                <li>Cuocere la pasta in abbondante acqua salata.</li>
                <li>Nel frattempo, preparare il sugo fresco.</li>
                <li>Scolare la pasta al dente e saltarla in padella con il sugo.</li>
                <li>Servire con basilico fresco.</li>
            </ol>

            <h2>Valori nutrizionali</h2>
            <ul class="nutrition-list">
                <li>kcal: 450</li>
                <li>Proteine: 15g</li>
                <li>Carboidrati: 60g</li>
                <li>Grassi: 18g</li>
            </ul>
        </div>

        <div class="suggestions">
            <h3>Ti manca qualcosa? Scopri ricette simili!</h3>
            <div class="suggestion-grid">
                <div class="card">
                    <img src="img/spaghetti_pest.png" alt="Spaghetti al Pesto">
                    <p>Spaghetti al Pesto</p>
                    <p>Pasta: 25 min</p>
                </div>
                <div class="card">
                    <img src="img/spaghetti_vongo.png" alt="Spaghetti alle Vongole">
                    <p>Spaghetti alle Vongole</p>
                    <p>Pasta: 30 min</p>
                </div>
                <div class="card">
                    <img src="img/spaghetti_fung.png" alt="Spaghetti ai Funghi">
                    <p>Spaghetti ai Funghi</p>
                    <p>Pasta: 35 min</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>