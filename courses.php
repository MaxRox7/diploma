<?php
require_once 'config.php';
redirect_unauthenticated();

try {
    $pdo = get_db_connection();
    
    // Получаем все курсы из базы данных
    $stmt = $pdo->query("
        SELECT c.*, 
               COUNT(DISTINCT cp.id_user) as students_count,
               COUNT(DISTINCT l.id_lesson) as lessons_count
        FROM course c
        LEFT JOIN create_passes cp ON c.id_course = cp.id_course
        LEFT JOIN lessons l ON c.id_course = l.id_course
        GROUP BY c.id_course
        ORDER BY c.id_course DESC
    ");
    $courses = $stmt->fetchAll();

} catch (PDOException $e) {
    die('Ошибка при получении курсов: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Курсы - CodeSphere</title>
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
                <a href="courses.php" class="active item">Курсы</a>
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
    <div class="ui grid">
        <div class="sixteen wide column">
            <h1 class="ui header">Доступные курсы</h1>
            
            <?php if (is_admin()): ?>
                <a href="add_course.php" class="ui primary button">
                    <i class="plus icon"></i>
                    Создать новый курс
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($courses)): ?>
        <div class="ui placeholder segment">
            <div class="ui icon header">
                <i class="book icon"></i>
                Курсы пока не добавлены
            </div>
            <?php if (is_admin()): ?>
                <a href="add_course.php" class="ui primary button">Создать первый курс</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="ui three stackable cards" style="margin-top: 20px;">
            <?php foreach ($courses as $course): ?>
                <div class="ui raised card">
                    <div class="content">
                        <div class="header">
                            <a href="course.php?id=<?= htmlspecialchars($course['id_course']) ?>">
                                <?= htmlspecialchars($course['name_course']) ?>
                            </a>
                        </div>
                        <?php if (!empty($course['desc_course'])): ?>
                            <div class="description">
                                <?= htmlspecialchars($course['desc_course']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="extra content">
                        <span class="left floated">
                            <i class="user icon"></i>
                            <?= $course['students_count'] ?> студент(ов)
                        </span>
                        <span class="right floated">
                            <i class="book icon"></i>
                            <?= $course['lessons_count'] ?> урок(ов)
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p style="margin-top: 20px;">
        <a href="logout.php" class="ui red button">Выйти</a>
    </p>
</div>

</body>
</html>
