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
    
    // Регистрируем просмотр курса
    if (isset($_SESSION['user'])) {
        $stmt = $pdo->prepare("
            INSERT INTO course_views (id_course, id_user)
            VALUES (?, ?)
        ");
        $stmt->execute([$course_id, $user_id]);
        
        // Обновляем статистику курса
        $stmt = $pdo->prepare("
            INSERT INTO course_statistics (id_course, views_count, enrollment_count, completion_count)
            VALUES (?, 1, 0, 0)
            ON CONFLICT (id_course) DO UPDATE SET
            views_count = course_statistics.views_count + 1,
            last_updated = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$course_id]);
    }
    
    if (!$course) {
        throw new Exception('Курс не найден');
    }
    
    // Проверяем права доступа
    $is_creator = is_course_creator($pdo, $course_id, $user_id);
    $is_enrolled = is_enrolled_student($pdo, $course_id, $user_id);
    
    // Проверяем, является ли преподаватель создателем курса
    $is_actual_creator = false;
    if (is_teacher()) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM create_passes 
            WHERE id_course = ? AND id_user = ? AND is_creator = true
        ");
        $stmt->execute([$course_id, $user_id]);
        $is_actual_creator = $stmt->fetchColumn() > 0;
    }
    
    // Если администратор просматривает курс с параметром admin_view=1, 
    // даем ему права создателя курса
    if (is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1) {
        $is_creator = true;
        
        // Получаем информацию о создателе курса для отображения
        $stmt = $pdo->prepare("
            SELECT u.fn_user, u.login_user
            FROM create_passes cp
            JOIN users u ON cp.id_user = u.id_user
            WHERE cp.id_course = ? AND cp.is_creator = true
        ");
        $stmt->execute([$course_id]);
        $creator = $stmt->fetch();
        
        // Добавляем информацию о режиме просмотра администратора
        $admin_view = true;
    } else {
        $admin_view = false;
    }
    
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
                           JOIN test_attempts ta ON t.id_test = ta.id_test
                           WHERE s2.id_step = s.id_step
                           AND ta.id_user = ?
                           AND ta.status = 'completed'
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
    
    // Проверка ограничений курса
    $user = $_SESSION['user'];
    if (!empty($course['required_uni']) && $course['required_uni'] !== $user['uni_user']) {
        throw new Exception('Этот курс доступен только для студентов вуза: ' . htmlspecialchars($course['required_uni']));
    }
    if (!empty($course['required_spec']) && $course['required_spec'] !== $user['spec_user']) {
        throw new Exception('Этот курс доступен только для специальности: ' . htmlspecialchars($course['required_spec']));
    }
    if (!empty($course['requred_year']) && (string)$course['requred_year'] !== (string)$user['year_user']) {
        throw new Exception('Этот курс доступен только для студентов ' . htmlspecialchars($course['requred_year']) . ' года обучения');
    }
    
    // Обработка POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            // Запись на курс
            if ($_POST['action'] === 'enroll' && is_student()) {
                try {
                    // Проверяем, не записан ли студент уже на этот курс
                    $check_stmt = $pdo->prepare("
                        SELECT COUNT(*) FROM create_passes 
                        WHERE id_course = ? AND id_user = ?
                    ");
                    $check_stmt->execute([$course_id, $user_id]);
                    $already_enrolled = $check_stmt->fetchColumn() > 0;
                    
                    if ($already_enrolled) {
                        $success = 'Вы уже записаны на этот курс';
                        $course['is_enrolled'] = true;
                        $is_enrolled = true;
                    } else {
                        // Записываем студента на курс
                        $stmt = $pdo->prepare("
                            INSERT INTO create_passes (id_course, id_user, date_complete)
                            VALUES (?, ?, NULL)
                        ");
                        $stmt->execute([$course_id, $user_id]);
                        
                        // Обновляем интересы пользователя на основе курсов
                        try {
                            update_user_interests($user_id);
                        } catch (Exception $e) {
                            // Игнорируем ошибку, если таблицы для интересов не существуют
                            error_log('Ошибка при обновлении интересов пользователя: ' . $e->getMessage());
                        }
                        
                        // Логируем активность студента - функция не существует, поэтому комментируем
                        /*
                        $stmt = $pdo->prepare("
                            SELECT log_student_activity(?, 'course_enrollment', ?, NULL, NULL, NULL, NULL, NULL)
                        ");
                        $stmt->execute([$user_id, $course_id]);
                        */
                        
                        $success = 'Вы успешно записались на курс';
                        $course['is_enrolled'] = true;
                        $is_enrolled = true;
                    }
                } catch (PDOException $e) {
                    $error = 'Ошибка при записи на курс: ' . $e->getMessage();
                }
            }
            // Отправка курса на модерацию
            elseif ($_POST['action'] === 'send_to_moderation' && $is_creator) {
                try {
                    $stmt = $pdo->prepare("UPDATE course SET status_course='pending', moderation_comment=NULL WHERE id_course=?");
                    $stmt->execute([$course_id]);
                    $success = 'Курс успешно отправлен на модерацию';
                    $course['status_course'] = 'pending';
                } catch (PDOException $e) {
                    $error = 'Ошибка при отправке курса на модерацию: ' . $e->getMessage();
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
        <?php if (isset($admin_view) && $admin_view): ?>
            <div class="ui info message">
                <i class="eye icon"></i>
                <strong>Режим администратора:</strong> Вы просматриваете курс как преподаватель <?= htmlspecialchars($creator['fn_user']) ?> (<?= htmlspecialchars($creator['login_user']) ?>)
                <a href="course.php?id=<?= $course_id ?>" class="ui small right floated button">Выйти из режима просмотра</a>
            </div>
        <?php endif; ?>
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
                    
                    <?php if ($is_creator && ($is_actual_creator || isset($admin_view))): ?>
                        <!-- Панель управления для создателя курса -->
                        <div class="ui segment">
                            <h3 class="ui header">Панель управления курсом</h3>
                            
                            <?php 
                            $status_labels = [
                                'draft' => ['Черновик', 'grey'],
                                'pending' => ['На модерации', 'yellow'],
                                'correction' => ['Требует доработки', 'orange'],
                                'approved' => ['Одобрен', 'green'],
                                'rejected' => ['Отклонен', 'red']
                            ];
                            $status_info = $status_labels[$course['status_course']] ?? ['Неизвестно', 'black'];
                            ?>
                            
                            <div class="ui <?= $status_info[1] ?> label" style="margin-bottom: 15px;">
                                <i class="info circle icon"></i>
                                Статус: <?= $status_info[0] ?>
                            </div>
                            
                            <?php if ($course['status_course'] === 'correction' && !empty($course['moderation_comment'])): ?>
                                <div class="ui warning message">
                                    <div class="header">Комментарий модератора:</div>
                                    <p><?= nl2br(htmlspecialchars($course['moderation_comment'])) ?></p>
                                </div>
                            <?php elseif ($course['status_course'] === 'rejected' && !empty($course['moderation_comment'])): ?>
                                <div class="ui negative message">
                                    <div class="header">Причина отклонения:</div>
                                    <p><?= nl2br(htmlspecialchars($course['moderation_comment'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
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
                            
                            <?php if ($course['status_course'] === 'draft' || $course['status_course'] === 'correction'): ?>
                                <form method="post" style="margin-top: 10px;">
                                    <input type="hidden" name="action" value="send_to_moderation">
                                    <button type="submit" class="ui orange button">
                                        <i class="paper plane icon"></i> Отправить на модерацию
                                    </button>
                                </form>
                            <?php endif; ?>
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
                                    <?php 
                                    $can_access_lesson = true; // Флаг доступа к уроку
                                    foreach ($lessons as $index => $lesson): 
                                    ?>
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
                                            
                                            <?php if (!$can_access_lesson && $index > 0 && $lesson['completed_steps'] == 0): ?>
                                                <div class="ui warning message">
                                                    <i class="lock icon"></i>
                                                    Сначала завершите предыдущие уроки
                                                </div>
                                            <?php else: ?>
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
                                            <?php endif; ?>
                                        </div>
                                        <?php 
                                        // Если урок не полностью завершен, блокируем доступ к следующим урокам
                                        if ($lesson['total_steps'] > 0 && $lesson['completed_steps'] < $lesson['total_steps']) {
                                            $can_access_lesson = false;
                                        }
                                        ?>
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
