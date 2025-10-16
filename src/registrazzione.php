<?php
session_start();

// Helper: generate CSRF token
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper: verify CSRF token
function verify_csrf_token(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

// Helper: simple rate-limiting (per session)
if (!isset($_SESSION['reg_attempts'])) {
    $_SESSION['reg_attempts'] = 0;
    $_SESSION['reg_first_attempt'] = time();
}

if (time() - ($_SESSION['reg_first_attempt'] ?? time()) > 3600) {
    // reset every hour
    $_SESSION['reg_attempts'] = 0;
    $_SESSION['reg_first_attempt'] = time();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic rate limit: max 10 attempts per hour
    $_SESSION['reg_attempts']++;
    if ($_SESSION['reg_attempts'] > 10) {
        $errors[] = 'Troppe richieste. Riprova più tardi.';
    } else {
        // Get and trim inputs
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrf = $_POST['csrf_token'] ?? null;

        // CSRF check
        if (!verify_csrf_token($csrf)) {
            $errors[] = 'Token CSRF non valido. Ricarica la pagina e riprova.';
        }

        // Validate username
        if ($username === '') {
            $errors[] = 'Username richiesto.';
        } elseif (strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = 'Username deve essere tra 3 e 30 caratteri.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $username)) {
            $errors[] = 'Username contiene caratteri non permessi. Usa lettere, numeri, underscore, punti o trattini.';
        }

        // Validate email
        if ($email === '') {
            $errors[] = 'Email richiesta.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Formato email non valido.';
        }

        // Validate password strength
        $pwMinLength = 8;
        if ($password === '') {
            $errors[] = 'Password richiesta.';
        } else {
            if (strlen($password) < $pwMinLength) {
                $errors[] = "Password troppo corta. Minimo $pwMinLength caratteri.";
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Password deve contenere almeno una lettera maiuscola.';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Password deve contenere almeno una lettera minuscola.';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password deve contenere almeno una cifra.';
            }
            if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                $errors[] = 'Password deve contenere almeno un carattere speciale (es. !@#$%&*).';
            }
        }

        // If no validation errors so far, attempt to insert user
        if (empty($errors)) {
            try {
                $pdo = new PDO($dsn, $dbUser, $dbPass, $pdoOptions);

                // Optional: ensure unique constraints exist on the table
                // Table creation SQL (run once separately):
                // CREATE TABLE users (
                //   id INT AUTO_INCREMENT PRIMARY KEY,
                //   username VARCHAR(30) NOT NULL UNIQUE,
                //   email VARCHAR(255) NOT NULL UNIQUE,
                //   password_hash VARCHAR(255) NOT NULL,
                //   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                // Check for existing username or email
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
                $stmt->execute([':username' => $username, ':email' => $email]);
                $existing = $stmt->fetch();
                if ($existing) {
                    $errors[] = 'Username o email già in uso.';
                } else {
                    // Hash password with bcrypt
                    // You can increase cost if desired, but keep an eye on performance
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                    $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
                    $insert->execute([
                        ':username' => $username,
                        ':email' => $email,
                        ':password_hash' => $passwordHash,
                    ]);

                    $success = true;
                    // Optionally, regenerate CSRF token after success
                    unset($_SESSION['csrf_token']);
                }

            } catch (PDOException $e) {
                // Do not leak details to user in production
                error_log('DB error: ' . $e->getMessage());
                $errors[] = 'Errore interno. Riprova più tardi.';
            }
        }
    }
}

// If GET or on error, show the form
$csrfToken = generate_csrf_token();
?>

<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registrazione</title>
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial;margin:40px;background:#f7f7fb}
        .card{max-width:520px;margin:0 auto;background:white;padding:24px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.06)}
        input,button{width:100%;padding:10px;margin:6px 0;border-radius:6px;border:1px solid #d9d9e0}
        .error{background:#fff0f0;color:#9b1c1c;padding:10px;border-radius:6px;margin-bottom:10px}
        .success{background:#f0fff4;color:#14632a;padding:10px;border-radius:6px;margin-bottom:10px}
        .pw-requirements{font-size:13px;color:#555}
    </style>
</head>
<body>
<div class="card">
    <h2>Registrazione</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">Registrazione completata. Ora puoi effettuare il login.</div>
    <?php else: ?>

    <form id="regForm" method="post" novalidate>
        <label for="username">Username</label>
        <input id="username" name="username" type="text" maxlength="30" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required aria-describedby="pwhelp">
        <div id="pwhelp" class="pw-requirements">
            Minimo 8 caratteri, almeno una maiuscola, una minuscola, un numero e un carattere speciale.
        </div>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <button type="submit">Registrati</button>
    </form>

    <script>
    // Client-side password check (user convenience only; server validates again)
    document.getElementById('regForm').addEventListener('submit', function(e){
        var pw = document.getElementById('password').value;
        var errors = [];
        if (pw.length < 8) errors.push('Password troppo corta (min 8).');
        if (!/[A-Z]/.test(pw)) errors.push('Deve contenere una lettera maiuscola.');
        if (!/[a-z]/.test(pw)) errors.push('Deve contenere una lettera minuscola.');
        if (!/[0-9]/.test(pw)) errors.push('Deve contenere una cifra.');
        if (!/[^a-zA-Z0-9]/.test(pw)) errors.push('Deve contenere un carattere speciale.');
        if (errors.length) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });
    </script>

    <?php endif; ?>
</div>
</body>
</html>
