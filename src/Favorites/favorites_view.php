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
            <li><a href="../Dashboard/dashboard.php">Home</a></li>
            <li><a href="inventory_view.php">Inventario</a

                ></li>
            <li><a href="favorites.php" class="active">Preferiti</a></li>
        </ul>
    </nav>

    <main>
        <h1>I tuoi Preferiti</h1>
        <div class="recipe-box">
            <h2>Ricette salvate <span class="recipe-count">(<?php echo $total; ?>)</span></h2>

            <?php if (empty($favorites)): ?>
                <p class="no-recipes">Nessuna ricetta preferita al momento. Aggiungine qualcuna!</p>
            <?php else: ?>
                <div class="recipe-grid">
                    <?php foreach ($favorites as $r): ?>
                        <div class="recipe-card">
                            <img src="<?php echo htmlspecialchars($r['image_url'] ?? 'img/placeholder.png'); ?>"
                                 loading="lazy" alt="<?php echo htmlspecialchars($r['name']); ?>">
                            <h3><?php echo htmlspecialchars($r['name']); ?></h3>
                            <p>Pronta in <?php echo $r['prep_time'] ?: 'N/A'; ?> min</p>
                            <br>
                            <a href="recipe_detail.php?id=<?php echo $r['id']; ?>" style="text-decoration:none;">
                                <button class="btn-details">Dettagli</button>
                            </a>
                            <a href="favorites.php?action=remove&recipe_id=<?php echo $r['id']; ?>&page=<?php echo $page; ?>"
                               onclick="return confirm('Rimuovere dai preferiti?');"
                               style="text-decoration:none; margin-top:.5rem; display:inline-block;">
                                <button class="remove-favorite">Rimuovi</button>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- PAGINAZIONE -->
                <?php if ($pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="favorites.php?page=<?php echo $page - 1; ?>" class="page-btn">Indietro</a>
                        <?php endif; ?>
                        <span class="page-info">Pagina <?php echo $page; ?> di <?php echo $pages; ?></span>
                        <?php if ($page < $pages): ?>
                            <a href="favorites.php?page=<?php echo $page + 1; ?>" class="page-btn">Avanti</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>