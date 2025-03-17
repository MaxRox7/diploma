<?php
require_once 'config.php';
redirect_unauthenticated();

$course_id = $_GET['id'] ?? '';
$courses = get_courses();
$course = null;

foreach ($courses as $c) {
    if ($c['id'] === $course_id) {
        $course = $c;
        break;
    }
}

if (!$course) {
    header('Location: courses.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?></title>
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
                <a href="courses.php" class="item">Курсы</a>
                <a href="profile.php" class="item">Профиль</a>
                <a href="logout.php" class="item">Выход</a>
            <?php else: ?>
                <a href="login.php" class="item">Войти</a>
                <a href="register.php" class="item">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="ui container" style="margin-top: 50px;">
    <h1 class="ui header"><?= htmlspecialchars($course['title']) ?></h1>

    <h2 class="ui dividing header">Материалы курса:</h2>
    <ul class="ui list">
        <?php foreach ($course['materials'] as $material): ?>
            <li class="item"><?= htmlspecialchars($material) ?></li>
        <?php endforeach; ?>
    </ul>

    <!-- Кнопка перехода на тест -->
    <?php if (!empty($course['test'])): ?>
        <a href="test.php?id=<?= $course['id'] ?>" class="ui primary button">Пройти тест</a>
    <?php endif; ?>

    <p><a href="courses.php" class="ui link">Назад к курсам</a></p>
</div>

</body>
</html>
