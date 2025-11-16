<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" >
    <title>My Secret Chef - Login / Sign Up</title>
</head>
<body>
<div class="container">

    <div class="tabs">
        <span style="color: #173822" class="active" onclick="showForm('login')">Login</span>
        <span style="color: #173822" onclick="showForm('signup')">Sign up</span>
    </div>
    <form id="login" class="form active">
        <label>Email</label>
        <input type="email">
        <label>Password</label>
        <input type="password">
        <button class="login-button" type="submit">Login</button>
    </form>
    <form id="signup" class="form">
        <label>Username</label>
        <input type="text">
        <label>Email</label>
        <input type="email">
        <label>Password</label>
        <input type="password">
        <label>Confirm password</label>
        <input type="password">
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