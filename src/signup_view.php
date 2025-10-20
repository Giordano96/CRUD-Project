<?php
?>
<html>
<head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?display=swap&family=Inter%3Awght%40400%3B500%3B700%3B900&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900" />
    <link rel="stylesheet" href="styles.css" />
    <title>MySecretChef - Sign Up</title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />
</head>
<body>
<div class="container">
    <div class="header-image"></div>
    <div class="hero-section">
        <div class="hero-content">
            <h1>MySecretChef</h1>
            <h2>Cook with what you have, without waste 🍔</h2>
        </div>
        <button class="get-started-btn">Get Started</button>
    </div>
    <div class="welcome-section">
        <div class="welcome-content">
            <h3>Welcome to MySecretChef</h3>
            <p>Your virtual fridge assistant</p>
        </div>
    </div>
    <div class="nav-tabs">
        <a href="login.php">Log In</a>
        <a href="signup.php" class="active-tab">Sign Up</a>
    </div>
    <?php if (isset($error) && $error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="signup.php" class="form-container">
        <input type="hidden" name="csrf_token" value="<?php echo isset($csrf_token) ? htmlspecialchars($csrf_token) : ''; ?>">
        <div class="form-group">
            <label>
                <input name="username" placeholder="Username" class="form-input" value="" />
            </label>
        </div>
        <div class="form-group">
            <label>
                <input name="email" placeholder="Email" class="form-input" value="" />
            </label>
        </div>
        <div class="form-group">
            <label>
                <input name="password" type="password" placeholder="Password" class="form-input" value="" />
            </label>
        </div>
        <button type="submit" class="submit-btn">Sign Up</button>
    </form>
</div>
</body>
</html>
