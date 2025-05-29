<?php
require_once 'config.php';

// Проверка на авторизацию
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];  // Получаем данные о пользователе из сессии
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <!-- Подключение Semantic UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>

<!-- Шапка -->
<div class="ui menu">
    <div class="ui container">
        <a href="index.php" class="header item">CodeSphere</a>
        <div class="right menu">
            <a href="courses.php" class="item">Курсы</a>
            <a href="profile.php" class="item">Профиль</a>
            <a href="logout.php" class="item">Выход</a>
        </div>
    </div>
</div>

<!-- Контент профиля -->
<div class="ui container" style="margin-top: 50px;">
    <h2 class="ui header">Профиль пользователя</h2>
    <div class="ui list">
        <div class="item">
            <div class="header">Логин:</div>
            <?= htmlspecialchars($_SESSION['user']['login_user']) ?>
        </div>
        <div class="item">
            <div class="header">Имя:</div>
            <?= htmlspecialchars($_SESSION['user']['fn_user']) ?>
        </div>
        <div class="item">
            <div class="header">Роль:</div>
            <?= htmlspecialchars($_SESSION['user']['is_admin'] ? 'Администратор' : 'Студент') ?>
        </div>
    </div>

    <div class="ui segment">
        <h3>Действия:</h3>
        <a href="edit_profile.php" class="ui button">Редактировать профиль</a>
    </div>
</div>

</body>
</html>
