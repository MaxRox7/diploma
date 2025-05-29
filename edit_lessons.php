<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем наличие ID курса
if (!isset($_GET['course_id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = (int)$_GET['course_id'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Проверяем, является ли пользователь создателем курса
    if (!is_course_creator($pdo, $course_id, $_SESSION['user']['id_user'])) {
        header('Location: courses.php');
        exit;
    }
    
    // Получаем информацию о курсе
    $stmt = $pdo->prepare("
        SELECT c.* 
        FROM course c
        WHERE c.id_course = ?
    ");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: courses.php');
        exit;
    }
    
    // Получаем уроки курса
    $stmt = $pdo->prepare("
        SELECT * FROM lessons 
        WHERE id_course = ?
        ORDER BY id_lesson
    ");
    $stmt->execute([$course_id]);
    $lessons = $stmt->fetchAll();
    
    // Обработка добавления нового урока
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add_lesson') {
                $name_lesson = trim($_POST['name_lesson']);
                if (empty($name_lesson)) {
                    $error = 'Введите название урока';
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO lessons (id_course, name_lesson, status_lesson)
                        VALUES (?, ?, 'new')
                    ");
                    $stmt->execute([$course_id, $name_lesson]);
                    $success = 'Урок успешно добавлен';
                    
                    // Перезагружаем список уроков
                    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id_course = ? ORDER BY id_lesson");
                    $stmt->execute([$course_id]);
                    $lessons = $stmt->fetchAll();
                }
            }
            elseif ($_POST['action'] === 'delete_lesson') {
                $lesson_id = (int)$_POST['lesson_id'];
                
                // Проверяем, есть ли у урока шаги
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Steps WHERE id_lesson = ?");
                $stmt->execute([$lesson_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Нельзя удалить урок, содержащий шаги';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM lessons WHERE id_lesson = ? AND id_course = ?");
                    $stmt->execute([$lesson_id, $course_id]);
                    $success = 'Урок успешно удален';
                    
                    // Перезагружаем список уроков
                    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id_course = ? ORDER BY id_lesson");
                    $stmt->execute([$course_id]);
                    $lessons = $stmt->fetchAll();
                }
            }
        }
    }
    
} catch (PDOException $e) {
    $error = 'Ошибка базы данных: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование уроков - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <div class="ui clearing segment">
                <h2 class="ui left floated header">
                    <?= htmlspecialchars($course['name_course']) ?>
                    <div class="sub header">Редактирование уроков</div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="courses.php" class="ui button">Назад к курсам</a>
                    <a href="course.php?id=<?= $course_id ?>" class="ui primary button">Просмотр курса</a>
                </div>
            </div>

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

            <!-- Форма добавления урока -->
            <form class="ui form" method="post" id="addLessonForm">
                <input type="hidden" name="action" value="add_lesson">
                <div class="fields">
                    <div class="twelve wide field">
                        <input type="text" name="name_lesson" placeholder="Название нового урока" required>
                    </div>
                    <div class="four wide field">
                        <button type="submit" class="ui primary fluid button">Добавить урок</button>
                    </div>
                </div>
            </form>

            <!-- Список уроков -->
            <?php if (empty($lessons)): ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="book icon"></i>
                        В курсе пока нет уроков
                    </div>
                </div>
            <?php else: ?>
                <div class="ui relaxed divided list">
                    <?php foreach ($lessons as $lesson): ?>
                        <div class="item">
                            <div class="right floated content">
                                <a href="edit_steps.php?lesson_id=<?= $lesson['id_lesson'] ?>" class="ui primary button">
                                    <i class="tasks icon"></i>
                                    Шаги урока
                                </a>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_lesson">
                                    <input type="hidden" name="lesson_id" value="<?= $lesson['id_lesson'] ?>">
                                    <button type="submit" class="ui red button" onclick="return confirm('Вы уверены, что хотите удалить этот урок?');">
                                        <i class="trash icon"></i>
                                        Удалить
                                    </button>
                                </form>
                            </div>
                            <i class="large book middle aligned icon"></i>
                            <div class="content">
                                <div class="header"><?= htmlspecialchars($lesson['name_lesson']) ?></div>
                                <div class="description">
                                    Статус: <?= htmlspecialchars($lesson['status_lesson']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.form').form({
        fields: {
            name_lesson: 'empty'
        }
    });
});
</script>

</body>
</html> 