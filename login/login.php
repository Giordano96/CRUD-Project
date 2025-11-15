<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: url() no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            backdrop-filter: blur(6px);
        }

        .container {
            width: 400px;
            background-color: #cc7a00; /* arancione simile all’immagine */
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: all 0.4s ease;
        }

        .tab {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }

        .tab button {
            background: none;
            border: none;
            font-size: 16px;
            color: white;
            cursor: pointer;
            padding: 10px;
            transition: 0.3s;
        }

        .tab button.active {
            border-bottom: 2px solid white;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            color: white;
            font-size: 14px;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 25px;
            background-color: #ddd;
            outline: none;
        }

        button.submit {
            margin-top: 10px;
            background-color: #ddd;
            border: none;
            border-radius: 25px;
            padding: 10px 0;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }

        button.submit:hover {
            background-color: #bbb;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="tab">
        <button id="loginTab" class="active">LOGIN</button>
        <button id="registerTab">REGISTER</button>
    </div>

    <form id="loginForm">
        <div class="input-group">
            <label>Username</label>
            <input type="text" placeholder="Enter username">
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" placeholder="Enter password">
        </div>
        <button class="submit">Login</button>
    </form>

    <form id="registerForm" class="hidden">
        <div class="input-group">
            <label>Username</label>
            <input type="text" placeholder="Enter username">
        </div>
        <div class="input-group">
            <label>Email</label>
            <input type="email" placeholder="Enter email">
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" placeholder="Enter password">
        </div>
        <div class="input-group">
            <label>Re-enter Password</label>
            <input type="password" placeholder="Re-enter password">
        </div>
        <button class="submit">Register</button>
    </form>
</div>

<script>
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    });

    registerTab.addEventListener('click', () => {
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    });
</script>
</body>
</html>
