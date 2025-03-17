<?php
require_once 'config.php';
redirect_unauthenticated();

$courses = get_courses();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Курсы</title>
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
    <h1 class="ui header">Доступные курсы</h1>

    <?php if (is_admin()): ?>
        <a href="add_course.php" class="ui primary button">Создать новый курс</a>
    <?php endif; ?>
    
    <div class="ui cards" style="margin-top: 20px;">
        <?php foreach ($courses as $course): ?>
            <div class="card">
                <div class="content">
                    <h3 class="header">
                        <a href="course.php?id=<?= $course['id'] ?>" class="ui link"><?= htmlspecialchars($course['title']) ?></a>
                    </h3>
                    <div class="description">
                        <p><?= htmlspecialchars($course['description']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p style="margin-top: 20px;">
        <a href="logout.php" class="ui red button">Выйти</a>
    </p>
</div>

</body>
</html>
