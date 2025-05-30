<?php
require_once 'config.php';
redirect_unauthenticated();

if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$lesson_id = (int)$_GET['id'];
$user_id = $_SESSION['user']['id_user'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Получаем информацию об уроке и курсе
    $stmt = $pdo->prepare("
        SELECT l.*, c.id_course, c.name_course
        FROM lessons l
        JOIN course c ON l.id_course = c.id_course
        WHERE l.id_lesson = ?
    ");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch();
    
    if (!$lesson) {
        header('Location: courses.php');
        exit;
    }
    
    // Проверяем права доступа
    $is_creator = is_course_creator($pdo, $lesson['id_course'], $user_id);
    $is_enrolled = is_enrolled_student($pdo, $lesson['id_course'], $user_id);
    
    // Если пользователь не создатель и не записан на курс - редирект
    if (!$is_creator && !$is_enrolled) {
        header('Location: courses.php');
        exit;
    }
    
    // Создатели курса не могут проходить уроки
    if ($is_creator) {
        header('Location: edit_lessons.php?course_id=' . $lesson['id_course']);
        exit;
    }
    
    $lesson['is_enrolled'] = $is_enrolled;
    
    // Инициализируем переменные по умолчанию
    $steps = [];
    $total_steps = 0;
    $completed_steps = 0;
    $progress_percentage = 0;
    
    // Получаем шаги урока и статус их выполнения
    $stmt = $pdo->prepare("
        SELECT s.*,
               m.path_matial as file_path,
               t.id_test,
               CASE 
                   WHEN m.id_material IS NOT NULL AND EXISTS(
                       SELECT 1 FROM user_material_progress ump
                       WHERE ump.id_step = s.id_step
                       AND ump.id_user = ?
                   ) THEN true
                   WHEN t.id_test IS NOT NULL AND EXISTS(
                       SELECT 1 
                       FROM Results r 
                       JOIN Answers a ON r.id_answer = a.id_answer
                       WHERE r.id_test = t.id_test 
                       AND a.id_user = ?
                       AND CAST(r.score_result AS INTEGER) >= 60
                   ) THEN true
                   ELSE false
               END as is_completed
        FROM Steps s
        LEFT JOIN Material m ON s.id_step = m.id_step
        LEFT JOIN Tests t ON s.id_step = t.id_step
        WHERE s.id_lesson = ?
        ORDER BY s.id_step
    ");
    $stmt->execute([$user_id, $user_id, $lesson_id]);
    $steps = $stmt->fetchAll();
    
    // Подсчитываем прогресс
    $total_steps = count($steps);
    $completed_steps = array_reduce($steps, function($carry, $step) {
        return $carry + ($step['is_completed'] ? 1 : 0);
    }, 0);
    $progress_percentage = $total_steps > 0 ? round(($completed_steps / $total_steps) * 100) : 0;
    
    // Если это POST запрос для отметки материала как прочитанного
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_material_completed'])) {
        $step_id = (int)$_POST['step_id'];
        
        // Проверяем, что шаг принадлежит этому уроку
        $valid_step = false;
        foreach ($steps as $step) {
            if ($step['id_step'] == $step_id) {
                $valid_step = true;
                break;
            }
        }
        
        if ($valid_step) {
            try {
                // Добавляем запись в таблицу прогресса пользователя
                $stmt = $pdo->prepare("
                    INSERT INTO user_material_progress (id_user, id_step)
                    VALUES (?, ?)
                    ON CONFLICT (id_user, id_step) DO NOTHING
                ");
                $stmt->execute([$user_id, $step_id]);
                
                $success = "Материал отмечен как прочитанный";
                
                // Перезагружаем информацию о шагах
                $stmt = $pdo->prepare("
                    SELECT s.*,
                           m.path_matial as file_path,
                           t.id_test,
                           CASE 
                               WHEN m.id_material IS NOT NULL AND EXISTS(
                                   SELECT 1 FROM user_material_progress ump
                                   WHERE ump.id_step = s.id_step
                                   AND ump.id_user = ?
                               ) THEN true
                               WHEN t.id_test IS NOT NULL AND EXISTS(
                                   SELECT 1 
                                   FROM Results r 
                                   JOIN Answers a ON r.id_answer = a.id_answer
                                   WHERE r.id_test = t.id_test 
                                   AND a.id_user = ?
                                   AND CAST(r.score_result AS INTEGER) >= 60
                               ) THEN true
                               ELSE false
                           END as is_completed
                    FROM Steps s
                    LEFT JOIN Material m ON s.id_step = m.id_step
                    LEFT JOIN Tests t ON s.id_step = t.id_step
                    WHERE s.id_lesson = ?
                    ORDER BY s.id_step
                ");
                $stmt->execute([$user_id, $user_id, $lesson_id]);
                $steps = $stmt->fetchAll();
                
                // Обновляем прогресс
                $total_steps = count($steps);
                $completed_steps = array_reduce($steps, function($carry, $step) {
                    return $carry + ($step['is_completed'] ? 1 : 0);
                }, 0);
                $progress_percentage = $total_steps > 0 ? round(($completed_steps / $total_steps) * 100) : 0;
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
    
    // Если это POST запрос для прохождения теста
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_id'])) {
        $test_id = (int)$_POST['test_id'];
        
        // Проверяем, что тест принадлежит этому уроку
        $valid_test = false;
        foreach ($steps as $step) {
            if ($step['id_test'] == $test_id) {
                $valid_test = true;
                break;
            }
        }
        
        if ($valid_test) {
            // Получаем вопросы теста
            $stmt = $pdo->prepare("
                SELECT q.*, 
                       (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
                FROM Questions q
                WHERE q.id_test = ?
                ORDER BY q.id_question
            ");
            $stmt->execute([$test_id]);
            $questions = $stmt->fetchAll();
            
            $correct_answers = 0;
            $total_questions = count($questions);
            
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            try {
                // Создаем запись о результате теста
                $stmt = $pdo->prepare("
                    INSERT INTO Results (id_test, id_user, date_result)
                    VALUES (?, ?, CURRENT_DATE)
                    RETURNING id_result
                ");
                $stmt->execute([$test_id, $user_id]);
                $result_id = $stmt->fetchColumn();
                
                // Проверяем ответы
                foreach ($questions as $question) {
                    $question_id = $question['id_question'];
                    $user_answer = $_POST['answer_' . $question_id] ?? null;
                    
                    if ($user_answer !== null) {
                        // Получаем правильный ответ
                        $stmt = $pdo->prepare("
                            SELECT id_option 
                            FROM Answer_options 
                            WHERE id_question = ? AND is_correct = true
                        ");
                        $stmt->execute([$question_id]);
                        $correct_option = $stmt->fetchColumn();
                        
                        // Записываем ответ
                        $stmt = $pdo->prepare("
                            INSERT INTO Answers (id_question, id_user, id_option)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$question_id, $user_id, $user_answer]);
                        
                        if ($user_answer == $correct_option) {
                            $correct_answers++;
                        }
                    }
                }
                
                // Обновляем результат теста
                $score = ($correct_answers / $total_questions) * 100;
                $stmt = $pdo->prepare("
                    UPDATE Results 
                    SET score_result = ? 
                    WHERE id_result = ?
                ");
                $stmt->execute([$score, $result_id]);
                
                $pdo->commit();
                
                $success = "Тест завершен! Ваш результат: " . round($score) . "%";
                
                // Перезагружаем информацию о шагах
                $stmt = $pdo->prepare("
                    SELECT s.*,
                           m.path_matial as file_path,
                           t.id_test,
                           CASE 
                               WHEN m.id_material IS NOT NULL AND s.status_step = 'completed' THEN true
                               WHEN t.id_test IS NOT NULL AND EXISTS(
                                   SELECT 1 FROM Results r 
                                   WHERE r.id_test = t.id_test 
                                   AND r.id_user = ?
                               ) THEN true
                               ELSE false
                           END as is_completed
                    FROM Steps s
                    LEFT JOIN Material m ON s.id_step = m.id_step
                    LEFT JOIN Tests t ON s.id_step = t.id_step
                    WHERE s.id_lesson = ?
                    ORDER BY s.id_step
                ");
                $stmt->execute([$user_id, $lesson_id]);
                $steps = $stmt->fetchAll();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    }
    
} catch (PDOException $e) {
    $error = 'Ошибка базы данных: ' . $e->getMessage();
}

// Функция для получения вопросов и вариантов ответов теста
function get_test_questions($pdo, $test_id) {
    $stmt = $pdo->prepare("
        SELECT q.*, 
               (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
        FROM Questions q
        WHERE q.id_test = ?
        ORDER BY q.id_question
    ");
    $stmt->execute([$test_id]);
    return $stmt->fetchAll();
}

// Функция для получения вариантов ответов вопроса
function get_question_options($pdo, $question_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM Answer_options
        WHERE id_question = ?
        ORDER BY id_option
    ");
    $stmt->execute([$question_id]);
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lesson['name_lesson']) ?> - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <!-- Основное содержимое -->
        <div class="eleven wide column">
            <div class="ui clearing segment">
                <h2 class="ui left floated header">
                    <?= htmlspecialchars($lesson['name_lesson']) ?>
                    <div class="sub header">
                        Курс: <?= htmlspecialchars($lesson['name_course']) ?>
                    </div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="course.php?id=<?= $lesson['id_course'] ?>" class="ui button">
                        Назад к курсу
                    </a>
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

            <!-- Шаги урока -->
            <?php if (empty($steps)): ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="tasks icon"></i>
                        В уроке пока нет шагов
                    </div>
                </div>
            <?php else: ?>
                <div class="ui segments">
                    <?php foreach ($steps as $step): ?>
                        <div class="ui segment">
                            <h3 class="ui header">
                                <?= htmlspecialchars($step['number_steps'] ?? 'Шаг') ?>
                                <?php if ($step['is_completed']): ?>
                                    <i class="green check circle icon"></i>
                                <?php endif; ?>
                            </h3>
                            
                            <?php if ($step['type_step'] === 'material' && $step['file_path']): ?>
                                <a href="<?= htmlspecialchars($step['file_path']) ?>" class="ui primary button" target="_blank">
                                    <i class="file pdf icon"></i>
                                    Открыть материал
                                </a>
                                <?php if (!$step['is_completed']): ?>
                                    <form method="post" style="display: inline-block; margin-left: 10px;">
                                        <input type="hidden" name="mark_material_completed" value="1">
                                        <input type="hidden" name="step_id" value="<?= $step['id_step'] ?>">
                                        <button type="submit" class="ui positive button">
                                            <i class="check icon"></i>
                                            Отметить как прочитанное
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php elseif ($step['type_step'] === 'test' && $step['id_test']): ?>
                                <?php if (!$step['is_completed']): ?>
                                    <?php
                                    $questions = get_test_questions($pdo, $step['id_test']);
                                    if (!empty($questions)):
                                    ?>
                                        <form class="ui form" method="post">
                                            <input type="hidden" name="test_id" value="<?= $step['id_test'] ?>">
                                            
                                            <?php foreach ($questions as $question): ?>
                                                <div class="field">
                                                    <label><?= htmlspecialchars($question['text_question']) ?></label>
                                                    <?php
                                                    $options = get_question_options($pdo, $question['id_question']);
                                                    foreach ($options as $option):
                                                    ?>
                                                        <div class="field">
                                                            <div class="ui radio checkbox">
                                                                <input type="radio" name="answer_<?= $question['id_question'] ?>" value="<?= $option['id_option'] ?>" required>
                                                                <label><?= htmlspecialchars($option['text_option']) ?></label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <button type="submit" class="ui primary button">Отправить ответы</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="ui message">
                                        <i class="info circle icon"></i>
                                        Тест уже пройден
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Боковая панель с прогрессом -->
        <div class="five wide column">
            <div class="ui sticky">
                <div class="ui segment">
                    <h3 class="ui header">Прогресс урока</h3>
                    <div class="ui indicating progress" data-percent="<?= $progress_percentage ?>">
                        <div class="bar">
                            <div class="progress"><?= $progress_percentage ?>%</div>
                        </div>
                        <div class="label">Завершено <?= $completed_steps ?> из <?= $total_steps ?> шагов</div>
                    </div>
                </div>
                
                <div class="ui segment">
                    <h4 class="ui header">Содержание урока</h4>
                    <div class="ui list">
                        <?php foreach ($steps as $step): ?>
                            <div class="item">
                                <i class="<?= $step['is_completed'] ? 'green check circle' : 'circle outline' ?> icon"></i>
                                <div class="content">
                                    <div class="header"><?= htmlspecialchars($step['number_steps'] ?? 'Шаг') ?></div>
                                    <div class="description">
                                        <?= $step['type_step'] === 'material' ? 'Материал' : 'Тест' ?>
                                        <?php if ($step['is_completed']): ?>
                                            <span class="ui green text">
                                                <i class="check icon"></i>
                                                Завершено
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.sticky').sticky({
        context: '.ui.grid'
    });
    
    $('.ui.progress').progress();
    $('.ui.checkbox').checkbox();
    
    $('.ui.form').form({
        onSuccess: function() {
            $(this).addClass('loading');
        }
    });
});
</script>

</body>
</html> 