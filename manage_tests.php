<?php
require_once 'config.php';
redirect_unauthenticated();

$step_id = isset($_GET['step_id']) ? (int)$_GET['step_id'] : 0;
$lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
$is_admin_view = is_admin() && (isset($_GET['admin_view']) && $_GET['admin_view'] == 1) || (isset($_POST['admin_view']) && $_POST['admin_view'] == 1);
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Check access based on the context (either step_id or lesson_id)
    if ($step_id > 0) {
        // Get step information and check access rights
        if ($is_admin_view) {
            // Admin in view mode can access any step
            $stmt = $pdo->prepare("
                SELECT s.*, l.id_lesson, l.name_lesson, c.id_course, c.name_course
                FROM Steps s
                JOIN lessons l ON s.id_lesson = l.id_lesson
                JOIN course c ON l.id_course = c.id_course
                WHERE s.id_step = ?
            ");
            $stmt->execute([$step_id]);
        } else {
            // Regular access check
            $stmt = $pdo->prepare("
                SELECT s.*, l.id_lesson, l.name_lesson, c.id_course, c.name_course
                FROM Steps s
                JOIN lessons l ON s.id_lesson = l.id_lesson
                JOIN course c ON l.id_course = c.id_course
                JOIN create_passes cp ON c.id_course = cp.id_course
                WHERE s.id_step = ? AND cp.id_user = ? AND cp.is_creator = true
                AND EXISTS (
                    SELECT 1 FROM users u 
                    WHERE u.id_user = cp.id_user AND u.role_user = ?
                )
            ");
            $stmt->execute([$step_id, $_SESSION['user']['id_user'], ROLE_TEACHER]);
        }
        $step = $stmt->fetch();
        
        if (!$step) {
            header('Location: courses.php');
            exit;
        }
        
        // Get tests for this step
        $stmt = $pdo->prepare("
            SELECT t.*,
                   (SELECT COUNT(*) FROM Questions q WHERE q.id_test = t.id_test) as question_count,
                   (SELECT COUNT(*) FROM test_attempts ta WHERE ta.id_test = t.id_test) as attempt_count
            FROM Tests t
            WHERE t.id_step = ?
            ORDER BY t.id_test
        ");
        $stmt->execute([$step_id]);
        $tests = $stmt->fetchAll();
    } elseif ($lesson_id > 0 && isset($_POST['action']) && $_POST['action'] === 'create_test_step') {
        // Check access for lesson
        if ($is_admin_view) {
            // Admin in view mode can access any lesson
            $stmt = $pdo->prepare("
                SELECT l.*, c.id_course, c.name_course
                FROM lessons l
                JOIN course c ON l.id_course = c.id_course
                WHERE l.id_lesson = ?
            ");
            $stmt->execute([$lesson_id]);
        } else {
            // Regular access check
            $stmt = $pdo->prepare("
                SELECT l.*, c.id_course, c.name_course
                FROM lessons l
                JOIN course c ON l.id_course = c.id_course
                JOIN create_passes cp ON c.id_course = cp.id_course
                WHERE l.id_lesson = ? AND cp.id_user = ? AND cp.is_creator = true
                AND EXISTS (
                    SELECT 1 FROM users u 
                    WHERE u.id_user = cp.id_user AND u.role_user = ?
                )
            ");
            $stmt->execute([$lesson_id, $_SESSION['user']['id_user'], ROLE_TEACHER]);
        }
        $lesson = $stmt->fetch();
        
        if (!$lesson) {
            header('Location: courses.php');
            exit;
        }
    } else {
        header('Location: courses.php');
        exit;
    }
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            // Create test step (from edit_steps.php)
            if ($_POST['action'] === 'create_test_step') {
                $lesson_id = (int)$_POST['lesson_id'];
                $name_step = trim($_POST['name_step']);
                
                if (empty($name_step)) {
                    $error = 'Введите название шага';
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        // Create step
                        $stmt = $pdo->prepare("
                            INSERT INTO Steps (id_lesson, number_steps, type_step)
                            VALUES (?, ?, 'test')
                            RETURNING id_step
                        ");
                        $stmt->execute([$lesson_id, $name_step]);
                        $step_id = $stmt->fetchColumn();
                        
                        // Create test
                        $stmt = $pdo->prepare("
                            INSERT INTO Tests (id_step, name_test, desc_test)
                            VALUES (?, 'Новый тест', '')
                            RETURNING id_test
                        ");
                        $stmt->execute([$step_id]);
                        $test_id = $stmt->fetchColumn();
                        
                        $pdo->commit();
                        
                        // Update step info for the page
                        $stmt = $pdo->prepare("
                            SELECT s.*, l.id_lesson, l.name_lesson, c.id_course, c.name_course
                            FROM Steps s
                            JOIN lessons l ON s.id_lesson = l.id_lesson
                            JOIN course c ON l.id_course = c.id_course
                            WHERE s.id_step = ?
                        ");
                        $stmt->execute([$step_id]);
                        $step = $stmt->fetch();
                        
                        $success = 'Шаг с тестом успешно создан';
                        
                        // Redirect back to edit_steps.php
                        header("Location: edit_steps.php?lesson_id=" . $lesson_id . ($is_admin_view ? "&admin_view=1" : ""));
                        exit;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = 'Ошибка при создании шага с тестом: ' . $e->getMessage();
                    }
                }
            }
            // Add new test
            elseif ($_POST['action'] === 'add_test') {
                $test_name = trim($_POST['test_name']);
                $test_description = trim($_POST['test_description']);
                $passing_percentage = (int)$_POST['passing_percentage'];
                $max_attempts = (int)$_POST['max_attempts'];
                $time_between_attempts = (int)$_POST['time_between_attempts'];
                $show_results = isset($_POST['show_results']) ? 'true' : 'false';
                $practice_mode = isset($_POST['practice_mode']) ? 'true' : 'false';
                
                if (empty($test_name)) {
                    $error = 'Введите название теста';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO Tests (id_step, name_test, desc_test, 
                                              passing_percentage, max_attempts, 
                                              time_between_attempts, show_results_after_completion,
                                              practice_mode)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            RETURNING id_test
                        ");
                        $stmt->execute([
                            $step_id, 
                            $test_name, 
                            $test_description,
                            $passing_percentage,
                            $max_attempts,
                            $time_between_attempts,
                            $show_results,
                            $practice_mode
                        ]);
                        $test_id = $stmt->fetchColumn();
                        
                        // Создаем уровни оценок по умолчанию
                        $default_levels = [
                            ['0', '59', 'Не пройдено', '#DB2828'], // красный
                            ['60', '74', 'Удовлетворительно', '#F2711C'], // оранжевый
                            ['75', '89', 'Хорошо', '#2185D0'], // синий
                            ['90', '100', 'Отлично', '#21BA45'] // зеленый
                        ];
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO test_grade_levels (id_test, min_percentage, max_percentage, grade_name, grade_color)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        foreach ($default_levels as $level) {
                            $stmt->execute([
                                $test_id,
                                $level[0],
                                $level[1],
                                $level[2],
                                $level[3]
                            ]);
                        }
                        
                        $success = 'Тест успешно создан';
                        header("Location: edit_test.php?test_id=" . $test_id);
                        exit;
                        
                    } catch (Exception $e) {
                        $error = 'Ошибка при создании теста: ' . $e->getMessage();
                    }
                }
            }
            // Delete test
            elseif ($_POST['action'] === 'delete_test') {
                $test_id = (int)$_POST['test_id'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Delete test answers first
                    $stmt = $pdo->prepare("
                        DELETE FROM test_answers 
                        WHERE id_attempt IN (
                            SELECT id_attempt FROM test_attempts WHERE id_test = ?
                        )
                        OR id_question IN (
                            SELECT id_question FROM Questions WHERE id_test = ?
                        )
                    ");
                    $stmt->execute([$test_id, $test_id]);
                    
                    // Then delete test attempts
                    $stmt = $pdo->prepare("DELETE FROM test_attempts WHERE id_test = ?");
                    $stmt->execute([$test_id]);
                    
                    // Then delete answer options
                    $stmt = $pdo->prepare("
                        DELETE FROM Answer_options 
                        WHERE id_question IN (
                            SELECT id_question FROM Questions WHERE id_test = ?
                        )
                    ");
                    $stmt->execute([$test_id]);
                    
                    // Delete questions
                    $stmt = $pdo->prepare("DELETE FROM Questions WHERE id_test = ?");
                    $stmt->execute([$test_id]);
                    
                    // Delete test
                    $stmt = $pdo->prepare("DELETE FROM Tests WHERE id_test = ? AND id_step = ?");
                    $stmt->execute([$test_id, $step_id]);
                    
                    $pdo->commit();
                    $success = 'Тест успешно удален';
                    
                    // Refresh tests list
                    $stmt = $pdo->prepare("
                        SELECT t.*,
                               (SELECT COUNT(*) FROM Questions q WHERE q.id_test = t.id_test) as question_count,
                               (SELECT COUNT(*) FROM test_attempts ta WHERE ta.id_test = t.id_test) as attempt_count
                        FROM Tests t
                        WHERE t.id_step = ?
                        ORDER BY t.id_test
                    ");
                    $stmt->execute([$step_id]);
                    $tests = $stmt->fetchAll();
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Ошибка при удалении теста: ' . $e->getMessage();
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
    <title>Управление тестами - CodeSphere</title>
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
                    Управление тестами
                    <div class="sub header">
                        Курс: <?= htmlspecialchars($step['name_course']) ?><br>
                        Урок: <?= htmlspecialchars($step['name_lesson']) ?><br>
                        Шаг: <?= htmlspecialchars($step['number_steps']) ?>
                    </div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="edit_steps.php?lesson_id=<?= $step['id_lesson'] ?>" class="ui button">
                        Назад к шагам
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
                    <div class="header">Успех!</div>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <!-- Форма добавления теста -->
            <div class="ui segment">
                <h3>Добавить новый тест</h3>
                <form method="post" class="ui form">
                    <input type="hidden" name="action" value="add_test">
                    
                    <div class="field">
                        <label>Название теста</label>
                        <input type="text" name="test_name" placeholder="Введите название теста" required>
                    </div>
                    
                    <div class="field">
                        <label>Описание теста</label>
                        <textarea name="test_description" rows="2" placeholder="Введите описание теста"></textarea>
                    </div>
                    
                    <div class="field">
                        <label>Проходной балл (в процентах)</label>
                        <input type="number" name="passing_percentage" min="0" max="100" value="70" required>
                    </div>

                    <div class="field">
                        <label>Максимальное количество попыток</label>
                        <input type="number" name="max_attempts" min="1" value="3" required>
                    </div>

                    <div class="field">
                        <label>Время между попытками (в секундах)</label>
                        <input type="number" name="time_between_attempts" min="0" value="60" required>
                    </div>

                    <div class="field">
                        <label>Показывать результаты после завершения</label>
                        <input type="checkbox" name="show_results" value="1">
                    </div>

                    <div class="field">
                        <label>Режим практики</label>
                        <input type="checkbox" name="practice_mode" value="1">
                    </div>

                    <button type="submit" class="ui primary button">Создать тест</button>
                </form>
            </div>

            <!-- Список тестов -->
            <div class="ui segment">
                <h3>Тесты в этом шаге</h3>
                <?php if (empty($tests)): ?>
                    <div class="ui placeholder segment">
                        <div class="ui icon header">
                            <i class="tasks icon"></i>
                            В этом шаге пока нет тестов
                        </div>
                    </div>
                <?php else: ?>
                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Описание</th>
                                <th>Проходной %</th>
                                <th>Макс. попыток</th>
                                <th>Режим</th>
                                <th>Вопросов</th>
                                <th>Попыток</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tests as $test): ?>
                                <tr>
                                    <td><?= htmlspecialchars($test['name_test']) ?></td>
                                    <td><?= htmlspecialchars($test['desc_test'] ?? '') ?></td>
                                    <td><?= $test['passing_percentage'] ?? 70 ?>%</td>
                                    <td><?= $test['max_attempts'] ?? 3 ?></td>
                                    <td><?= $test['practice_mode'] ? 'Практика' : 'Экзамен' ?></td>
                                    <td><?= $test['question_count'] ?></td>
                                    <td><?= $test['attempt_count'] ?></td>
                                    <td>
                                        <div class="ui tiny buttons">
                                            <a href="edit_test.php?test_id=<?= $test['id_test'] ?>" 
                                               class="ui primary button">
                                                Редактировать
                                            </a>
                                            <a href="test_results.php?test_id=<?= $test['id_test'] ?>" 
                                               class="ui button">
                                                Результаты
                                            </a>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_test">
                                                <input type="hidden" name="test_id" value="<?= $test['id_test'] ?>">
                                                <button type="submit" 
                                                        class="ui negative button"
                                                        onclick="return confirm('Вы уверены, что хотите удалить этот тест? Все результаты будут удалены.');">
                                                    Удалить
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
});
</script>

</body>
</html> 