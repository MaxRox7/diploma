<?php
require_once 'config.php';
redirect_unauthenticated();

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$is_admin_view = is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1;
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Get test information and check access rights
    if ($is_admin_view) {
        // Admin in view mode can access any test
        $stmt = $pdo->prepare("
            SELECT t.*, s.id_step, s.number_steps as name_step, l.id_lesson, l.name_lesson, c.id_course, c.name_course
            FROM Tests t
            JOIN Steps s ON t.id_step = s.id_step
            JOIN lessons l ON s.id_lesson = l.id_lesson
            JOIN course c ON l.id_course = c.id_course
            WHERE t.id_test = ?
        ");
        $stmt->execute([$test_id]);
    } else {
        // For teachers, check if they are the creator
        if (is_teacher()) {
            $stmt = $pdo->prepare("
                SELECT t.*, s.id_step, s.number_steps as name_step, l.id_lesson, l.name_lesson, c.id_course, c.name_course
                FROM Tests t
                JOIN Steps s ON t.id_step = s.id_step
                JOIN lessons l ON s.id_lesson = l.id_lesson
                JOIN course c ON l.id_course = c.id_course
                WHERE t.id_test = ?
            ");
            $stmt->execute([$test_id]);
            $test_temp = $stmt->fetch();
            
            if ($test_temp && !is_course_creator($pdo, $test_temp['id_course'], $_SESSION['user']['id_user'])) {
                $test_temp = null; // Если не создатель, убираем доступ
            }
            $test = $test_temp;
        } else {
            // For students
            $stmt = $pdo->prepare("
                SELECT t.*, s.id_step, s.number_steps as name_step, l.id_lesson, l.name_lesson, c.id_course, c.name_course
                FROM Tests t
                JOIN Steps s ON t.id_step = s.id_step
                JOIN lessons l ON s.id_lesson = l.id_lesson
                JOIN course c ON l.id_course = c.id_course
                JOIN create_passes cp ON c.id_course = cp.id_course
                WHERE t.id_test = ? AND cp.id_user = ? AND cp.is_creator = false
            ");
            $stmt->execute([$test_id, $_SESSION['user']['id_user']]);
            $test = $stmt->fetch();
        }
    }
    
    if (!$test) {
        header('Location: courses.php');
        exit;
    }

    // Check if there's an active attempt
    $stmt = $pdo->prepare("
        SELECT * FROM test_attempts 
        WHERE id_test = ? AND id_user = ? AND status = 'in_progress'
        ORDER BY start_time DESC LIMIT 1
    ");
    $stmt->execute([$test_id, $_SESSION['user']['id_user']]);
    $current_attempt = $stmt->fetch();

    // Get test questions
    $stmt = $pdo->prepare("
        SELECT q.* FROM Questions q
        WHERE q.id_test = ?
        ORDER BY q.id_question
    ");
    $stmt->execute([$test_id]);
    $questions = $stmt->fetchAll();

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['start_test'])) {
            // Start new attempt
            $stmt = $pdo->prepare("
                INSERT INTO test_attempts (id_test, id_user, status, max_score)
                VALUES (?, ?, 'in_progress', ?)
                RETURNING id_attempt
            ");
            $stmt->execute([$test_id, $_SESSION['user']['id_user'], count($questions)]);
            $attempt_id = $stmt->fetchColumn();
            $current_attempt = [
                'id_attempt' => $attempt_id,
                'status' => 'in_progress'
            ];
        }
        elseif (isset($_POST['submit_test'])) {
            $attempt_id = $_POST['attempt_id'];
            $score = 0;
            
            try {
                $pdo->beginTransaction();
                
                // Process each answer
                foreach ($_POST['answers'] as $question_id => $option_id) {
                    // Get correct answer
                    $stmt = $pdo->prepare("
                        SELECT is_correct FROM Answer_options 
                        WHERE id_option = ? AND id_question = ?
                    ");
                    $stmt->execute([$option_id, $question_id]);
                    $is_correct = $stmt->fetchColumn();
                    
                    if ($is_correct) {
                        $score++;
                    }
                    
                    // Record answer
                    $stmt = $pdo->prepare("
                        INSERT INTO test_answers (id_attempt, id_question, id_selected_option, is_correct)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$attempt_id, $question_id, $option_id, $is_correct]);
                }
                
                // Update attempt
                $stmt = $pdo->prepare("
                    UPDATE test_attempts 
                    SET status = 'completed', score = ?, end_time = CURRENT_TIMESTAMP
                    WHERE id_attempt = ?
                ");
                $stmt->execute([$score, $attempt_id]);
                
                $pdo->commit();
                $success = "Тест завершен! Ваш результат: $score из " . count($questions);
                $current_attempt = null;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Ошибка при сохранении результатов: ' . $e->getMessage();
            }
        }
    }

    // Get answer options for each question
    function get_question_options($pdo, $question_id, $shuffle = false) {
        $stmt = $pdo->prepare("
            SELECT * FROM Answer_options
            WHERE id_question = ?
            ORDER BY id_option
        ");
        $stmt->execute([$question_id]);
        $options = $stmt->fetchAll();
        
        // Перемешиваем варианты ответов для студентов
        if ($shuffle && !empty($options)) {
            shuffle($options);
        }
        
        return $options;
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
    <title><?= htmlspecialchars($test['name_course']) ?> - Тест</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <h1 class="ui header">
                <?= htmlspecialchars($test['name_course']) ?> - Тест
                <div class="sub header">
                    Урок: <?= htmlspecialchars($test['name_lesson']) ?><br>
                    Шаг: <?= htmlspecialchars($test['name_step']) ?>
                </div>
            </h1>

            <?php if ($is_admin_view): ?>
                <div class="ui info message">
                    <i class="eye icon"></i>
                    <strong>Режим администратора:</strong> Вы проходите тест как преподаватель
                    <a href="test.php?test_id=<?= $test_id ?>" class="ui small right floated button">Выйти из режима просмотра</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="ui success message">
                    <div class="header">Успех!</div>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <?php if (is_teacher() && is_course_creator($pdo, $test['id_course'], $_SESSION['user']['id_user'])): ?>
                <div class="ui info message">
                    <i class="info circle icon"></i>
                    <strong>Режим преподавателя:</strong> Вы можете просматривать этот тест, но не проходить его.
                    <a href="edit_test.php?test_id=<?= $test_id ?>" class="ui small right floated button">Редактировать тест</a>
                </div>
                
                <div class="ui segment">
                    <h3>Предварительный просмотр теста</h3>
                    <p>Тест содержит <?= count($questions) ?> вопросов.</p>
                    
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="ui segment">
                            <h4>Вопрос <?= $index + 1 ?>:</h4>
                            <p><?= htmlspecialchars($question['text_question']) ?></p>
                            
                            <?php 
                            $options = get_question_options($pdo, $question['id_question'], false); // Без перемешивания для преподавателей
                            foreach ($options as $option): 
                            ?>
                                <div class="ui <?= $option['is_correct'] ? 'green' : '' ?> label" style="display: block; margin: 5px 0;">
                                    <?= htmlspecialchars($option['text_option']) ?>
                                    <?php if ($option['is_correct']): ?>
                                        <i class="check icon"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            <?php elseif (!$current_attempt && !$success): ?>
                <div class="ui segment">
                    <h3>Начать тест</h3>
                    <p>Тест содержит <?= count($questions) ?> вопросов.</p>
                    <form method="post" class="ui form">
                        <button type="submit" name="start_test" class="ui primary button">Начать тест</button>
                    </form>
                </div>
            <?php elseif ($current_attempt): ?>
                <form method="post" class="ui form">
                    <input type="hidden" name="attempt_id" value="<?= $current_attempt['id_attempt'] ?>">
                    
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="ui segment">
                            <div class="field">
                                <label>
                                    <h4>Вопрос <?= $index + 1 ?>:</h4>
                                    <?= htmlspecialchars($question['text_question']) ?>
                                </label>
                                
                                <?php 
                                $options = get_question_options($pdo, $question['id_question'], true); // Перемешиваем для студентов
                                foreach ($options as $option): 
                                ?>
                                    <div class="ui radio checkbox" style="display: block; margin: 10px 0;">
                                        <input type="radio" 
                                               name="answers[<?= $question['id_question'] ?>]" 
                                               value="<?= $option['id_option'] ?>" 
                                               required>
                                        <label><?= htmlspecialchars($option['text_option']) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" name="submit_test" class="ui primary button">
                        Завершить тест
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="ui segment">
                    <?php if ($is_admin_view): ?>
                        <a href="edit_test.php?test_id=<?= $test_id ?>&admin_view=1" class="ui button">
                            Назад к редактированию теста
                        </a>
                    <?php elseif (is_teacher() && is_course_creator($pdo, $test['id_course'], $_SESSION['user']['id_user'])): ?>
                        <a href="edit_test.php?test_id=<?= $test_id ?>" class="ui button">
                            Назад к редактированию теста
                        </a>
                    <?php else: ?>
                        <a href="lesson.php?id=<?= $test['id_lesson'] ?>" class="ui button">
                            Назад к уроку
                        </a>
                        <a href="course.php?id=<?= $test['id_course'] ?>" class="ui button">
                            Вернуться к курсу
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="ui segment">
                    <?php if ($is_admin_view): ?>
                        <a href="edit_test.php?test_id=<?= $test_id ?>&admin_view=1" class="ui button">
                            Назад к редактированию теста
                        </a>
                    <?php elseif (is_teacher() && is_course_creator($pdo, $test['id_course'], $_SESSION['user']['id_user'])): ?>
                        <a href="edit_test.php?test_id=<?= $test_id ?>" class="ui button">
                            Назад к редактированию теста
                        </a>
                    <?php else: ?>
                        <a href="lesson.php?id=<?= $test['id_lesson'] ?>" class="ui button">
                            Назад к уроку
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.radio.checkbox').checkbox();
});
</script>

</body>
</html>
