<?php
session_start();

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>


// FILE: register.php (shows form and handles POST)
<?php
require_once 'db.php';
require_once 'csrf.php';
session_start();

// If user already logged in, redirect
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Richiesta non valida (CSRF).';
    }

    // Basic validation
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (strlen($username) < 3 || strlen($username) > 50) $errors[] = 'Username tra 3 e 50 caratteri.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email non valida.';
    if (strlen($password) < 8) $errors[] = 'La password deve avere almeno 8 caratteri.';
    if ($password !== $password_confirm) $errors[] = 'Le password non coincidono.';

    if (empty($errors)) {
        // Check uniqueness
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
        $stmt->execute([':u' => $username, ':e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username o email già utilizzati.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, created_at) VALUES (:u, :e, :p, NOW())');
            $insert->execute([':u' => $username, ':e' => $email, ':p' => $hash]);
            // Auto-login after register (optional)
            $userId = $pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit;
        }
    }
}

// HTML form (simple)
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrazione</title>
  <style>body{font-family:system-ui,Arial;margin:40px}form{max-width:420px}input{display:block;margin:8px 0;padding:8px;width:100%}</style>
</head>
<body>
  <h1>Registrati</h1>
  <?php if (!empty($errors)): ?>
    <div style="color:red">
      <ul>
      <?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <input name="csrf_token" type="hidden" value="<?php echo htmlspecialchars(csrf_token()); ?>">
    <label>Username<input name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>"></label>
    <label>Email<input name="email" type="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>"></label>
    <label>Password<input name="password" type="password" required></label>
    <label>Conferma password<input name="password_confirm" type="password" required></label>
    <button type="submit">Registrati</button>
  </form>
  <p>Hai già un account? <a href="login.php">Login</a></p>
</body>
</html>


// FILE: login.php (form + authenticate)
<?php
require_once 'db.php';
require_once 'csrf.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Richiesta non valida (CSRF).';
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email non valida.';
    if (empty($password)) $errors[] = 'Inserisci la password.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            // Password ok
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Credenziali non valide.';
        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login</title>
  <style>body{font-family:system-ui,Arial;margin:40px}form{max-width:420px}input{display:block;margin:8px 0;padding:8px;width:100%}</style>
</head>
<body>
  <h1>Login</h1>
  <?php if (!empty($errors)): ?>
    <div style="color:red">
      <ul>
      <?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <input name="csrf_token" type="hidden" value="<?php echo htmlspecialchars(csrf_token()); ?>">
    <label>Email<input name="email" type="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>"></label>
    <label>Password<input name="password" type="password" required></label>
    <button type="submit">Accedi</button>
  </form>
  <p>Non hai un account? <a href="register.php">Registrati</a></p>
</body>
</html>


// FILE: dashboard.php (protected page)
<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="it">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard</title></head>
<body>
  <h1>Benvenuto, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
  <p><a href="logout.php">Logout</a></p>
</body>
</html>


// FILE: logout.php
<?php
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}
session_destroy();
header('Location: login.php');
exit;
?>