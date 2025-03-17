<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $users = get_users();
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = $user;
            header('Location: courses.php');
            exit;
        }
    }
    $error = 'Неверные учетные данные';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <!-- Подключение Semantic UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<div class="ui menu">
    <div class="ui container">
        <a href="index.php" class="header item">CodeSphere</a>
        <div class="right menu">
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Если пользователь авторизован -->
                <a href="courses.php" class="item">Курсы</a>
                <a href="profile.php" class="item">Профиль</a>
                <a href="logout.php" class="item">Выход</a>
            <?php else: ?>
                <!-- Если пользователь не авторизован -->
                <a href="login.php" class="item">Войти</a>
                <a href="register.php" class="item">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="ui container" style="margin-top: 50px;">
    <h1 class="ui header">Вход в систему</h1>

    <?php if ($error): ?>
        <div class="ui red message">
            <div class="header"><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <form method="post" class="ui form">
        <div class="field">
            <label for="username">Логин</label>
            <input type="text" name="username" id="username" placeholder="Введите логин" required>
        </div>
        
        <div class="field">
            <label for="password">Пароль</label>
            <input type="password" name="password" id="password" placeholder="Введите пароль" required>
        </div>
        
        <button type="submit" class="ui primary button">Войти</button>
    </form>

    <p style="margin-top: 20px;">
        Нет аккаунта? <a href="register.php" class="ui link">Регистрация</a>
    </p>
</div>

</body>
</html>
