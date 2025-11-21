
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles_login_sign.css">
        <link href='https://fonts.googleapis.com/css?family=Plus Jakarta Sans' rel='stylesheet'>
        <title>My Secret Chef - Login / Sign Up</title>
    </head>
<body>
<div class="container">
    <!-- SUCCESS MESSAGE -->
    <div class="tabs">
        <span class="<?php echo $active_tab === 'login' ? 'active' : ''; ?>" onclick="showForm('login')">Login</span>
        <span class="<?php echo $active_tab === 'signup' ? 'active' : ''; ?>" onclick="showForm('signup')">Sign up</span>
    </div>


    <!-- LOGIN FORM -->
    <form id="login" class="form <?php echo $active_tab === 'login' ? 'active' : ''; ?>" method="post" action="login_sign.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="action" value="login">
        <?php if (!empty($error) && $active_tab === 'login'): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <label>Email</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="login-button" type="submit">Login</button>
    </form>

    <!-- SUCCESS MESSAGE -->
    <?php if (!empty($success)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- SIGNUP FORM -->
    <form id="signup" class="form <?php echo $active_tab === 'signup' ? 'active' : ''; ?>" method="post" action="login_sign.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="action" value="signup">

        <?php if (!empty($error) && $active_tab === 'signup'): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <label>Username</label>
        <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">

        <label>Email</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Sign up</button>
    </form>
</div>
<script>
    function showForm(formType) {
        const tabs = document.querySelectorAll('.tabs span');
        const forms = document.querySelectorAll('.form');

        tabs.forEach(tab => tab.classList.remove('active'));
        forms.forEach(form => form.classList.remove('active'));

        if (formType === 'login') {
            tabs[0].classList.add('active');
            document.getElementById('login').classList.add('active');
        } else {
            tabs[1].classList.add('active');
            document.getElementById('signup').classList.add('active');
        }
    }

    // Ripristina il tab corretto dopo un errore (PHP ci dice qual era attivo)
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($active_tab === 'signup'): ?>
        showForm('signup');
        <?php else: ?>
        showForm('login');
        <?php endif; ?>
    });
</script>
</body>
</html>