<?php
require '../DbConnector.php';
session_start();

$conn = new DbConnector('localhost', 'root', '', 'MySecretChef');
$user_id = 1; // TODO: $_SESSION['user_id']

// Genera CSRF token separati se non esistono
if (!isset($_SESSION['csrf_token_add'])) {
    $_SESSION['csrf_token_add'] = md5(uniqid(rand(), TRUE));
}
if (!isset($_SESSION['csrf_token_delete'])) {
    $_SESSION['csrf_token_delete'] = md5(uniqid(rand(), TRUE));
}

// === GESTIONE ELIMINAZIONE ===
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['ingredient_id'])) {
    if (!isset($_POST['csrf_token_delete']) || $_POST['csrf_token_delete'] !== $_SESSION['csrf_token_delete']) {
        die('Invalid CSRF token for delete');
    }
    $ingredient_id = (int)$_POST['ingredient_id'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE user_id = :user_id AND ingredient_id = :ingredient_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
    $stmt->execute();
    header('Location: inventory_view.php');
    exit;
}

// === GESTIONE INSERIMENTO ===
if (isset($_POST['action']) && $_POST['action'] === 'add' && isset($_POST['ingredient_id'])) {
    if (!isset($_POST['csrf_token_add']) || $_POST['csrf_token_add'] !== $_SESSION['csrf_token_add']) {
        die('Invalid CSRF token for add');
    }
    $ingredient_id = (int)$_POST['ingredient_id'];
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;

    // Controllo se già esiste
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM inventory WHERE user_id = :user_id AND ingredient_id = :ingredient_id");
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
    $check_stmt->execute();
    if ($check_stmt->fetchColumn() > 0) {
        // Aggiorna
        $stmt = $conn->prepare("UPDATE inventory SET expiration_date = :expiration_date WHERE user_id = :user_id AND ingredient_id = :ingredient_id");
    } else {
        // Inserisci
        $stmt = $conn->prepare("INSERT INTO inventory (user_id, ingredient_id, expiration_date) VALUES (:user_id, :ingredient_id, :expiration_date)");
    }
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
    $stmt->bindParam(':expiration_date', $expiration_date);
    $stmt->execute();
    header('Location: inventory_view.php');
    exit;
}

// === RICERCA AJAX ===
if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $q = trim($_GET['ajax_search']);
    if (strlen($q) < 2) {
        echo json_encode([]);
        exit;
    }
    $stmt = $conn->prepare("SELECT id, name FROM ingredient WHERE name LIKE :term ORDER BY name LIMIT 15");
    $stmt->execute([':term' => '%' . $q . '%']);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// === CARICA INVENTARIO ===
$stmt = $conn->prepare("
    SELECT inv.ingredient_id, inv.expiration_date, i.name AS ingredient_name
    FROM inventory inv
    JOIN ingredient i ON inv.ingredient_id = i.id
    WHERE inv.user_id = :user_id
    ORDER BY inv.expiration_date ASC
");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$inventory_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=arrow_back,box,favorite,view_cozy" />
    <title>Dispensa - MySecretChef</title>
    <link rel="stylesheet" href="styles_inventory.css">
    <style>
        .autocomplete-wrapper { position: relative; }
        #autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 300px;
            overflow-y: auto;
            z-index: 999;
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
            display: none;
        }
        .autocomplete-item {
            padding: 16px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        .autocomplete-item:hover { background-color: #fdf8f0; }
    </style>
</head>
<body>

<div class="header">
    <span class="back-icon" onclick="history.back()">Back</span>
    MySecretChef
</div>

<div class="inventory-wrapper">

    <div class="autocomplete-wrapper">
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search ingredients" autocomplete="off">
        </div>
        <div id="autocomplete-list"></div>
    </div>

    <form class="add-form" method="POST">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="csrf_token_add" value="<?= $_SESSION['csrf_token_add'] ?>">
        <input type="hidden" name="ingredient_id" id="selectedIngredientId" value="">
        <input type="date" name="expiration_date" class="add-date">
        <button class="add-btn" type="submit">Add</button>
    </form>

    <div class="table-header">
        <span>Ingredients</span>
        <span class="expiry-title">Expiring date</span>
    </div>

    <div class="ingredients-list">
        <?php if (empty($inventory_items)): ?>
            <div style="text-align:center; padding:60px 20px; color:#999; font-size:15px;">
                La tua dispensa è vuota
            </div>
        <?php else: foreach ($inventory_items as $item): ?>
            <div class="ingredient-row">
                <div class="ingredient-left">
                    <span class="ingredient-name"><?= htmlspecialchars($item['ingredient_name']) ?></span>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="csrf_token_delete" value="<?= $_SESSION['csrf_token_delete'] ?>">
                        <input type="hidden" name="ingredient_id" value="<?= $item['ingredient_id'] ?>">
                        <button type="submit" class="delete-inline-btn">
                            Delete
                        </button>
                    </form>
                </div>
                <span class="expiry-value">
                    <?= $item['expiration_date'] ? date('d/m/Y', strtotime($item['expiration_date'])) : 'N/A' ?>
                </span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="bottom-nav">
    <div class="nav-item" onclick="location.href='../Dashboard/dashboard_view.php'">
        <span class="material-symbols-outlined">view_cozy</span>
        Home
    </div>
    <div class="nav-item active">
        <span class="material-symbols-outlined">box</span>
        Inventory
    </div>
    <div class="nav-item" onclick="location.href='../Favorites/favorites_page_view.php'">
        <span class="material-symbols-outlined">favorite</span>
        Favorites
    </div>
</div>

<script>
    const input = document.getElementById('searchInput');
    const list = document.getElementById('autocomplete-list');
    const hiddenId = document.getElementById('selectedIngredientId');

    input.addEventListener('input', function() {
        const q = this.value.trim();

        if (q.length < 2) {
            list.style.display = 'none';
            list.innerHTML = '';
            return;
        }

        fetch(`?ajax_search=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                list.innerHTML = '';
                if (data.length === 0) {
                    list.style.display = 'none';
                    return;
                }

                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    div.textContent = item.name;
                    div.onclick = () => {
                        input.value = item.name;
                        hiddenId.value = item.id;
                        list.style.display = 'none';
                    };
                    list.appendChild(div);
                });
                list.style.display = 'block';
            });
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.autocomplete-wrapper')) {
            list.style.display = 'none';
        }
    });
</script>

</body>
</html>