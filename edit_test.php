<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем наличие ID теста
if (!isset($_GET['test_id'])) {
    header('Location: courses.php');
    exit;
}

$test_id = (int)$_GET['test_id'];
$is_admin_view = is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1;
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Инициализируем переменную questions
    $questions = [];
    
    // Получаем информацию о тесте и проверяем права доступа
    if ($is_admin_view) {
        // Admin in view mode can access any test
        $stmt = $pdo->prepare("
            SELECT t.*, s.id_step, s.number_steps, l.id_lesson, l.name_lesson, c.id_course, c.name_course
            FROM Tests t
            JOIN Steps s ON t.id_step = s.id_step
            JOIN lessons l ON s.id_lesson = l.id_lesson
            JOIN course c ON l.id_course = c.id_course
            WHERE t.id_test = ?
        ");
        $stmt->execute([$test_id]);
    } else {
        // Regular access check
        $stmt = $pdo->prepare("
            SELECT t.*, s.id_step, s.number_steps, l.id_lesson, l.name_lesson, c.id_course, c.name_course, cp.id_user
            FROM Tests t
            JOIN Steps s ON t.id_step = s.id_step
            JOIN lessons l ON s.id_lesson = l.id_lesson
            JOIN course c ON l.id_course = c.id_course
            JOIN create_passes cp ON c.id_course = cp.id_course
            WHERE t.id_test = ? AND cp.id_user = ? AND cp.is_creator = true
            AND EXISTS (
                SELECT 1 FROM users u 
                WHERE u.id_user = cp.id_user AND u.role_user = ?
            )
        ");
        $stmt->execute([$test_id, $_SESSION['user']['id_user'], ROLE_TEACHER]);
    }
    $test = $stmt->fetch();
    
    if (!$test) {
        header('Location: courses.php');
        exit;
    }
    
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
    
    // Получаем уровни оценок для этого теста
    $stmt = $pdo->prepare("
        SELECT * FROM test_grade_levels
        WHERE id_test = ?
        ORDER BY min_percentage
    ");
    $stmt->execute([$test_id]);
    $grade_levels = $stmt->fetchAll();
    
    // Обработка POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            // Обновление настроек теста
            if ($_POST['action'] === 'update_test_settings') {
                $test_name = trim($_POST['test_name'] ?? '');
                $test_description = trim($_POST['test_description'] ?? '');
                $passing_percentage = min(100, max(0, (int)($_POST['passing_percentage'] ?? 70)));
                $max_attempts = max(1, (int)($_POST['max_attempts'] ?? 3));
                $time_between_attempts = max(0, (int)($_POST['time_between_attempts'] ?? 0));
                $show_results = isset($_POST['show_results']) ? 1 : 0;
                $practice_mode = isset($_POST['practice_mode']) ? 1 : 0;
                
                try {
                    $stmt = $pdo->prepare("
                        UPDATE Tests 
                        SET name_test = ?, 
                            desc_test = ?,
                            passing_percentage = ?,
                            max_attempts = ?,
                            time_between_attempts = ?,
                            show_results_after_completion = ?,
                            practice_mode = ?
                        WHERE id_test = ?
                    ");
                    $stmt->execute([
                        $test_name,
                        $test_description,
                        $passing_percentage,
                        $max_attempts,
                        $time_between_attempts,
                        $show_results,
                        $practice_mode,
                        $test_id
                    ]);
                    
                    // Обновляем уровни оценок, если они были отправлены
                    if (isset($_POST['grade_levels']) && is_array($_POST['grade_levels'])) {
                        // Сначала удаляем существующие уровни
                        $stmt = $pdo->prepare("DELETE FROM test_grade_levels WHERE id_test = ?");
                        $stmt->execute([$test_id]);
                        
                        // Затем добавляем новые
                        $stmt = $pdo->prepare("
                            INSERT INTO test_grade_levels 
                            (id_test, min_percentage, max_percentage, grade_name, grade_color)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        foreach ($_POST['grade_levels'] as $level) {
                            if (isset($level['min'], $level['max'], $level['name'])) {
                                $min = min(100, max(0, (int)$level['min']));
                                $max = min(100, max(0, (int)$level['max']));
                                $name = trim($level['name']);
                                $color = preg_match('/^#[0-9a-f]{6}$/i', $level['color'] ?? '') 
                                    ? $level['color'] 
                                    : '#000000';
                                
                                if ($min < $max && !empty($name)) {
                                    $stmt->execute([$test_id, $min, $max, $name, $color]);
                                }
                            }
                        }
                    }
                    
                    $success = 'Настройки теста успешно обновлены';
                    
                    // Обновляем информацию о тесте
                    $stmt = $pdo->prepare("SELECT * FROM Tests WHERE id_test = ?");
                    $stmt->execute([$test_id]);
                    $test = array_merge($test, $stmt->fetch());
                    
                    // Обновляем уровни оценок
                    $stmt = $pdo->prepare("
                        SELECT * FROM test_grade_levels
                        WHERE id_test = ?
                        ORDER BY min_percentage
                    ");
                    $stmt->execute([$test_id]);
                    $grade_levels = $stmt->fetchAll();
                    
                } catch (Exception $e) {
                    $error = 'Ошибка при обновлении настроек теста: ' . $e->getMessage();
                }
            }
            // Добавление нового вопроса
            elseif ($_POST['action'] === 'add_question') {
                $question_text = trim($_POST['question_text']);
                $type_question = $_POST['type_question'] ?? 'single';
                if (empty($question_text)) {
                    $error = 'Введите текст вопроса';
                } else {
                    try {
                        $pdo->beginTransaction();
                        // Добавляем вопрос
                        $stmt = $pdo->prepare("
                            INSERT INTO Questions (id_test, text_question, answer_question, type_question)
                            VALUES (?, ?, '', ?)
                            RETURNING id_question
                        ");
                        $stmt->execute([$test_id, $question_text, $type_question]);
                        $question_id = $stmt->fetchColumn();
                        // SINGLE
                        if ($type_question === 'single') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_option = (int)$_POST['correct_option'];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([strval($correct_option), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MULTI
                        elseif ($type_question === 'multi') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_options = $_POST['correct_options'] ?? [];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([implode(',', $correct_options), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MATCH
                        elseif ($type_question === 'match') {
                            $left = $_POST['match_left'] ?? [];
                            $right = $_POST['match_right'] ?? [];
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            for ($i = 0; $i < count($left); $i++) {
                                $pair = trim($left[$i]) . '||' . trim($right[$i]);
                                $stmt->execute([
                                    $question_id,
                                    $pair
                                ]);
                            }
                        }
                        // CODE
                        elseif ($type_question === 'code') {
                            // Получаем данные для задания с кодом
                            $code_language = $_POST['code_language'] ?? 'php';
                            $code_template = $_POST['code_template'] ?? '';
                            $code_input = $_POST['code_input'] ?? '';
                            $code_output = $_POST['code_output'] ?? '';
                            $code_timeout = (int)($_POST['code_timeout'] ?? 5);
                            
                            // Проверяем обязательные поля
                            if (empty($code_output)) {
                                throw new Exception('Необходимо указать ожидаемый вывод для задания с кодом');
                            }
                            
                            // Сохраняем в таблицу code_tasks
                            $stmt = $pdo->prepare("
                                INSERT INTO code_tasks (id_question, template_code, input_ct, output_ct, language, execution_timeout)
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $question_id,
                                $code_template,
                                $code_input,
                                $code_output,
                                $code_language,
                                $code_timeout
                            ]);
                        }
                        $pdo->commit();
                        $success = 'Вопрос успешно добавлен';
                        // Перезагружаем список вопросов
                        $stmt = $pdo->prepare("
                            SELECT q.*, 
                                   (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
                            FROM Questions q
                            WHERE q.id_test = ?
                            ORDER BY q.id_question
                        ");
                        $stmt->execute([$test_id]);
                        $questions = $stmt->fetchAll();
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = $e->getMessage();
                    }
                }
            }
            // Удаление вопроса
            elseif ($_POST['action'] === 'delete_question') {
                $question_id = (int)$_POST['question_id'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Удаляем варианты ответов
                    $stmt = $pdo->prepare("DELETE FROM Answer_options WHERE id_question = ?");
                    $stmt->execute([$question_id]);
                    
                    // Удаляем вопрос
                    $stmt = $pdo->prepare("DELETE FROM Questions WHERE id_question = ? AND id_test = ?");
                    $stmt->execute([$question_id, $test_id]);
                    
                    $pdo->commit();
                    $success = 'Вопрос успешно удален';
                    
                    // Перезагружаем список вопросов
                    $stmt = $pdo->prepare("
                        SELECT q.*, 
                               (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
                        FROM Questions q
                        WHERE q.id_test = ?
                        ORDER BY q.id_question
                    ");
                    $stmt->execute([$test_id]);
                    $questions = $stmt->fetchAll();
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = $e->getMessage();
                }
            }
            // Редактирование вопроса
            elseif ($_POST['action'] === 'edit_question') {
                $question_id = (int)$_POST['question_id'];
                $question_text = trim($_POST['question_text']);
                $type_question = $_POST['type_question'] ?? 'single';
                if (empty($question_text)) {
                    $error = 'Введите текст вопроса';
                } else {
                    try {
                        $pdo->beginTransaction();
                        // Обновляем вопрос
                        $stmt = $pdo->prepare("
                            UPDATE Questions 
                            SET text_question = ?, answer_question = '', type_question = ?
                            WHERE id_question = ? AND id_test = ?
                        ");
                        $stmt->execute([$question_text, $type_question, $question_id, $test_id]);
                        // Удаляем старые варианты ответов
                        $stmt = $pdo->prepare("DELETE FROM Answer_options WHERE id_question = ?");
                        $stmt->execute([$question_id]);
                        // SINGLE
                        if ($type_question === 'single') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_option = (int)$_POST['correct_option'];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([strval($correct_option), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MULTI
                        elseif ($type_question === 'multi') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_options = $_POST['correct_options'] ?? [];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([implode(',', $correct_options), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MATCH
                        elseif ($type_question === 'match') {
                            $left = $_POST['match_left'] ?? [];
                            $right = $_POST['match_right'] ?? [];
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            for ($i = 0; $i < count($left); $i++) {
                                $pair = trim($left[$i]) . '||' . trim($right[$i]);
                                $stmt->execute([
                                    $question_id,
                                    $pair
                                ]);
                            }
                        }
                        // CODE
                        elseif ($type_question === 'code') {
                            // Получаем данные для задания с кодом
                            $code_language = $_POST['code_language'] ?? 'php';
                            $code_template = $_POST['code_template'] ?? '';
                            $code_input = $_POST['code_input'] ?? '';
                            $code_output = $_POST['code_output'] ?? '';
                            $code_timeout = (int)($_POST['code_timeout'] ?? 5);
                            
                            // Проверяем обязательные поля
                            if (empty($code_output)) {
                                throw new Exception('Необходимо указать ожидаемый вывод для задания с кодом');
                            }
                            
                            // Проверяем, существует ли уже запись для этого вопроса
                            $stmt = $pdo->prepare("SELECT id_ct FROM code_tasks WHERE id_question = ?");
                            $stmt->execute([$question_id]);
                            $code_task_id = $stmt->fetchColumn();
                            
                            if ($code_task_id) {
                                // Обновляем существующую запись
                                $stmt = $pdo->prepare("
                                    UPDATE code_tasks 
                                    SET template_code = ?, input_ct = ?, output_ct = ?, language = ?, execution_timeout = ?
                                    WHERE id_question = ?
                                ");
                                $stmt->execute([
                                    $code_template,
                                    $code_input,
                                    $code_output,
                                    $code_language,
                                    $code_timeout,
                                    $question_id
                                ]);
                            } else {
                                // Создаем новую запись
                                $stmt = $pdo->prepare("
                                    INSERT INTO code_tasks (id_question, template_code, input_ct, output_ct, language, execution_timeout)
                                    VALUES (?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $question_id,
                                    $code_template,
                                    $code_input,
                                    $code_output,
                                    $code_language,
                                    $code_timeout
                                ]);
                            }
                        }
                        $pdo->commit();
                        $success = 'Вопрос успешно обновлен';
                        // Перезагружаем список вопросов
                        $stmt = $pdo->prepare("
                            SELECT q.*, 
                                   (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
                            FROM Questions q
                            WHERE q.id_test = ?
                            ORDER BY q.id_question
                        ");
                        $stmt->execute([$test_id]);
                        $questions = $stmt->fetchAll();
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = $e->getMessage();
                    }
                }
            }
        }
    }
    
} catch (PDOException $e) {
    $error = 'Ошибка базы данных: ' . $e->getMessage();
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
    <title>Редактирование теста - CodeSphere</title>
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
                    Редактирование теста
                    <div class="sub header">
                        Курс: <?= htmlspecialchars($test['name_course'] ?? '') ?><br>
                        Урок: <?= htmlspecialchars($test['name_lesson'] ?? '') ?><br>
                        Шаг: <?= htmlspecialchars($test['number_steps'] ?? '') ?>
                    </div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="test_results.php?test_id=<?= $test_id ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui button">
                        Результаты теста
                    </a>
                    <a href="edit_steps.php?lesson_id=<?= $test['id_lesson'] ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui button">
                        Назад к шагам
                    </a>
                </div>
            </div>

            <?php if ($is_admin_view): ?>
                <div class="ui info message">
                    <i class="eye icon"></i>
                    <strong>Режим администратора:</strong> Вы редактируете тест как преподаватель
                    <a href="edit_test.php?test_id=<?= $test_id ?>" class="ui small right floated button">Выйти из режима редактирования</a>
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

            <!-- Настройки теста -->
            <div class="ui segments">
                <div class="ui segment">
                    <h3>Настройки теста</h3>
                </div>
                <div class="ui segment">
                    <form method="post" class="ui form">
                        <input type="hidden" name="action" value="update_test_settings">
                        <div class="two fields">
                            <div class="field">
                                <label>Название теста</label>
                                <input type="text" name="test_name" value="<?= htmlspecialchars($test['name_test']) ?>" required>
                            </div>
                            <div class="field">
                                <label>Проходной балл (в процентах)</label>
                                <input type="number" name="passing_percentage" min="0" max="100" value="<?= (int)($test['passing_percentage'] ?? 70) ?>" required>
                            </div>
                        </div>
                        <div class="field">
                            <label>Описание теста</label>
                            <textarea name="test_description" rows="2"><?= htmlspecialchars($test['desc_test'] ?? '') ?></textarea>
                        </div>
                        <div class="three fields">
                            <div class="field">
                                <label>Максимальное количество попыток</label>
                                <input type="number" name="max_attempts" min="1" max="100" value="<?= (int)($test['max_attempts'] ?? 3) ?>" required>
                            </div>
                            <div class="field">
                                <label>Интервал между попытками (минуты)</label>
                                <input type="number" name="time_between_attempts" min="0" max="10080" value="<?= (int)($test['time_between_attempts'] ?? 0) ?>">
                                <div class="ui mini label">0 = без ограничений</div>
                            </div>
                            <div class="field">
                                <div class="ui segment">
                                    <div class="field">
                                        <div class="ui checkbox">
                                            <input type="checkbox" name="show_results" <?= ($test['show_results_after_completion'] ?? true) ? 'checked' : '' ?>>
                                            <label>Показывать результаты после прохождения</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="ui checkbox">
                                            <input type="checkbox" name="practice_mode" <?= ($test['practice_mode'] ?? false) ? 'checked' : '' ?>>
                                            <label>Режим практики (без ограничения попыток, не влияет на прогресс)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ui divider"></div>
                        <h4 class="ui header">Шкала оценок</h4>
                        <div class="ui info message">
                            <p>Добавьте диапазоны оценок для теста. Уровни должны полностью покрывать диапазон от 0 до 100% без пересечений.</p>
                        </div>
                        
                        <div id="grade-levels-container">
                            <?php if (empty($grade_levels)): ?>
                                <!-- Стандартные уровни если ещё не заданы -->
                                <div class="grade-level four fields">
                                    <div class="field">
                                        <label>Минимум (%)</label>
                                        <input type="number" name="grade_levels[0][min]" min="0" max="100" value="0" required>
                                    </div>
                                    <div class="field">
                                        <label>Максимум (%)</label>
                                        <input type="number" name="grade_levels[0][max]" min="1" max="100" value="59" required>
                                    </div>
                                    <div class="field">
                                        <label>Название уровня</label>
                                        <input type="text" name="grade_levels[0][name]" value="Не пройдено" required>
                                    </div>
                                    <div class="field">
                                        <label>Цвет</label>
                                        <input type="color" name="grade_levels[0][color]" value="#FF0000">
                                    </div>
                                </div>
                                <div class="grade-level four fields">
                                    <div class="field">
                                        <label>Минимум (%)</label>
                                        <input type="number" name="grade_levels[1][min]" min="0" max="100" value="60" required>
                                    </div>
                                    <div class="field">
                                        <label>Максимум (%)</label>
                                        <input type="number" name="grade_levels[1][max]" min="1" max="100" value="74" required>
                                    </div>
                                    <div class="field">
                                        <label>Название уровня</label>
                                        <input type="text" name="grade_levels[1][name]" value="Удовлетворительно" required>
                                    </div>
                                    <div class="field">
                                        <label>Цвет</label>
                                        <input type="color" name="grade_levels[1][color]" value="#FFA500">
                                    </div>
                                </div>
                                <div class="grade-level four fields">
                                    <div class="field">
                                        <label>Минимум (%)</label>
                                        <input type="number" name="grade_levels[2][min]" min="0" max="100" value="75" required>
                                    </div>
                                    <div class="field">
                                        <label>Максимум (%)</label>
                                        <input type="number" name="grade_levels[2][max]" min="1" max="100" value="89" required>
                                    </div>
                                    <div class="field">
                                        <label>Название уровня</label>
                                        <input type="text" name="grade_levels[2][name]" value="Хорошо" required>
                                    </div>
                                    <div class="field">
                                        <label>Цвет</label>
                                        <input type="color" name="grade_levels[2][color]" value="#2ECC40">
                                    </div>
                                </div>
                                <div class="grade-level four fields">
                                    <div class="field">
                                        <label>Минимум (%)</label>
                                        <input type="number" name="grade_levels[3][min]" min="0" max="100" value="90" required>
                                    </div>
                                    <div class="field">
                                        <label>Максимум (%)</label>
                                        <input type="number" name="grade_levels[3][max]" min="1" max="100" value="100" required>
                                    </div>
                                    <div class="field">
                                        <label>Название уровня</label>
                                        <input type="text" name="grade_levels[3][name]" value="Отлично" required>
                                    </div>
                                    <div class="field">
                                        <label>Цвет</label>
                                        <input type="color" name="grade_levels[3][color]" value="#0E6EB8">
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Существующие уровни из базы данных -->
                                <?php foreach ($grade_levels as $index => $level): ?>
                                    <div class="grade-level four fields">
                                        <div class="field">
                                            <label>Минимум (%)</label>
                                            <input type="number" name="grade_levels[<?= $index ?>][min]" min="0" max="100" value="<?= (int)$level['min_percentage'] ?>" required>
                                        </div>
                                        <div class="field">
                                            <label>Максимум (%)</label>
                                            <input type="number" name="grade_levels[<?= $index ?>][max]" min="1" max="100" value="<?= (int)$level['max_percentage'] ?>" required>
                                        </div>
                                        <div class="field">
                                            <label>Название уровня</label>
                                            <input type="text" name="grade_levels[<?= $index ?>][name]" value="<?= htmlspecialchars($level['grade_name']) ?>" required>
                                        </div>
                                        <div class="field">
                                            <label>Цвет</label>
                                            <input type="color" name="grade_levels[<?= $index ?>][color]" value="<?= htmlspecialchars($level['grade_color']) ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ui hidden divider"></div>
                        <button type="button" class="ui basic button" onclick="addGradeLevel()"><i class="plus icon"></i> Добавить уровень оценки</button>
                        <div class="ui hidden divider"></div>
                        
                        <button type="submit" class="ui primary button">Сохранить настройки теста</button>
                    </form>
                    
                    <div class="ui divider"></div>
                    <h4>Управление попытками</h4>
                    <p>Предоставление дополнительных попыток для прохождения теста студентам</p>
                    <a href="manage_test_attempts.php?test_id=<?= $test_id ?>" class="ui teal button">
                        <i class="users icon"></i> Управление попытками студентов
                    </a>
                </div>
            </div>

            <!-- Форма добавления вопроса -->
            <div class="ui segment">
                <h3>Добавить новый вопрос</h3>
                <form method="post" class="ui form" id="addQuestionForm">
                    <input type="hidden" name="action" value="add_question">
                    <div class="field">
                        <label>Тип вопроса</label>
                        <select name="type_question" id="type_question" class="ui dropdown" required>
                            <option value="single">Один правильный ответ</option>
                            <option value="multi">Несколько правильных ответов</option>
                            <option value="match">Сопоставление</option>
                            <option value="code">Вопрос с кодом</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Текст вопроса</label>
                        <textarea name="question_text" rows="2" required></textarea>
                    </div>
                    <div id="options-block">
                        <!-- Здесь будут варианты в зависимости от типа -->
                    </div>
                    <button type="submit" class="ui primary button">Добавить вопрос</button>
                </form>
            </div>

            <!-- Список существующих вопросов -->
            <div class="ui segment">
                <h3>Вопросы теста</h3>
                <div class="ui styled fluid accordion">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="title">
                            <i class="dropdown icon"></i>
                            Вопрос <?= $index + 1 ?> (<?= htmlspecialchars($question['type_question']) ?>): <?= htmlspecialchars(substr($question['text_question'], 0, 50)) ?>...
                        </div>
                        <div class="content">
                            <form method="post" class="ui form">
                                <input type="hidden" name="action" value="edit_question">
                                <input type="hidden" name="question_id" value="<?= $question['id_question'] ?>">
                                <input type="hidden" name="type_question" value="<?= htmlspecialchars($question['type_question']) ?>">
                                <div class="field">
                                    <label>Текст вопроса</label>
                                    <textarea name="question_text" rows="2" required><?= htmlspecialchars($question['text_question']) ?></textarea>
                                </div>
                                <?php 
                                $options = get_question_options($pdo, $question['id_question']);
                                if ($question['type_question'] === 'single'): ?>
                                    <div class="fields">
                                        <div class="twelve wide field">
                                            <label>Варианты ответов</label>
                                            <div class="options-container">
                                                <?php foreach ($options as $opt_index => $option): ?>
                                                    <div class="field">
                                                        <input type="text" name="options[]" value="<?= htmlspecialchars($option['text_option']) ?>" required>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="ui basic button" onclick="addOptionToContainer(this)"><i class="plus icon"></i> Добавить вариант</button>
                                        </div>
                                        <div class="four wide field">
                                            <label>Правильный ответ</label>
                                            <select name="correct_option" class="ui dropdown" required>
                                                <?php foreach ($options as $opt_index => $option): ?>
                                                    <option value="<?= $opt_index ?>" <?= ((string)$opt_index === ($question['answer_question'] ?? '')) ? 'selected' : '' ?>>Вариант <?= $opt_index + 1 ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            
                                            <div class="ui hidden divider"></div>
                                            
                                            <div class="field">
                                                <label>Правильный ответ для генерации</label>
                                                <div class="ui fluid input">
                                                    <input type="text" class="correct-answer-text" placeholder="Введите правильный ответ">
                                                </div>
                                                <div class="fields" style="margin-top: 10px;">
                                                    <div class="four wide field">
                                                        <label>Количество вариантов</label>
                                                        <input type="number" class="num-options-input" min="1" max="10" value="3">
                                                    </div>
                                                    <div class="twelve wide field" style="display: flex; align-items: flex-end;">
                                                        <button type="button" class="ui teal button"
                                                                onclick="generateOptionsForExisting(this, <?= $question['id_question'] ?>)">
                                                            <i class="magic icon"></i> Сгенерировать варианты
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($question['type_question'] === 'multi'): ?>
                                    <div class="field">
                                        <label>Варианты ответов (отметьте правильные)</label>
                                        <div class="options-container">
                                            <?php foreach ($options as $opt_index => $option): ?>
                                                <div class="field">
                                                    <input type="text" name="options[]" value="<?= htmlspecialchars($option['text_option']) ?>" required>
                                                    <input type="checkbox" name="correct_options[]" value="<?= $opt_index ?>" <?= in_array((string)$opt_index, explode(',', $question['answer_question'] ?? '')) ? 'checked' : '' ?>> Правильный
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="ui basic button" onclick="addOptionMultiToContainer(this)"><i class="plus icon"></i> Добавить вариант</button>
                                    </div>
                                <?php elseif ($question['type_question'] === 'match'): ?>
                                    <div class="field">
                                        <label>Пары для сопоставления (левая и правая части)</label>
                                        <div class="match-container">
                                            <?php foreach ($options as $opt_index => $option): 
                                                $pair = explode('||', $option['text_option']);
                                                $left = $pair[0] ?? '';
                                                $right = $pair[1] ?? '';
                                            ?>
                                                <div class="fields">
                                                    <div class="eight wide field">
                                                        <input type="text" name="match_left[]" value="<?= htmlspecialchars($left) ?>" required>
                                                    </div>
                                                    <div class="eight wide field">
                                                        <input type="text" name="match_right[]" value="<?= htmlspecialchars($right) ?>" required>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="ui basic button" onclick="addMatchPairToContainer(this)"><i class="plus icon"></i> Добавить пару</button>
                                    </div>
                                <?php elseif ($question['type_question'] === 'code'): ?>
                                    <div class="field">
                                        <label>Настройки задания с кодом</label>
                                        <?php
                                        // Get code task details
                                        $stmt = $pdo->prepare("
                                            SELECT * FROM code_tasks
                                            WHERE id_question = ?
                                        ");
                                        $stmt->execute([$question['id_question']]);
                                        $code_task = $stmt->fetch();
                                        ?>
                                        <div class="fields">
                                            <div class="eight wide field">
                                                <label>Язык программирования</label>
                                                <select name="code_language" class="ui dropdown">
                                                    <option value="php" <?= ($code_task && $code_task['language'] === 'php') ? 'selected' : '' ?>>PHP</option>
                                                    <option value="python" <?= ($code_task && $code_task['language'] === 'python') ? 'selected' : '' ?>>Python</option>
                                                    <option value="cpp" <?= ($code_task && $code_task['language'] === 'cpp') ? 'selected' : '' ?>>C++</option>
                                                </select>
                                            </div>
                                            <div class="eight wide field">
                                                <label>Сложность задачи</label>
                                                <select name="code_difficulty" class="ui dropdown">
                                                    <option value="easy">Легкая</option>
                                                    <option value="medium" selected>Средняя</option>
                                                    <option value="hard">Сложная</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label>Шаблон кода</label>
                                            <textarea name="code_template" id="code_template" rows="10"></textarea>
                                        </div>
                                   
                                        <div class="field">
                                            <label>Ожидаемый вывод</label>
                                            <textarea name="code_output" id="code_output" rows="5" required></textarea>
                                        </div>
                                        <div class="field">
                                            <label>Таймаут выполнения (секунды)</label>
                                            <input type="number" name="code_timeout" id="code_timeout" value="5" min="1" max="30">
                                        </div>
                                        <button type="button" class="ui primary button" onclick="generateCodeTemplate()">
                                            <i class="magic icon"></i> Сгенерировать шаблон и вывод
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <div class="ui buttons">
                                    <button type="submit" class="ui primary button">Сохранить изменения</button>
                                    <button type="submit" class="ui negative button" onclick="if(!confirm('Вы уверены, что хотите удалить этот вопрос?')) return false; this.form.action.value='delete_question';">Удалить вопрос</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.accordion').accordion();
    $('.ui.dropdown').dropdown();
});

// JS для динамического отображения вариантов
function renderOptions(type) {
    const block = document.getElementById('options-block');
    if (type === 'single') {
        block.innerHTML = `
            <div class="field">
                <label>Правильный ответ для генерации</label>
                <div class="ui fluid input">
                    <input type="text" id="correct_answer_text" placeholder="Введите правильный ответ для генерации вариантов">
                </div>
                <div class="fields" style="margin-top: 10px;">
                    <div class="four wide field">
                        <label>Количество вариантов</label>
                        <input type="number" id="num_options" min="1" max="10" value="3">
                    </div>
                    <div class="twelve wide field" style="display: flex; align-items: flex-end;">
                        <button type="button" class="ui teal button" onclick="generateOptions()">
                            <i class="magic icon"></i> Сгенерировать неправильные варианты
                        </button>
                    </div>
                </div>
            </div>
            <div class="fields">
                <div class="twelve wide field">
                    <label>Варианты ответов</label>
                    <div id="options-container">
                        <div class="field">
                            <input type="text" name="options[]" placeholder="Вариант ответа 1" required>
                        </div>
                        <div class="field">
                            <input type="text" name="options[]" placeholder="Вариант ответа 2" required>
                        </div>
                    </div>
                    <button type="button" class="ui basic button" onclick="addOption()">
                        <i class="plus icon"></i> Добавить вариант
                    </button>
                </div>
                <div class="four wide field">
                    <label>Правильный ответ</label>
                    <select name="correct_option" class="ui dropdown" required>
                        <option value="0">Вариант 1</option>
                        <option value="1">Вариант 2</option>
                    </select>
                </div>
            </div>
        `;
    } else if (type === 'multi') {
        block.innerHTML = `
            <div class="field">
                <label>Правильные ответы для генерации</label>
                <div id="correct-answers-container">
                    <div class="ui action input" style="margin-bottom: 10px;">
                        <input type="text" placeholder="Введите правильный ответ" class="correct-answer-input" onkeypress="if(event.key==='Enter'){addCorrectAnswer(); return false;}">
                        <button type="button" class="ui green button" onclick="addCorrectAnswer()">
                            <i class="plus icon"></i> Добавить
                        </button>
                    </div>
                </div>
                <div id="correct-answers-list"></div>
                <div class="fields" style="margin-top: 10px;">
                    <div class="four wide field">
                        <label>Количество неправильных вариантов</label>
                        <input type="number" id="num_options" min="1" max="10" value="3">
                    </div>
                    <div class="twelve wide field" style="display: flex; align-items: flex-end;">
                        <button type="button" class="ui teal button" onclick="generateOptionsMulti()">
                            <i class="magic icon"></i> Сгенерировать неправильные варианты
                        </button>
                    </div>
                </div>
            </div>
            <div class="field">
                <label>Варианты ответов (правильные и неправильные)</label>
                <div id="options-container">
                    <div class="field">
                        <input type="text" name="options[]" placeholder="Вариант ответа 1" required>
                        <input type="checkbox" name="correct_options[]" value="0"> Правильный
                    </div>
                    <div class="field">
                        <input type="text" name="options[]" placeholder="Вариант ответа 2" required>
                        <input type="checkbox" name="correct_options[]" value="1"> Правильный
                    </div>
                </div>
                <button type="button" class="ui basic button" onclick="addOptionMulti()">
                    <i class="plus icon"></i> Добавить вариант
                </button>
            </div>
        `;
    } else if (type === 'match') {
        block.innerHTML = `
            <div class="field">
                <label>Пары для сопоставления (левая и правая части)</label>
                <div id="match-container">
                    <div class="fields">
                        <div class="eight wide field">
                            <input type="text" name="match_left[]" placeholder="Левая часть 1" required>
                        </div>
                        <div class="eight wide field">
                            <input type="text" name="match_right[]" placeholder="Правая часть 1" required>
                        </div>
                    </div>
                    <div class="fields">
                        <div class="eight wide field">
                            <input type="text" name="match_left[]" placeholder="Левая часть 2" required>
                        </div>
                        <div class="eight wide field">
                            <input type="text" name="match_right[]" placeholder="Правая часть 2" required>
                        </div>
                    </div>
                </div>
                <button type="button" class="ui basic button" onclick="addMatchPair()">
                    <i class="plus icon"></i> Добавить пару
                </button>
            </div>
        `;
    } else if (type === 'code') {
        block.innerHTML = `
            <div class="fields">
                <div class="eight wide field">
                    <label>Язык программирования</label>
                    <select name="code_language" id="code_language" class="ui dropdown">
                        <option value="php">PHP</option>
                        <option value="python">Python</option>
                        <option value="cpp">C++</option>
                    </select>
                </div>
                <div class="eight wide field">
                    <label>Сложность задачи</label>
                    <select name="code_difficulty" id="code_difficulty" class="ui dropdown">
                        <option value="easy">Легкая</option>
                        <option value="medium" selected>Средняя</option>
                        <option value="hard">Сложная</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Шаблон кода</label>
                <textarea name="code_template" id="code_template" rows="10"></textarea>
            </div>
            <div class="field">
                <label>Входные данные (опционально)</label>
                <textarea name="code_input" id="code_input" rows="2"></textarea>
            </div>
            <div class="field">
                <label>Ожидаемый вывод</label>
                <textarea name="code_output" id="code_output" rows="5" required></textarea>
            </div>
            <div class="field">
                <label>Таймаут выполнения (секунды)</label>
                <input type="number" name="code_timeout" id="code_timeout" value="5" min="1" max="30">
            </div>
            <button type="button" class="ui primary button" onclick="generateCodeTemplate()">
                <i class="magic icon"></i> Сгенерировать шаблон и вывод
            </button>`;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    renderOptions('single');
    document.getElementById('type_question').addEventListener('change', function() {
        renderOptions(this.value);
    });
});
function addOption() {
    const container = document.getElementById('options-container');
    const optionCount = container.children.length;
    const newField = document.createElement('div');
    newField.className = 'field';
    newField.innerHTML = `<input type="text" name="options[]" placeholder="Вариант ответа ${optionCount + 1}" required>`;
    container.appendChild(newField);
    updateCorrectOptions();
}
function addOptionMulti() {
    const container = document.getElementById('options-container');
    const optionCount = container.children.length;
    const newField = document.createElement('div');
    newField.className = 'field';
    newField.innerHTML = `<input type="text" name="options[]" placeholder="Вариант ответа ${optionCount + 1}" required> <input type="checkbox" name="correct_options[]" value="${optionCount}"> Правильный`;
    container.appendChild(newField);
}

// Глобальный массив для хранения правильных ответов
let correctAnswers = [];

function addCorrectAnswer() {
    const input = document.querySelector('.correct-answer-input');
    const answer = input.value.trim();
    
    if (answer && !correctAnswers.includes(answer)) {
        correctAnswers.push(answer);
        input.value = '';
        updateCorrectAnswersList();
    } else if (correctAnswers.includes(answer)) {
        alert('Этот ответ уже добавлен');
    }
}

function removeCorrectAnswer(index) {
    correctAnswers.splice(index, 1);
    updateCorrectAnswersList();
}

function updateCorrectAnswersList() {
    const listContainer = document.getElementById('correct-answers-list');
    if (correctAnswers.length === 0) {
        listContainer.innerHTML = '';
        return;
    }
    
    let html = '<div class="ui segments" style="margin-top: 10px;">';
    correctAnswers.forEach((answer, index) => {
        html += `
            <div class="ui segment">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><strong>Правильный ответ ${index + 1}:</strong> ${answer}</span>
                    <button type="button" class="ui red mini button" onclick="removeCorrectAnswer(${index})">
                        <i class="trash icon"></i> Удалить
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    listContainer.innerHTML = html;
}
function addMatchPair() {
    const container = document.getElementById('match-container');
    const pairCount = container.children.length + 1;
    const newFields = document.createElement('div');
    newFields.className = 'fields';
    newFields.innerHTML = `<div class="eight wide field"><input type="text" name="match_left[]" placeholder="Левая часть ${pairCount}" required></div><div class="eight wide field"><input type="text" name="match_right[]" placeholder="Правая часть ${pairCount}" required></div>`;
    container.appendChild(newFields);
}
function updateCorrectOptions() {
    const container = document.getElementById('options-container');
    const select = document.querySelector('select[name="correct_option"]');
    const optionCount = container.children.length;
    select.innerHTML = '';
    for (let i = 0; i < optionCount; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `Вариант ${i + 1}`;
        select.appendChild(option);
    }
}

// Функция для генерации неправильных вариантов для множественного выбора
function generateOptionsMulti() {
    const questionText = document.querySelector('textarea[name="question_text"]').value;
    const questionType = 'multi';
    const numOptions = document.getElementById('num_options')?.value || 3;
    
    if (!questionText) {
        alert('Пожалуйста, введите текст вопроса');
        return;
    }
    
    if (correctAnswers.length === 0) {
        alert('Пожалуйста, добавьте хотя бы один правильный ответ');
        return;
    }
    
    // Показываем индикатор загрузки
    const container = document.getElementById('options-container');
    container.innerHTML = '<div class="ui active inline loader"></div> Генерация неправильных вариантов...';
    
    // Отправляем запрос к API
    fetch('generate_options.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            question: questionText,
            correct_answers: correctAnswers, // Отправляем массив правильных ответов
            num_options: parseInt(numOptions),
            question_type: questionType
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Ошибка сервера (${response.status}): ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Очищаем контейнер
            container.innerHTML = '';
            
            // Добавляем все правильные ответы
            correctAnswers.forEach((answer, index) => {
                const correctField = document.createElement('div');
                correctField.className = 'field';
                correctField.innerHTML = `
                    <input type="text" name="options[]" value="${answer}" required>
                    <input type="checkbox" name="correct_options[]" value="${index}" checked> Правильный
                `;
                container.appendChild(correctField);
            });
            
            // Добавляем сгенерированные неправильные варианты
            data.options.forEach((option, index) => {
                const newField = document.createElement('div');
                newField.className = 'field';
                newField.innerHTML = `
                    <input type="text" name="options[]" value="${option}" required>
                    <input type="checkbox" name="correct_options[]" value="${correctAnswers.length + index}"> Правильный
                `;
                container.appendChild(newField);
            });
        } else {
            alert('Ошибка при генерации вариантов: ' + (data.error || 'Неизвестная ошибка'));
            // Восстанавливаем исходное состояние
            renderOptions('multi');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при генерации вариантов: ' + error.message);
        // Восстанавливаем исходное состояние
        renderOptions('multi');
    });
}

// Функция для генерации вариантов ответов через API
function generateOptions() {
    const questionText = document.querySelector('textarea[name="question_text"]').value;
    const correctAnswer = document.getElementById('correct_answer_text').value;
    const questionType = document.getElementById('type_question').value;
    const numOptions = document.getElementById('num_options')?.value || 3;
    
    if (!questionText || !correctAnswer) {
        alert('Пожалуйста, введите текст вопроса и правильный ответ');
        return;
    }
    
    // Показываем индикатор загрузки
    let container;
    if (questionType === 'match') {
        container = document.getElementById('match-container');
    } else {
        container = document.getElementById('options-container');
    }
    container.innerHTML = '<div class="ui active inline loader"></div> Генерация вариантов...';
    
    // Отправляем запрос к API
    fetch('generate_options.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            question: questionText,
            correct_answer: correctAnswer,
            num_options: parseInt(numOptions),
            question_type: questionType
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Ошибка сервера (${response.status}): ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Очищаем контейнер
            container.innerHTML = '';
            
            if (questionType === 'single') {
                // Добавляем правильный ответ как первый вариант
                const correctField = document.createElement('div');
                correctField.className = 'field';
                correctField.innerHTML = `<input type="text" name="options[]" value="${correctAnswer}" required>`;
                container.appendChild(correctField);
                
                // Добавляем сгенерированные неправильные варианты
                data.options.forEach(option => {
                    const newField = document.createElement('div');
                    newField.className = 'field';
                    newField.innerHTML = `<input type="text" name="options[]" value="${option}" required>`;
                    container.appendChild(newField);
                });
                
                // Обновляем выпадающий список правильных ответов
                updateCorrectOptions();
                
                // Устанавливаем первый вариант (правильный ответ) как выбранный
                const select = document.querySelector('select[name="correct_option"]');
                select.value = 0;
            } 
            else if (questionType === 'multi') {
                // Добавляем правильный ответ как первый вариант и отмечаем его
                const correctField = document.createElement('div');
                correctField.className = 'field';
                correctField.innerHTML = `
                    <input type="text" name="options[]" value="${correctAnswer}" required>
                    <input type="checkbox" name="correct_options[]" value="0" checked> Правильный
                `;
                container.appendChild(correctField);
                
                // Добавляем сгенерированные варианты
                data.options.forEach((option, index) => {
                    const newField = document.createElement('div');
                    newField.className = 'field';
                    newField.innerHTML = `
                        <input type="text" name="options[]" value="${option}" required>
                        <input type="checkbox" name="correct_options[]" value="${index + 1}"> Правильный
                    `;
                    container.appendChild(newField);
                });
            } 
            else if (questionType === 'match') {
                // Обрабатываем пары для сопоставления
                // Добавляем исходную пару
                const parts = correctAnswer.split('||');
                if (parts.length === 2) {
                    const pairField = document.createElement('div');
                    pairField.className = 'fields';
                    pairField.innerHTML = `
                        <div class="eight wide field">
                            <input type="text" name="match_left[]" value="${parts[0]}" required>
                        </div>
                        <div class="eight wide field">
                            <input type="text" name="match_right[]" value="${parts[1]}" required>
                        </div>
                    `;
                    container.appendChild(pairField);
                }
                
                // Добавляем сгенерированные пары
                data.options.forEach(option => {
                    const pairParts = option.split('||');
                    if (pairParts.length === 2) {
                        const newField = document.createElement('div');
                        newField.className = 'fields';
                        newField.innerHTML = `
                            <div class="eight wide field">
                                <input type="text" name="match_left[]" value="${pairParts[0]}" required>
                            </div>
                            <div class="eight wide field">
                                <input type="text" name="match_right[]" value="${pairParts[1]}" required>
                            </div>
                        `;
                        container.appendChild(newField);
                    }
                });
            }
        } else {
            alert('Ошибка при генерации вариантов: ' + (data.error || 'Неизвестная ошибка'));
            // Восстанавливаем исходное состояние
            renderOptions(questionType);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при генерации вариантов: ' + error.message);
        // Восстанавливаем исходное состояние
        renderOptions(questionType);
    });
}

// Функция для генерации вариантов для существующего вопроса
function generateOptionsForExisting(button, questionId) {
    const form = button.closest('form');
    const questionText = form.querySelector('textarea[name="question_text"]').value;
    const correctAnswerInput = button.parentElement.querySelector('.correct-answer-text');
    const correctAnswer = correctAnswerInput.value;
    const questionType = form.querySelector('input[name="type_question"]').value;
    const numOptionsInput = button.parentElement.querySelector('.num-options-input');
    const numOptions = numOptionsInput ? parseInt(numOptionsInput.value) : 3;
    
    if (!questionText || !correctAnswer) {
        alert('Пожалуйста, введите текст вопроса и правильный ответ');
        return;
    }
    
    // Показываем индикатор загрузки
    const container = form.querySelector('.options-container');
    container.innerHTML = '<div class="ui active inline loader"></div> Генерация вариантов...';
    
    // Отправляем запрос к API
    fetch('generate_options.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            question: questionText,
            correct_answer: correctAnswer,
            num_options: numOptions,
            question_type: questionType
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Ошибка сервера (${response.status}): ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Очищаем контейнер
            container.innerHTML = '';
            
            // Для типа single
            if (questionType === 'single') {
                // Добавляем правильный ответ как первый вариант
                const correctField = document.createElement('div');
                correctField.className = 'field';
                correctField.innerHTML = `<input type="text" name="options[]" value="${correctAnswer}" required>`;
                container.appendChild(correctField);
                
                // Добавляем сгенерированные неправильные варианты
                data.options.forEach(option => {
                    const newField = document.createElement('div');
                    newField.className = 'field';
                    newField.innerHTML = `<input type="text" name="options[]" value="${option}" required>`;
                    container.appendChild(newField);
                });
                
                // Обновляем выпадающий список правильных ответов
                const select = form.querySelector('select[name="correct_option"]');
                select.innerHTML = '';
                const optionCount = container.children.length;
                for (let i = 0; i < optionCount; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Вариант ${i + 1}`;
                    select.appendChild(option);
                }
                
                // Устанавливаем первый вариант (правильный ответ) как выбранный
                select.value = 0;
            }
        } else {
            alert('Ошибка при генерации вариантов: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при генерации вариантов: ' + error.message);
        
        // Восстанавливаем контейнер
        container.innerHTML = '<div class="ui negative message">Произошла ошибка. Попробуйте еще раз.</div>';
    });
}

// Функция для добавления варианта в существующий контейнер
function addOptionToContainer(button) {
    const container = button.parentElement.querySelector('.options-container');
    const optionCount = container.children.length;
    const newField = document.createElement('div');
    newField.className = 'field';
    newField.innerHTML = `<input type="text" name="options[]" placeholder="Вариант ответа ${optionCount + 1}" required>`;
    container.appendChild(newField);
    
    // Обновляем выпадающий список правильных ответов
    const form = button.closest('form');
    const select = form.querySelector('select[name="correct_option"]');
    const option = document.createElement('option');
    option.value = optionCount;
    option.textContent = `Вариант ${optionCount + 1}`;
    select.appendChild(option);
}

// Функция для добавления варианта в существующий контейнер для multi-option
function addOptionMultiToContainer(button) {
    const container = button.parentElement.querySelector('.options-container');
    const optionCount = container.children.length;
    const newField = document.createElement('div');
    newField.className = 'field';
    newField.innerHTML = `<input type="text" name="options[]" placeholder="Вариант ответа ${optionCount + 1}" required> <input type="checkbox" name="correct_options[]" value="${optionCount}"> Правильный`;
    container.appendChild(newField);
}

// Функция для добавления пары в существующий контейнер для match
function addMatchPairToContainer(button) {
    const container = button.parentElement.querySelector('.match-container');
    const pairCount = container.children.length + 1;
    const newFields = document.createElement('div');
    newFields.className = 'fields';
    newFields.innerHTML = `<div class="eight wide field"><input type="text" name="match_left[]" placeholder="Левая часть ${pairCount}" required></div><div class="eight wide field"><input type="text" name="match_right[]" placeholder="Правая часть ${pairCount}" required></div>`;
    container.appendChild(newFields);
}

// Функция для генерации шаблона и вывода кода
function generateCodeTemplate() {
    const questionText = document.querySelector('textarea[name="question_text"]').value;
    const language = document.getElementById('code_language').value;
    const difficulty = document.getElementById('code_difficulty').value;
    const inputExample = document.getElementById('code_input').value;
    
    if (!questionText) {
        alert('Пожалуйста, введите текст вопроса');
        return;
    }
    
    // Показываем индикатор загрузки
    document.getElementById('code_template').value = 'Генерация шаблона...';
    document.getElementById('code_output').value = 'Генерация ожидаемого вывода...';
    
    // Отправляем запрос к API
    fetch('generate_code_template.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            question: questionText,
            language: language,
            difficulty: difficulty,
            input_example: inputExample
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Ошибка сервера (${response.status}): ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('code_template').value = data.template;
            document.getElementById('code_output').value = data.output;
            
            // Сохраняем решение в скрытое поле для справки
            if (!document.getElementById('code_solution')) {
                const solutionField = document.createElement('input');
                solutionField.type = 'hidden';
                solutionField.id = 'code_solution';
                solutionField.name = 'code_solution';
                document.getElementById('code_template').parentNode.appendChild(solutionField);
            }
            document.getElementById('code_solution').value = data.solution;
            
            // Показываем сообщение об успехе
            alert('Шаблон кода и ожидаемый вывод успешно сгенерированы!');
        } else {
            alert('Ошибка при генерации кода: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при генерации кода: ' + error.message);
        document.getElementById('code_template').value = '';
        document.getElementById('code_output').value = '';
    });
}

// Функция для добавления нового уровня оценки
function addGradeLevel() {
    const container = document.getElementById('grade-levels-container');
    const levels = container.querySelectorAll('.grade-level');
    const newIndex = levels.length;
    
    const newLevel = document.createElement('div');
    newLevel.className = 'grade-level four fields';
    newLevel.innerHTML = `
        <div class="field">
            <label>Минимум (%)</label>
            <input type="number" name="grade_levels[${newIndex}][min]" min="0" max="100" value="0" required>
        </div>
        <div class="field">
            <label>Максимум (%)</label>
            <input type="number" name="grade_levels[${newIndex}][max]" min="1" max="100" value="100" required>
        </div>
        <div class="field">
            <label>Название уровня</label>
            <input type="text" name="grade_levels[${newIndex}][name]" value="Новый уровень" required>
        </div>
        <div class="field">
            <label>Цвет</label>
            <input type="color" name="grade_levels[${newIndex}][color]" value="#000000">
        </div>
        <button type="button" class="ui negative icon button remove-level"><i class="trash icon"></i></button>
    `;
    
    container.appendChild(newLevel);
    
    // Добавляем обработчик для кнопки удаления
    newLevel.querySelector('.remove-level').addEventListener('click', function() {
        container.removeChild(newLevel);
    });
    
    // Добавляем кнопки удаления к существующим уровням, если их еще нет
    levels.forEach(level => {
        if (!level.querySelector('.remove-level')) {
            const removeBtn = document.createElement('button');
            removeBtn.className = 'ui negative icon button remove-level';
            removeBtn.innerHTML = '<i class="trash icon"></i>';
            removeBtn.addEventListener('click', function() {
                container.removeChild(level);
            });
            level.appendChild(removeBtn);
        }
    });
}

// Инициализация чекбоксов
$(document).ready(function() {
    $('.ui.checkbox').checkbox();
    
    // Инициализируем кнопки удаления для существующих уровней оценок
    const container = document.getElementById('grade-levels-container');
    const levels = container.querySelectorAll('.grade-level');
    
    if (levels.length > 1) {  // Оставляем хотя бы один уровень
        levels.forEach(level => {
            if (!level.querySelector('.remove-level')) {
                const removeBtn = document.createElement('button');
                removeBtn.className = 'ui negative icon button remove-level';
                removeBtn.innerHTML = '<i class="trash icon"></i>';
                removeBtn.addEventListener('click', function() {
                    container.removeChild(level);
                });
                level.appendChild(removeBtn);
            }
        });
    }
});
</script>

</body>
</html> 