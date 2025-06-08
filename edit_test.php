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
    
    // Обработка POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            // Добавление нового вопроса
            if ($_POST['action'] === 'add_question') {
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
            // Редактирование настроек теста
            elseif ($_POST['action'] === 'edit_test_settings') {
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
                        
                        // Проверяем, есть ли уже настроенные уровни оценок для этого теста
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_grade_levels WHERE id_test = ?");
                        $stmt->execute([$test_id]);
                        $grade_levels_count = $stmt->fetchColumn();
                        
                        // Если уровней оценок еще нет, создаем их по умолчанию
                        if ($grade_levels_count == 0) {
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
                        }
                        
                        $success = 'Настройки теста успешно обновлены';
                        
                        // Обновляем информацию о тесте
                        $stmt = $pdo->prepare("
                            SELECT t.*, s.id_step, s.number_steps, l.id_lesson, l.name_lesson, c.id_course, c.name_course
                            FROM Tests t
                            JOIN Steps s ON t.id_step = s.id_step
                            JOIN lessons l ON s.id_lesson = l.id_lesson
                            JOIN course c ON l.id_course = c.id_course
                            WHERE t.id_test = ?
                        ");
                        $stmt->execute([$test_id]);
                        $test = $stmt->fetch();
                        
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                }
            }
            // Добавление уровня оценки
            elseif ($_POST['action'] === 'add_grade_level') {
                $min_percentage = (int)$_POST['min_percentage'];
                $max_percentage = (int)$_POST['max_percentage'];
                $grade_name = trim($_POST['grade_name']);
                $grade_color = trim($_POST['grade_color']);
                
                if (empty($grade_name) || $min_percentage >= $max_percentage) {
                    $error = 'Проверьте данные уровня оценки';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO test_grade_levels (id_test, min_percentage, max_percentage, grade_name, grade_color)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$test_id, $min_percentage, $max_percentage, $grade_name, $grade_color]);
                        
                        $success = 'Уровень оценки успешно добавлен';
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                }
            }
            // Удаление уровня оценки
            elseif ($_POST['action'] === 'delete_grade_level') {
                $level_id = (int)$_POST['level_id'];
                
                try {
                    $stmt = $pdo->prepare("
                        DELETE FROM test_grade_levels
                        WHERE id_level = ? AND id_test = ?
                    ");
                    $stmt->execute([$level_id, $test_id]);
                    
                    $success = 'Уровень оценки успешно удален';
                } catch (Exception $e) {
                    $error = $e->getMessage();
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

            <!-- Редактирование настроек теста -->
            <div class="ui segment">
                <h3>Настройки теста</h3>
                <form method="post" class="ui form">
                    <input type="hidden" name="action" value="edit_test_settings">
                    
                    <div class="field">
                        <label>Название теста</label>
                        <input type="text" name="test_name" value="<?= htmlspecialchars($test['name_test']) ?>" required>
                    </div>
                    
                    <div class="field">
                        <label>Описание теста</label>
                        <textarea name="test_description" rows="2"><?= htmlspecialchars($test['desc_test'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="two fields">
                        <div class="field">
                            <label>Проходной балл (в процентах)</label>
                            <input type="number" name="passing_percentage" min="0" max="100" value="<?= $test['passing_percentage'] ?? 70 ?>" required>
                        </div>
                        
                        <div class="field">
                            <label>Максимальное количество попыток</label>
                            <input type="number" name="max_attempts" min="1" value="<?= $test['max_attempts'] ?? 3 ?>" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label>Время между попытками (в минутах)</label>
                        <input type="number" name="time_between_attempts" min="0" value="<?= $test['time_between_attempts'] ?? 0 ?>">
                        <small>0 = без ожидания между попытками</small>
                    </div>
                    
                    <div class="two fields">
                        <div class="field">
                            <div class="ui checkbox">
                                <input type="checkbox" name="show_results" value="1" <?= ($test['show_results_after_completion'] == 'true' || $test['show_results_after_completion'] === true || $test['show_results_after_completion'] == 't' || $test['show_results_after_completion'] == '1') ? 'checked' : '' ?>>
                                <label>Показывать результаты сразу после завершения</label>
                            </div>
                        </div>
                        
                        <div class="field">
                            <div class="ui checkbox">
                                <input type="checkbox" name="practice_mode" value="1" <?= ($test['practice_mode'] == 'true' || $test['practice_mode'] === true || $test['practice_mode'] == 't' || $test['practice_mode'] == '1') ? 'checked' : '' ?>>
                                <label>Режим практики (результаты не влияют на прогресс)</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="ui primary button">Сохранить настройки</button>
                </form>
                
                <div class="ui divider"></div>
                
                <a href="manage_student_test_settings.php?test_id=<?= $test_id ?>" class="ui blue button">
                    <i class="users icon"></i> Управление настройками для студентов
                </a>
            </div>
            
            <div class="ui segment">
                <h3>Уровни оценок</h3>
                
                <?php
                // Получаем уровни оценок для этого теста
                $stmt = $pdo->prepare("
                    SELECT * FROM test_grade_levels
                    WHERE id_test = ?
                    ORDER BY min_percentage
                ");
                $stmt->execute([$test_id]);
                $grade_levels = $stmt->fetchAll();
                ?>
                
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th>Диапазон</th>
                            <th>Название</th>
                            <th>Цвет</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grade_levels as $level): ?>
                        <tr>
                            <td><?= $level['min_percentage'] ?>% - <?= $level['max_percentage'] ?>%</td>
                            <td><?= htmlspecialchars($level['grade_name']) ?></td>
                            <td>
                                <div style="width: 20px; height: 20px; background-color: <?= htmlspecialchars($level['grade_color']) ?>"></div>
                            </td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_grade_level">
                                    <input type="hidden" name="level_id" value="<?= $level['id_level'] ?>">
                                    <button type="submit" class="ui tiny red button" onclick="return confirm('Вы уверены?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($grade_levels)): ?>
                        <tr>
                            <td colspan="4" class="center aligned">Нет настроенных уровней оценок</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <form method="post" class="ui form">
                    <input type="hidden" name="action" value="add_grade_level">
                    
                    <div class="three fields">
                        <div class="field">
                            <label>Минимальный %</label>
                            <input type="number" name="min_percentage" min="0" max="100" required>
                        </div>
                        <div class="field">
                            <label>Максимальный %</label>
                            <input type="number" name="max_percentage" min="0" max="100" required>
                        </div>
                        <div class="field">
                            <label>Название</label>
                            <input type="text" name="grade_name" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label>Цвет</label>
                        <input type="color" name="grade_color" value="#4CAF50">
                    </div>
                    
                    <button type="submit" class="ui primary button">Добавить уровень оценки</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    $('.ui.checkbox').checkbox();
    $('.ui.dropdown').dropdown();
    $('.ui.accordion').accordion();
    
    // Остальной код JavaScript
    // ...
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
                <label>Правильный ответ для генерации</label>
                <div class="ui fluid input">
                    <input type="text" id="correct_answer_text" placeholder="Введите один из правильных ответов для генерации вариантов">
                </div>
                <div class="fields" style="margin-top: 10px;">
                    <div class="four wide field">
                        <label>Количество вариантов</label>
                        <input type="number" id="num_options" min="1" max="10" value="3">
                    </div>
                    <div class="twelve wide field" style="display: flex; align-items: flex-end;">
                        <button type="button" class="ui teal button" onclick="generateOptions()">
                            <i class="magic icon"></i> Сгенерировать варианты
                        </button>
                    </div>
                </div>
            </div>
            <div class="field">
                <label>Варианты ответов (отметьте правильные)</label>
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
</script>

</body>
</html> 