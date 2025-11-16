<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>My Secret Chef - Login / Sign Up</title>
</head>
<body>
<div class="container">

    <div class="tabs">
        <span class="active" onclick="showForm('login')">Login</span>
        <span onclick="showForm('signup')">Sign up</span>
    </div>

    <!-- LOGIN FORM -->
    <form id="login" class="form active" method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <label>Email</label>
        <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="login-button" type="submit">Login</button>
    </form>

    <!-- SIGNUP FORM (solo frontend, action da gestire separatamente) -->
    <form id="signup" class="form" method="post" action="../Sign/signup.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

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
</script>
</body>
</html>