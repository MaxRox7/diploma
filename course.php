<?php
require_once 'config.php';
redirect_unauthenticated();

if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = (int)$_GET['id'];
$user_id = $_SESSION['user']['id_user'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Получаем информацию о курсе
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(DISTINCT cp.id_user) as students_count,
               COUNT(DISTINCT l.id_lesson) as lessons_count,
               AVG(CAST(f.rate_feedback AS FLOAT)) as average_rating,
               COUNT(DISTINCT f.id_feedback) as feedback_count
        FROM course c
        LEFT JOIN create_passes cp ON c.id_course = cp.id_course
        LEFT JOIN lessons l ON c.id_course = l.id_course
        LEFT JOIN feedback f ON c.id_course = f.id_course
        WHERE c.id_course = ?
        GROUP BY c.id_course
    ");
    
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        throw new Exception('Курс не найден');
    }
    
    // Проверяем права доступа
    $is_creator = is_course_creator($pdo, $course_id, $user_id);
    $is_enrolled = is_enrolled_student($pdo, $course_id, $user_id);
    
    // Если пользователь не создатель и не записан на курс - показываем только общую информацию
    // и кнопку записи на курс
    $course['is_creator'] = $is_creator;
    $course['is_enrolled'] = $is_enrolled;
    
    // Инициализируем переменные по умолчанию
    $lessons = [];
    $total_steps = 0;
    $completed_steps = 0;
    $progress_percentage = 0;
    $feedbacks = [];
    $can_leave_feedback = false;
    
    // Получаем уроки курса и прогресс пользователя (только для студентов)
    if ($is_enrolled) {
        $stmt = $pdo->prepare("
            SELECT l.*,
                   COUNT(DISTINCT s.id_step) as total_steps,
                   COUNT(DISTINCT CASE WHEN s.id_step IS NOT NULL AND (
                       EXISTS(
                           SELECT 1 FROM Material m 
                           JOIN Steps s2 ON m.id_step = s2.id_step 
                           JOIN user_material_progress ump ON s2.id_step = ump.id_step
                           WHERE s2.id_step = s.id_step
                           AND ump.id_user = ?
                       )
                       OR
                       EXISTS(
                           SELECT 1 FROM Tests t 
                           JOIN Steps s2 ON t.id_step = s2.id_step 
                           JOIN Results r ON t.id_test = r.id_test
                           JOIN Answers a ON r.id_answer = a.id_answer
                           WHERE s2.id_step = s.id_step
                           AND a.id_user = ?
                           AND CAST(r.score_result AS INTEGER) >= 60
                       )
                   ) THEN s.id_step END) as completed_steps
            FROM lessons l
            LEFT JOIN Steps s ON l.id_lesson = s.id_lesson
            WHERE l.id_course = ?
            GROUP BY l.id_lesson
            ORDER BY l.id_lesson
        ");
        $stmt->execute([$user_id, $user_id, $course_id]);
        $lessons = $stmt->fetchAll();
        
        // Подсчитываем общий прогресс
        foreach ($lessons as $lesson) {
            $total_steps += $lesson['total_steps'];
            $completed_steps += $lesson['completed_steps'];
        }
        $progress_percentage = $total_steps > 0 ? round(($completed_steps / $total_steps) * 100) : 0;

        // Обновляем дату завершения курса, если достигнут 100% прогресс
        if ($progress_percentage == 100) {
            $stmt = $pdo->prepare("
                UPDATE create_passes 
                SET date_complete = CURRENT_TIMESTAMP 
                WHERE id_course = ? AND id_user = ? AND date_complete IS NULL
            ");
            $stmt->execute([$course_id, $user_id]);
        }
    }
    
    // Получаем отзывы
    $stmt = $pdo->prepare("
        SELECT f.*, u.fn_user
        FROM feedback f
        JOIN users u ON f.id_user = u.id_user
        WHERE f.id_course = ?
        ORDER BY f.date_feedback DESC
    ");
    $stmt->execute([$course_id]);
    $feedbacks = $stmt->fetchAll();
    
    // Проверяем, может ли пользователь оставить отзыв
    if ($is_enrolled && $progress_percentage == 100) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM feedback 
            WHERE id_course = ? AND id_user = ?
        ");
        $stmt->execute([$course_id, $user_id]);
        $has_feedback = $stmt->fetchColumn() > 0;
        $can_leave_feedback = !$has_feedback;
    }
    
    // Обработка POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            // Запись на курс
            if ($_POST['action'] === 'enroll' && is_student()) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO create_passes (id_course, id_user, date_complete)
                        VALUES (?, ?, NULL)
                    ");
                    $stmt->execute([$course_id, $user_id]);
                    $success = 'Вы успешно записались на курс';
                    $course['is_enrolled'] = true;
                    $is_enrolled = true;
                } catch (PDOException $e) {
                    $error = 'Ошибка при записи на курс: ' . $e->getMessage();
                }
            }
            // Добавление отзыва
            elseif ($_POST['action'] === 'add_feedback' && $can_leave_feedback) {
                $text_feedback = trim($_POST['text_feedback']);
                $rate_feedback = (int)$_POST['rate_feedback'];
                
                if (empty($text_feedback)) {
                    $error = 'Введите текст отзыва';
                } elseif ($rate_feedback < 1 || $rate_feedback > 5) {
                    $error = 'Оценка должна быть от 1 до 5';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO feedback (id_course, id_user, text_feedback, date_feedback, rate_feedback)
                            VALUES (?, ?, ?, CURRENT_DATE, ?)
                        ");
                        $stmt->execute([$course_id, $user_id, $text_feedback, $rate_feedback]);
                        $success = 'Отзыв успешно добавлен';
                        
                        // Перезагружаем отзывы
                        $stmt = $pdo->prepare("
                            SELECT f.*, u.fn_user
                            FROM feedback f
                            JOIN users u ON f.id_user = u.id_user
                            WHERE f.id_course = ?
                            ORDER BY f.date_feedback DESC
                        ");
                        $stmt->execute([$course_id]);
                        $feedbacks = $stmt->fetchAll();
                        $can_leave_feedback = false;
                    } catch (PDOException $e) {
                        $error = 'Ошибка при добавлении отзыва: ' . $e->getMessage();
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['name_course']) ?> - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <?php if ($error): ?>
        <div class="ui error message">
            <div class="header">Ошибка</div>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php elseif ($course): ?>
        <div class="ui grid">
            <!-- Основная информация о курсе -->
            <div class="sixteen wide column">
                <div class="ui segment">
                    <h1 class="ui header">
                        <?= htmlspecialchars($course['name_course']) ?>
                        <?php if ($course['average_rating']): ?>
                            <div class="sub header">
                                <div class="ui star rating" data-rating="<?= round($course['average_rating']) ?>" data-max-rating="5"></div>
                                (<?= number_format($course['average_rating'], 1) ?> / 5.0 - <?= $course['feedback_count'] ?> отзывов)
                            </div>
                        <?php endif; ?>
                    </h1>
                    
                    <div class="ui divider"></div>
                    
                    <p><?= nl2br(htmlspecialchars($course['desc_course'])) ?></p>
                    
                    <div class="ui statistics">
                        <div class="statistic">
                            <div class="value">
                                <i class="user icon"></i> <?= $course['students_count'] ?>
                            </div>
                            <div class="label">Студентов</div>
                        </div>
                        <div class="statistic">
                            <div class="value">
                                <i class="book icon"></i> <?= $course['lessons_count'] ?>
                            </div>
                            <div class="label">Уроков</div>
                        </div>
                        <div class="statistic">
                            <div class="value">
                                <i class="clock icon"></i> <?= $course['hourse_course'] ?>
                            </div>
                            <div class="label">Часов</div>
                        </div>
                    </div>
                    
                    <?php if ($is_creator): ?>
                        <!-- Панель управления для создателя курса -->
                        <div class="ui segment">
                            <h3 class="ui header">Панель управления курсом</h3>
                            <div class="ui buttons">
                                <a href="edit_course.php?id=<?= $course_id ?>" class="ui primary button">
                                    <i class="edit icon"></i>
                                    Редактировать курс
                                </a>
                                <a href="edit_lessons.php?course_id=<?= $course_id ?>" class="ui positive button">
                                    <i class="list icon"></i>
                                    Управление уроками
                                </a>
                                <button class="ui negative button" onclick="if(confirm('Вы уверены, что хотите удалить курс?')) window.location.href='delete_course.php?id=<?= $course_id ?>'">
                                    <i class="trash icon"></i>
                                    Удалить курс
                                </button>
                            </div>
                        </div>
                    <?php elseif (is_student()): ?>
                        <?php if (!$is_enrolled): ?>
                            <!-- Кнопка записи на курс для студента -->
                            <form method="post" style="margin-top: 20px;">
                                <input type="hidden" name="action" value="enroll">
                                <button type="submit" class="ui primary button">Записаться на курс</button>
                            </form>
                        <?php else: ?>
                            <!-- Список уроков для записанного студента -->
                            <div class="ui segments">
                                <div class="ui segment">
                                    <h3 class="ui header">
                                        <i class="book icon"></i>
                                        Содержание курса
                                    </h3>
                                    <?php if ($progress_percentage > 0): ?>
                                        <div class="ui indicating progress" data-percent="<?= $progress_percentage ?>" style="margin-top: 20px;">
                                            <div class="bar">
                                                <div class="progress"><?= $progress_percentage ?>%</div>
                                            </div>
                                            <div class="label">Общий прогресс курса</div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($lessons)): ?>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <div class="ui segment">
                                            <h4 class="ui header">
                                                <?= htmlspecialchars($lesson['name_lesson']) ?>
                                                <?php if ($lesson['total_steps'] > 0): ?>
                                                    <div class="sub header">
                                                        Прогресс: <?= $lesson['completed_steps'] ?> / <?= $lesson['total_steps'] ?> шагов
                                                        (<?= $lesson['total_steps'] > 0 ? round(($lesson['completed_steps'] / $lesson['total_steps']) * 100) : 0 ?>%)
                                                    </div>
                                                <?php endif; ?>
                                            </h4>
                                            
                                            <?php if ($lesson['total_steps'] > 0): ?>
                                                <div class="ui tiny indicating progress" data-percent="<?= $lesson['total_steps'] > 0 ? round(($lesson['completed_steps'] / $lesson['total_steps']) * 100) : 0 ?>">
                                                    <div class="bar"></div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <a href="lesson.php?id=<?= $lesson['id_lesson'] ?>" class="ui primary button">
                                                <?php if ($lesson['completed_steps'] == $lesson['total_steps'] && $lesson['total_steps'] > 0): ?>
                                                    <i class="check circle icon"></i>
                                                    Повторить урок
                                                <?php elseif ($lesson['completed_steps'] > 0): ?>
                                                    <i class="sync icon"></i>
                                                    Продолжить урок
                                                <?php else: ?>
                                                    <i class="play icon"></i>
                                                    Начать урок
                                                <?php endif; ?>
                                            </a>
                                        </div>
        <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="ui segment">
                                        <p>В курсе пока нет уроков</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
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

                <!-- Отзывы -->
                <div class="ui segment">
                    <h3 class="ui header">Отзывы о курсе</h3>
                    
                    <?php if ($can_leave_feedback): ?>
                        <form class="ui form" method="post" id="feedbackForm">
                            <input type="hidden" name="action" value="add_feedback">
                            <div class="field">
                                <label>Ваша оценка</label>
                                <div class="ui massive star rating" data-max-rating="5"></div>
                                <input type="hidden" name="rate_feedback" id="ratingInput" required>
                            </div>
                            <div class="field">
                                <label>Ваш отзыв</label>
                                <textarea name="text_feedback" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="ui primary button">Отправить отзыв</button>
                        </form>
                        <div class="ui divider"></div>
                    <?php endif; ?>
                    
                    <?php if (empty($feedbacks)): ?>
                        <div class="ui message">
                            <p>Пока нет отзывов</p>
                        </div>
                    <?php else: ?>
                        <div class="ui comments">
                            <?php foreach ($feedbacks as $feedback): ?>
                                <div class="comment">
                                    <div class="content">
                                        <a class="author"><?= htmlspecialchars($feedback['fn_user']) ?></a>
                                        <div class="metadata">
                                            <div class="date"><?= $feedback['date_feedback'] ?></div>
                                            <div class="rating">
                                                <div class="ui star rating" data-rating="<?= $feedback['rate_feedback'] ?>" data-max-rating="5"></div>
                                            </div>
                                        </div>
                                        <div class="text">
                                            <?= nl2br(htmlspecialchars($feedback['text_feedback'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="ui error message">
            <div class="header">Курс не найден</div>
            <p>Запрошенный курс не существует или у вас нет к нему доступа.</p>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Инициализация всех рейтингов только для чтения
    $('.ui.rating').not('.massive').rating('disable');
    
    <?php if ($can_leave_feedback): ?>
        // Инициализация интерактивного рейтинга для формы отзыва
        var $ratingInput = $('#ratingInput');
        var $feedbackForm = $('#feedbackForm');
        
        $('#feedbackForm .ui.rating').rating({
            initialRating: 0,
            maxRating: 5,
            onRate: function(value) {
                $ratingInput.val(value);
            }
        });
        
        // Валидация формы
        $feedbackForm.form({
            fields: {
                rate_feedback: {
                    identifier: 'rate_feedback',
                    rules: [{
                        type: 'empty',
                        prompt: 'Пожалуйста, поставьте оценку'
                    }]
                },
                text_feedback: {
                    identifier: 'text_feedback',
                    rules: [{
                        type: 'empty',
                        prompt: 'Пожалуйста, напишите отзыв'
                    }]
                }
            },
            onSuccess: function() {
                if (!$ratingInput.val()) {
                    alert('Пожалуйста, поставьте оценку');
                    return false;
                }
                return true;
            }
        });
    <?php endif; ?>
    
    $('.ui.progress').progress();
});
</script>

</body>
</html>
