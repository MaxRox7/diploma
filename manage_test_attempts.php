<?php
require_once 'config.php';
redirect_unauthenticated();

// Только преподаватели могут управлять попытками
if (!is_teacher()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user']['id_user'];
$pdo = get_db_connection();
$error = '';
$success = '';
$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Если не указан тест, перенаправляем на страницу курсов
if (!$test_id) {
    header('Location: my_courses.php');
    exit;
}

// Проверяем, принадлежит ли тест преподавателю
$stmt = $pdo->prepare("
    SELECT t.*, s.id_step, s.id_lesson, l.id_course, c.name_course
    FROM Tests t
    JOIN Steps s ON t.id_step = s.id_step
    JOIN lessons l ON s.id_lesson = l.id_lesson
    JOIN course c ON l.id_course = c.id_course
    JOIN create_passes cp ON c.id_course = cp.id_course
    WHERE t.id_test = ? AND cp.id_user = ? AND cp.is_creator = true
");
$stmt->execute([$test_id, $user_id]);
$test_info = $stmt->fetch();

if (!$test_info) {
    header('Location: my_courses.php');
    exit;
}

// Обработка добавления дополнительных попыток
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_attempts') {
    $student_id = (int)$_POST['student_id'];
    $attempts = (int)$_POST['attempts'];
    
    if ($attempts <= 0) {
        $error = 'Количество дополнительных попыток должно быть положительным числом';
    } else {
        try {
            // Проверяем, есть ли уже запись для этого студента и теста
            $stmt = $pdo->prepare("
                SELECT additional_attempts FROM student_test_settings
                WHERE id_user = ? AND id_test = ?
            ");
            $stmt->execute([$student_id, $test_id]);
            $current_attempts = $stmt->fetchColumn();
            
            if ($current_attempts !== false) {
                // Обновляем существующую запись
                $stmt = $pdo->prepare("
                    UPDATE student_test_settings
                    SET additional_attempts = additional_attempts + ?
                    WHERE id_user = ? AND id_test = ?
                ");
                $stmt->execute([$attempts, $student_id, $test_id]);
            } else {
                // Создаем новую запись
                $stmt = $pdo->prepare("
                    INSERT INTO student_test_settings (id_user, id_test, additional_attempts)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$student_id, $test_id, $attempts]);
            }
            
            $success = 'Дополнительные попытки успешно добавлены';
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении попыток: ' . $e->getMessage();
        }
    }
}

// Получаем список студентов, записанных на курс
$stmt = $pdo->prepare("
    SELECT u.id_user, u.fn_user, u.login_user,
           COALESCE(sts.additional_attempts, 0) as additional_attempts,
           COUNT(ta.id_attempt) as attempts_count,
           MAX(CASE WHEN ta.status = 'completed' AND ta.score >= (t.passing_percentage * ta.max_score / 100) THEN 1 ELSE 0 END) as has_passed
    FROM users u
    JOIN create_passes cp ON u.id_user = cp.id_user
    JOIN course c ON cp.id_course = c.id_course
    JOIN lessons l ON c.id_course = l.id_course
    JOIN Steps s ON l.id_lesson = s.id_lesson
    JOIN Tests t ON s.id_step = t.id_step
    LEFT JOIN student_test_settings sts ON u.id_user = sts.id_user AND t.id_test = sts.id_test
    LEFT JOIN test_attempts ta ON u.id_user = ta.id_user AND t.id_test = ta.id_test
    WHERE t.id_test = ? AND cp.is_creator = false
    GROUP BY u.id_user, u.fn_user, u.login_user, sts.additional_attempts
    ORDER BY u.fn_user
");
$stmt->execute([$test_id]);
$students = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление попытками теста - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <div class="ui segment">
                <h2 class="ui header">
                    <i class="settings icon"></i>
                    <div class="content">
                        Управление попытками теста
                        <div class="sub header">Предоставление дополнительных попыток для студентов</div>
                    </div>
                </h2>
                
                <div class="ui breadcrumb">
                    <a href="my_courses.php" class="section">Мои курсы</a>
                    <i class="right chevron icon divider"></i>
                    <a href="course.php?id=<?= $test_info['id_course'] ?>" class="section"><?= htmlspecialchars($test_info['name_course']) ?></a>
                    <i class="right chevron icon divider"></i>
                    <a href="lesson.php?id=<?= $test_info['id_lesson'] ?>" class="section">Урок</a>
                    <i class="right chevron icon divider"></i>
                    <a href="edit_test.php?test_id=<?= $test_id ?>" class="section">Тест</a>
                    <i class="right chevron icon divider"></i>
                    <div class="active section">Управление попытками</div>
                </div>
                
                <div class="ui divider"></div>
                
                <?php if ($error): ?>
                    <div class="ui error message">
                        <div class="header">Ошибка</div>
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="ui success message">
                        <div class="header">Успех</div>
                        <p><?= htmlspecialchars($success) ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="ui segment">
                    <h3>Настройки теста</h3>
                    <div class="ui list">
                        <div class="item">
                            <i class="right triangle icon"></i>
                            <div class="content">
                                <div class="header">Максимальное количество попыток по умолчанию</div>
                                <div class="description"><?= $test_info['max_attempts'] ?></div>
                            </div>
                        </div>
                        <div class="item">
                            <i class="right triangle icon"></i>
                            <div class="content">
                                <div class="header">Интервал между попытками</div>
                                <div class="description"><?= $test_info['time_between_attempts'] ?> мин.</div>
                            </div>
                        </div>
                        <div class="item">
                            <i class="right triangle icon"></i>
                            <div class="content">
                                <div class="header">Проходной балл</div>
                                <div class="description"><?= $test_info['passing_percentage'] ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($students)): ?>
                    <div class="ui placeholder segment">
                        <div class="ui icon header">
                            <i class="users icon"></i>
                            На курс пока не записаны студенты
                        </div>
                    </div>
                <?php else: ?>
                    <h3>Студенты, записанные на курс</h3>
                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>Студент</th>
                                <th>Email</th>
                                <th>Статус</th>
                                <th>Попытки</th>
                                <th>Доп. попытки</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['fn_user']) ?></td>
                                    <td><?= htmlspecialchars($student['login_user']) ?></td>
                                    <td>
                                        <?php if ($student['has_passed']): ?>
                                            <div class="ui green label">Пройден</div>
                                        <?php elseif ($student['attempts_count'] > 0): ?>
                                            <div class="ui red label">Не пройден</div>
                                        <?php else: ?>
                                            <div class="ui grey label">Не приступал</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $student['attempts_count'] ?> / <?= $test_info['max_attempts'] + $student['additional_attempts'] ?></td>
                                    <td><?= $student['additional_attempts'] ?></td>
                                    <td>
                                        <form method="post" class="ui form">
                                            <input type="hidden" name="action" value="add_attempts">
                                            <input type="hidden" name="student_id" value="<?= $student['id_user'] ?>">
                                            <div class="fields">
                                                <div class="eight wide field">
                                                    <input type="number" name="attempts" min="1" value="1" required>
                                                </div>
                                                <div class="eight wide field">
                                                    <button type="submit" class="ui blue button">
                                                        <i class="plus icon"></i> Добавить попытки
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <div class="ui divider"></div>
                
                <a href="edit_test.php?test_id=<?= $test_id ?>" class="ui button">
                    <i class="arrow left icon"></i> Вернуться к тесту
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html> 