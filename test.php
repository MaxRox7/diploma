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

// Обработка теста
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    foreach ($course['test'] as $index => $question) {
        if ($_POST['answers'][$index] === $question['correct']) {
            $score++;
        }
    }
    $result = "Результат: $score из " . count($course['test']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - Тест</title>
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
    <h1 class="ui header"><?= htmlspecialchars($course['title']) ?> - Тест</h1>

    <?php if (isset($result)): ?>
        <div class="ui message">
            <div class="header"><?= htmlspecialchars($result) ?></div>
        </div>
    <?php else: ?>
        <form method="post" class="ui form">
            <?php foreach ($course['test'] as $index => $question): ?>
                <div class="field">
                    <label><?= htmlspecialchars($question['question']) ?></label>
                    <?php foreach ($question['options'] as $option): ?>
                        <div class="ui radio checkbox">
                            <input type="radio" name="answers[<?= $index ?>]" 
                                value="<?= htmlspecialchars($option) ?>" required>
                            <label><?= htmlspecialchars($option) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="ui primary button">Проверить знания</button>
        </form>
    <?php endif; ?>

    <p><a href="course.php?id=<?= $course['id'] ?>" class="ui link">Назад к курсу</a></p>
</div>

</body>
</html>
