<?php
require_once('helpers.php')
?>


<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация и Регистрация</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>
    <div class="container">
        <!-- Авторизация -->
        <div class="form-container" id="login-form">
            <h2>Авторизация</h2>
            <form action="auth.php" method="POST">
                <label for="login-email">Email</label>
                <input type="email" id="login-email" name="email" placeholder="Введите ваш email" required>

                <label for="login-password">Пароль</label>
                <input type="password" id="login-password" name="password" placeholder="Введите ваш пароль" required>

                <button type="submit">Войти</button>
                <?php if (hasMessage('error')): ?>
                    <div class="notice-error"><?php echo getMessage('error') ?></div>

                <?php endif; ?>

                <p>Нет аккаунта? <a href="#" onclick="showRegisterForm()">Зарегистрироваться</a></p>
            </form>
        </div>

        <!-- Регистрация -->
        <div class="form-container" id="register-form" style="display: none;">
            <h2>Регистрация</h2>
            <form action="regist.php" method="POST">
                <label for="register-name">Имя</label>
                <input type="text" id="register-name" name="name" placeholder="Введите ваше имя" required>

                <label for="register-email">Email</label>
                <input type="email" id="register-email" name="email" placeholder="Введите ваш email" required>

                <label for="register-password">Пароль</label>
                <input type="password" id="register-password" name="password" placeholder="Придумайте пароль" required>

                <button type="submit">Зарегистрироваться</button>

                <?php if (hasMessage('errorRegist')): ?>
                    <div class="notice-error"><?php echo getMessage('errorRegist'); ?></div>
                    <script>
                        document.getElementById('register-form').style.display = 'block';
                        document.getElementById('login-form').style.display = 'none';
                    </script>
                <?php endif; ?>

                <p>Уже есть аккаунт? <a href="#" onclick="showLoginForm()">Войти</a></p>
            </form>
        </div>
    </div>


    <script>
        function showLoginForm() {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('register-form').style.display = 'none';
        }

        function showRegisterForm() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
        }
    </script>
</body>

</html>