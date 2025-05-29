<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeSphere - Главная</title>
    <!-- Подключение Semantic UI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Контент главной страницы -->
<div class="ui container" style="margin-top: 50px;">

    <!-- Заголовок -->
    <div class="ui centered header">
        <h1>Добро пожаловать в <span style="color: #2185d0;">CodeSphere</span>!</h1>
        <p>Современная система управления обучением (LMS), которая поможет вам учиться и преподавать с удовольствием.</p>
    </div>

    <!-- Описание функционала -->
    <div class="ui three column grid">
        <div class="column">
            <div class="ui segment">
                <h3 class="ui header">Удобные курсы</h3>
                <p>Создавайте и проходите курсы на любые темы. Наши курсы легко организовать, и они удобны для всех участников.</p>
            </div>
        </div>
        <div class="column">
            <div class="ui segment">
                <h3 class="ui header">Тесты и оценки</h3>
                <p>Включайте тесты для проверки знаний, создавайте вопросы с вариантами ответов, чтобы сделать обучение еще более интерактивным.</p>
            </div>
        </div>
        <div class="column">
            <div class="ui segment">
                <h3 class="ui header">Профили пользователей</h3>
                <p>Каждый пользователь может иметь свой профиль, следить за прогрессом и получать рекомендации по курсам.</p>
            </div>
        </div>
    </div>

    <!-- Преимущества -->
    <div class="ui segment" style="margin-top: 40px;">
        <h2 class="ui header">Почему выбирают <span style="color: #2185d0;">CodeSphere</span>?</h2>
        <div class="ui list">
            <div class="item">
                <i class="check circle icon"></i>
                <div class="content">
                    <strong>Гибкость обучения</strong> - обучайтесь в любое время и в любом месте, используя удобный интерфейс.
                </div>
            </div>
            <div class="item">
                <i class="check circle icon"></i>
                <div class="content">
                    <strong>Интерактивные курсы</strong> - добавляйте видео, тесты, и другие мультимедийные элементы для более эффективного обучения.
                </div>
            </div>
            <div class="item">
                <i class="check circle icon"></i>
                <div class="content">
                    <strong>Простота управления</strong> - создавайте курсы, добавляйте материалы и тесты без лишних усилий.
                </div>
            </div>
            <div class="item">
                <i class="check circle icon"></i>
                <div class="content">
                    <strong>Полная безопасность</strong> - ваши данные и материалы защищены с использованием новейших технологий.
                </div>
            </div>
        </div>
    </div>

    <!-- Призыв к действию -->
    <div class="ui centered segment" style="margin-top: 40px; padding: 30px; background-color: #f9fafb;">
        <h3 class="ui header">Готовы начать обучение?</h3>
        <p>Зарегистрируйтесь или войдите в систему, чтобы начать использовать <span style="color: #2185d0;">CodeSphere</span>.</p>
        <a href="register.php" class="ui primary button">Зарегистрироваться</a>
        <a href="login.php" class="ui secondary button">Войти</a>
    </div>

</div>

</body>
</html>
