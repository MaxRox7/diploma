<?php
require_once 'config.php';
redirect_unauthenticated();
asd
if (!is_admin()) {
    header('Location: courses.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_data = [
        'id' => uniqid(),
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'materials' => explode("\n", $_POST['materials']),
        'test' => []
    ];

    // Обработка вопросов теста
    foreach (explode("\n", $_POST['test']) as $question) {
        $parts = explode(';', trim($question));
        if (count($parts) >= 3) {
            $course_data['test'][] = [
                'question' => $parts[0],
                'options' => array_slice($parts, 1, -1),
                'correct' => end($parts)
            ];
        }
    }

    $courses = get_courses();
    $courses[] = $course_data;
    save_courses($courses);
    header('Location: courses.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить курс</title>
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
    <h1 class="ui header">Создание нового курса</h1>
    
    <form method="post" class="ui form">
        <div class="field">
            <label for="title">Название курса</label>
            <input type="text" name="title" id="title" placeholder="Введите название курса" required>
        </div>

        <div class="field">
            <label for="description">Описание курса</label>
            <textarea name="description" id="description" placeholder="Введите описание курса" required></textarea>
        </div>

        <div class="field">
            <label for="materials">Учебные материалы</label>
            <textarea name="materials" id="materials" placeholder="Введите учебные материалы (каждый пункт с новой строки)"></textarea>
        </div>

        <div class="field">
            <label for="test">Вопросы теста</label>
            <textarea name="test" id="test" placeholder="Вопросы теста (формат: вопрос;вариант1;вариант2;...;правильный_ответ)"></textarea>
        </div>

        <button type="submit" class="ui primary button">Создать курс</button>
    </form>
</div>

</body>
</html>
