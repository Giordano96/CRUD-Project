<?php
require '../DbConnector.php';
session_start();
/*
if (!isset($_SESSION['user_id']) || !isset($_GET['ingredient_id'])) {
    header('Location: login.php');
}*/

$conn = new DbConnector('localhost', 'root', '', 'MySecretChef');

$stmt = $conn->prepare("SELECT 
                            r.name AS recipe_name,
                            GROUP_CONCAT(DISTINCT CONCAT(ri.quantity, ' ', ri.unit, ' ', i.name) ORDER BY i.name SEPARATOR ', ') AS ingredients,
                            r.prep_time AS prep_time,
                            r.image_url AS image_url,
                            r.instructions AS instructions,
                            GROUP_CONCAT(DISTINCT CONCAT(n.name, ': ', rn.value, 'g') ORDER BY n.id SEPARATOR ', ') AS nutrients
                        FROM 
                            recipe r
                        LEFT JOIN 
                            recipe_ingredient ri ON r.id = ri.recipe_id
                        LEFT JOIN 
                            ingredient i ON ri.ingredient_id = i.id
                        LEFT JOIN 
                            recipe_nutrient rn ON r.id = rn.recipe_id
                        LEFT JOIN 
                            nutrient n ON rn.nutrient_id = n.id
                        WHERE 
                            r.id = 1  -- Sostituisci con l'ID desiderato
                        GROUP BY 
                            r.id, r.name, r.prep_time, r.image_url, r.instructions;");
//$stmt->bindParam(':recipe_id', $_POST["recipe_id"], PDO::PARAM_INT);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);
$ingredient = explode(',', $item['ingredients']);
$nutrecipe = explode(',', $item['nutrients']);
$instrucsion = $item['instructions'];

$stmt = $conn->prepare("SELECT 
                                    i.name AS ingrediente,
                                    inv.quantity AS quantità,
                                    inv.expiration_date AS scadenza
                                FROM 
                                    inventory inv
                                JOIN 
                                    ingredient i ON inv.ingredient_id = i.id
                                WHERE 
                                    inv.user_id = 1");
$stmt->execute();
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=arrow_back,box,favorite,view_cozy" />
    <title>My Secret Chef - Recipe details</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
<div class="header">
    MySecretChef
</div>

<img src="<?php echo $item['image_url'] ?>" class="recipe-img" alt="Recipe Image">

<div class="recipe-header">
    <h2><?php echo $item['recipe_name'] ?></h2>
    <div class="recipe-info">
        <?php echo $item['prep_time'] ?> min
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

        <?php foreach($ingredient as $ing):
            $ing = trim($ing);

            // Cattura quantità + unità all'inizio, anche con trattini o spazi vari
            preg_match('/^([\d.,\s]+(?:\s*(?:di|g|kg|ml|l|cl|dl|cucchiaio|cucchiaini|pizzico|q\.?b\.?|qb|fogli[ae]|spicchi?o|teste?))?)\s*-?\s*-?\s*(.+)$/i', $ing, $m);

            if ($m) {
                $quantita = trim($m[1]);
                $nome     = trim($m[2]);
            } else {
                $quantita = '';
                $nome     = $ing;
            }

            // Controllo presenza in inventario (tollerante)
            $found = false;
            foreach($inventory as $item) {
                $db_name = trim($item['ingrediente']);
                if (strcasecmp($db_name, $nome) === 0 || stripos($db_name, $nome) !== false || stripos($nome, $db_name) !== false) {
                    $found = true;
                    break;
                }
            }
            ?>

            <div class="ingredient-item">
                <input type="checkbox" <?= $found ? 'checked' : 'disabled' ?> class="ingredient-checkbox">

                <?php if ($quantita): ?>
                    <p><?= htmlspecialchars($nome) ?> — <?= htmlspecialchars($quantita) ?></p>
                <?php else: ?>
                    <?= htmlspecialchars($ing) ?>
                <?php endif; ?>
            </div>

        <?php endforeach; ?>
    </div>

    <div id="procedure-section" class="tab-section">
        <h3>Procedure</h3>
        <p><?php echo $instrucsion ?></p>
    </div>

    <div id="nutrition-section" class="tab-section">
        <h3>Nutrition</h3>
        <?php
        for($i=0; count($nutrecipe)>$i; $i++){
            echo "<p>".$nutrecipe[$i]."</p>";
        }
        ?>
    </div>

</div>

<div class="bottom-nav">
    <div class="nav-item" id="Home">
        <span class="material-symbols-outlined">view_cozy</span>
        Home
    </div>
    <div class="nav-item" id="Inventory">
        <span class="material-symbols-outlined">box</span>
        Inventory
    </div>
    <div class="nav-item" id="Favorites">
        <span class="material-symbols-outlined">favorite</span>
        Favorites
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
<script>
    document.getElementById("Home").onclick = function() {
        window.location.href = "../Dashboard/dashboard_view.php"; // Sostituisci con il tuo URL
    };
    document.getElementById("Inventory").onclick = function() {
        window.location.href = "../Inventory/inventory_view.php"; // Sostituisci con il tuo URL
    };
    document.getElementById("Favorites").onclick = function() {
        window.location.href = "../Favorites/favorites_page_view.php"; // Sostituisci con il tuo URL
    };
</script>