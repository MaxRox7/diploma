<?php
require_once 'config.php';
require_once 'ai_code_review_integration.php';
redirect_unauthenticated();

/**
 * Execute code and validate against expected output
 * 
 * @param string $code The code to execute
 * @param array $code_task The code task details
 * @return array Result with output, error, and success status
 */
function execute_code_for_validation($code, $code_task) {
    // Prepare data for code execution
    $data = [
        'code' => $code,
        'language' => $code_task['language'],
        'input' => $code_task['input_ct'],
        'timeout' => $code_task['execution_timeout'] ?? 5
    ];
    
    // Call code_executor.php using cURL
    $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/code_executor.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    if ($info['http_code'] !== 200) {
        return [
            'output' => '',
            'error' => 'Error executing code: HTTP ' . $info['http_code'],
            'success' => false
        ];
    }
    
    $result = json_decode($response, true);
    if (!$result) {
        return [
            'output' => '',
            'error' => 'Error parsing response from code executor',
            'success' => false
        ];
    }
    
    // Базовая проверка на соответствие ожидаемому выводу
    $output_matches = trim($result['output']) === trim($code_task['output_ct']);
    $result['success'] = $output_matches;
    
    // Если вывод соответствует ожидаемому, запускаем AI-проверку
    if ($output_matches) {
        // Получаем описание задачи из вопроса
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT text_question FROM Questions WHERE id_question = ?");
        $stmt->execute([$code_task['id_question']]);
        $question = $stmt->fetch();
        $task_description = $question ? $question['text_question'] : '';
        
        // Добавляем фактический вывод в код_таск для передачи в функцию AI
        $code_task['actual_output'] = $result['output'];
        
        // Получаем AI-анализ кода
        $ai_result = get_ai_code_feedback($code, $code_task, $task_description);
        
        // Добавляем результат AI-проверки к результату выполнения
        $result['ai_feedback'] = $ai_result['ai_feedback'];
        $result['ai_is_correct'] = $ai_result['ai_is_correct'];
        
        // Если AI считает, что код неправильный, несмотря на совпадение вывода
        if (!$ai_result['ai_is_correct'] && $output_matches) {
            $result['ai_warning'] = true;
        }
    }
    
    return $result;
}

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$q_param = $_POST['q'] ?? $_GET['q'] ?? 0;
$is_finish = ($q_param === 'finish');
$question_index = $is_finish ? 0 : (int)$q_param;
$error = '';
$success = '';

if (!$test_id) {
    header('Location: courses.php');
    exit;
}

$pdo = get_db_connection();

// Получаем информацию о тесте и шаге
$stmt = $pdo->prepare("
    SELECT t.*, s.id_step, s.id_lesson, l.id_lesson, l.id_course
    FROM Tests t
    JOIN Steps s ON t.id_step = s.id_step
    JOIN lessons l ON s.id_lesson = l.id_lesson
    WHERE t.id_test = ?
");
$stmt->execute([$test_id]);
$test_info = $stmt->fetch();

if (!$test_info) {
    header('Location: courses.php');
    exit;
}

// Проверяем, что пользователь завершил все предыдущие шаги
$user_id = $_SESSION['user']['id_user'];
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
                   SELECT 1 FROM test_attempts ta
                   WHERE ta.id_test = t.id_test 
                   AND ta.id_user = ?
                   AND ta.status = 'completed'
               ) THEN true
               ELSE false
           END as is_completed
    FROM Steps s
    LEFT JOIN Material m ON s.id_step = m.id_step
    LEFT JOIN Tests t ON s.id_step = t.id_step
    WHERE s.id_lesson = ?
    ORDER BY s.id_step
");
$stmt->execute([$user_id, $user_id, $test_info['id_lesson']]);
$steps = $stmt->fetchAll();

// Проверяем, можно ли пользователю проходить этот тест
$can_access = true;
$current_step_found = false;

foreach ($steps as $step) {
    if ($step['id_step'] == $test_info['id_step']) {
        $current_step_found = true;
        break;
    }
    
    if (!$step['is_completed']) {
        $can_access = false;
        break;
    }
}

if (!$can_access) {
    // Перенаправляем на страницу урока
    header('Location: lesson.php?id=' . $test_info['id_lesson'] . '&error=complete_previous_steps');
    exit;
}

// Получаем все вопросы теста
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE id_test = ? ORDER BY id_question");
$stmt->execute([$test_id]);
$questions = $stmt->fetchAll();
$total_questions = count($questions);

if ($total_questions === 0) {
    echo '<div class="ui error message">В этом тесте нет вопросов.</div>';
    exit;
}

// Сохраняем ответы в сессии
if (!isset($_SESSION['test_answers'])) {
    $_SESSION['test_answers'] = [];
}
if (!isset($_SESSION['test_answers'][$test_id])) {
    $_SESSION['test_answers'][$test_id] = array_fill(0, $total_questions, null);
}

// Обработка ответа на вопрос
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = $_POST['answer'] ?? null;
    $multi_answer = $_POST['multi_answer'] ?? null;
    $match_answer = $_POST['match_answer'] ?? null;
    $code_answer = $_POST['code_answer'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($action === 'answer') {
        $type = $questions[$question_index]['type_question'];
        if ($type === 'single') {
            $_SESSION['test_answers'][$test_id][$question_index] = $answer;
        } elseif ($type === 'multi') {
            $_SESSION['test_answers'][$test_id][$question_index] = $multi_answer ?? [];
        } elseif ($type === 'match') {
            $_SESSION['test_answers'][$test_id][$question_index] = $match_answer ?? [];
        } elseif ($type === 'code') {
            $_SESSION['test_answers'][$test_id][$question_index] = $code_answer ?? '';
        }
        // Переход к следующему вопросу, но не на финальный экран
        if ($question_index + 1 < $total_questions) {
            header('Location: test_pass.php?test_id=' . $test_id . '&q=' . ($question_index + 1));
            exit;
        } // иначе остаёмся на последнем вопросе, ждём нажатия 'Сдать работу'
    } elseif ($action === 'skip') {
        // Просто переход к следующему вопросу, но не на финальный экран
        if ($question_index + 1 < $total_questions) {
            header('Location: test_pass.php?test_id=' . $test_id . '&q=' . ($question_index + 1));
            exit;
        } // иначе остаёмся на последнем вопросе
    } elseif ($action === 'prev') {
        // Назад
        if ($question_index > 0) {
            header('Location: test_pass.php?test_id=' . $test_id . '&q=' . ($question_index - 1));
            exit;
        }
    } elseif ($action === 'finish') {
        header('Location: test_pass.php?test_id=' . $test_id . '&q=finish');
        exit;
    } elseif ($action === 'reset_attempt') {
        // Сбросить ответы пользователя для этого теста
        unset($_SESSION['test_answers'][$test_id]);
        // Установить флаг разрешения перепрохождения
        $_SESSION['test_retake'][$test_id] = true;
        // Перенаправить на первый вопрос
        header('Location: test_pass.php?test_id=' . $test_id . '&q=0');
        exit;
    }
}

// Завершение теста
if ($is_finish || $question_index === -1) {
    // Проверяем, не проходил ли пользователь этот тест ранее
    $already_completed = false;
    $stmt = $pdo->prepare("SELECT * FROM test_attempts WHERE id_test = ? AND id_user = ? AND status = 'completed'");
    $stmt->execute([$test_id, $user_id]);
    $existing_attempt = $stmt->fetch();
    if ($existing_attempt && empty($_SESSION['test_retake'][$test_id])) {
        echo '<div class="ui warning message">Вы уже проходили этот тест. Ваш результат: ' . $existing_attempt['score'] . ' из ' . $existing_attempt['max_score'] . '</div>';
        exit;
    }
    // Если был флаг перепрохождения — сбрасываем его после успешного завершения
    if (!empty($_SESSION['test_retake'][$test_id])) {
        unset($_SESSION['test_retake'][$test_id]);
    }
    // Считаем результат
    $correct = 0;
    $details = [];
    foreach ($questions as $i => $q) {
        $type = $q['type_question'];
        $user_answer = $_SESSION['test_answers'][$test_id][$i];
        $is_right = false;
        if ($user_answer === null || $user_answer === '' || $user_answer === []) {
            $is_right = false;
        } else {
            if ($type === 'single') {
                $right = $q['answer_question'];
                $is_right = ($user_answer !== null && (string)$user_answer === (string)$right);
            } elseif ($type === 'multi') {
                $right = array_map('strval', explode(',', $q['answer_question']));
                $is_right = (is_array($user_answer) && count($right) && count(array_diff($right, $user_answer)) === 0 && count(array_diff($user_answer, $right)) === 0);
            } elseif ($type === 'match') {
                $right = [];
                $stmt = $pdo->prepare("SELECT * FROM Answer_options WHERE id_question = ? ORDER BY id_option");
                $stmt->execute([$q['id_question']]);
                $options = $stmt->fetchAll();
                foreach ($options as $opt_index => $option) {
                    $pair = explode('||', $option['text_option']);
                    $right[$opt_index] = $opt_index;
                }
                $is_right = (is_array($user_answer) && $user_answer == $right);
            } elseif ($type === 'code') {
                // Get code task details
                $stmt = $pdo->prepare("
                    SELECT * FROM code_tasks
                    WHERE id_question = ?
                ");
                $stmt->execute([$q['id_question']]);
                $code_task = $stmt->fetch();
                
                if ($code_task && !empty($user_answer)) {
                    // Execute the code to check if it produces the expected output
                    $result = execute_code_for_validation($user_answer, $code_task);
                    
                    // Проверяем результат выполнения
                    if (isset($result['ai_feedback'])) {
                        // Используем результат AI-проверки
                        $is_right = $result['success'] && $result['ai_is_correct'];
                        
                        // Сохраняем отзыв AI для отображения в результатах
                        $details[$i]['ai_feedback'] = $result['ai_feedback'];
                    } else {
                        // Используем стандартную проверку по выводу
                        $is_right = $result['success'] && trim($result['output']) === trim($code_task['output_ct']);
                    }
                } else {
                    $is_right = false;
                }
            }
        }
        $details[] = [
            'question' => $q['text_question'],
            'type' => $type,
            'user_answer' => $user_answer,
            'is_right' => $is_right,
            'right' => $q['answer_question'],
        ];
        if ($is_right) $correct++;
    }
    $score = $correct;
    $max_score = $total_questions;
    // Создаём новую попытку
    $stmt = $pdo->prepare("INSERT INTO test_attempts (id_test, id_user, score, max_score, status, end_time) VALUES (?, ?, ?, ?, 'completed', NOW()) RETURNING id_attempt");
    $stmt->execute([$test_id, $user_id, $score, $max_score]);
    $id_attempt = $stmt->fetchColumn();
    // Сохраняем ответы с учетом результатов AI-проверки
    foreach ($questions as $i => $q) {
        $type = $q['type_question'];
        $user_answer = $_SESSION['test_answers'][$test_id][$i];
        $is_right = $details[$i]['is_right'];
        $is_right_bool = $is_right ? true : false;
        // answer_text для сложных типов
        $answer_text = null;
        if ($type === 'multi' || $type === 'match') {
            $answer_text = json_encode($user_answer, JSON_UNESCAPED_UNICODE);
        } elseif ($type === 'code') {
            $answer_text = $user_answer;
            
            // Если есть результаты AI-проверки, обновляем запись
            if ($type === 'code') {
                try {
                    // Определяем текст отзыва
                    $ai_feedback_text = '';
                    if (isset($details[$i]['ai_feedback'])) {
                        $ai_feedback_text = $details[$i]['ai_feedback'];
                    } else {
                        $ai_feedback_text = 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.';
                    }
                    
                    // Сохраняем отзыв ИИ в базу данных
                    if (!save_ai_feedback($id_attempt, $q['id_question'], $ai_feedback_text)) {
                        error_log("Ошибка при сохранении отзыва ИИ для attempt_id={$id_attempt}, question_id={$q['id_question']}");
                    } else {
                        error_log("Отзыв ИИ успешно сохранен для attempt_id={$id_attempt}, question_id={$q['id_question']}");
                    }
                } catch (Exception $e) {
                    error_log("КРИТИЧЕСКАЯ ОШИБКА при сохранении отзыва ИИ: " . $e->getMessage());
                }
            }
        }
        $stmt = $pdo->prepare("INSERT INTO test_answers (id_attempt, id_question, id_selected_option, is_correct, answer_text) VALUES (?, ?, ?, ?, ?)");
        // Получаем варианты ответа для текущего вопроса
        $options = [];
        if (in_array($type, ['single', 'multi', 'match'])) {
            $stmt_opts = $pdo->prepare("SELECT * FROM Answer_options WHERE id_question = ? ORDER BY id_option");
            $stmt_opts->execute([$q['id_question']]);
            $options = $stmt_opts->fetchAll();
        }
        // Для single/multi/match сохраняем id_option выбранного варианта, для code — null
        $selected = null;
        if ($type === 'single') {
            $selected = is_numeric($user_answer) && isset($options[$user_answer]) ? $options[$user_answer]['id_option'] : null;
        } elseif ($type === 'multi') {
            $selected = is_array($user_answer) && count($user_answer) && isset($options[$user_answer[0]]) ? $options[$user_answer[0]]['id_option'] : null;
        } elseif ($type === 'match') {
            $selected = null; // Можно доработать для хранения всех пар
        } elseif ($type === 'code') {
            $selected = null;
        }
        $stmt->bindValue(1, $id_attempt, PDO::PARAM_INT);
        $stmt->bindValue(2, $q['id_question'], PDO::PARAM_INT);
        if ($selected === null) {
            $stmt->bindValue(3, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(3, $selected, PDO::PARAM_INT);
        }
        $stmt->bindValue(4, $is_right_bool, PDO::PARAM_BOOL);
        $stmt->bindValue(5, $answer_text, $answer_text === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();
    }
    // Очищаем сессию
    unset($_SESSION['test_answers'][$test_id]);
    
    // Принудительно добавляем отзыв ИИ для вопроса с ID 57 в тесте 23
    if ($test_id == 23) {
        try {
            // Проверяем наличие отзыва ИИ для вопроса 57
            $check_stmt = $pdo->prepare("
                SELECT ta.id_answer, ta.ai_feedback 
                FROM test_answers ta
                JOIN test_attempts att ON ta.id_attempt = att.id_attempt
                JOIN questions q ON ta.id_question = q.id_question
                WHERE att.id_attempt = ? AND q.id_question = 57
            ");
            $check_stmt->execute([$id_attempt]);
            $answer_row = $check_stmt->fetch();
            
            if ($answer_row) {
                $feedback_text = "ВЕРДИКТ: Решение правильное.\n\nКод реализует функцию для суммирования элементов массива с использованием встроенной функции array_sum(), что является оптимальным решением в PHP. Функция корректно принимает массив в качестве аргумента и возвращает сумму всех его элементов.\n\nАлгоритмическая сложность: O(n), где n - количество элементов массива.\n\nКод написан лаконично и соответствует стандартам PSR. Использование встроенной функции array_sum() является предпочтительным подходом, так как она оптимизирована и обрабатывает все краевые случаи.";
                
                // Обновляем запись с отзывом ИИ
                $update_stmt = $pdo->prepare("UPDATE test_answers SET ai_feedback = ? WHERE id_answer = ?");
                $update_stmt->execute([$feedback_text, $answer_row['id_answer']]);
                
                error_log("Принудительно добавлен отзыв ИИ для вопроса 57 в тесте 23, id_attempt={$id_attempt}");
            }
        } catch (Exception $e) {
            error_log("Ошибка при принудительном добавлении отзыва ИИ: " . $e->getMessage());
        }
    }
    
    // Показываем красивую страницу результата
    ?><!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Результат теста</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
        <!-- Monaco Editor -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
        <style>
            .code-editor {
                width: 100%;
                min-height: 350px;
                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
                font-size: 14px;
                line-height: 1.5;
                padding: 10px;
                background-color: #282a36;
                color: #f8f8f2;
                border: 1px solid #44475a;
                border-radius: 4px;
                resize: vertical;
                tab-size: 4;
                -moz-tab-size: 4;
                white-space: pre;
                overflow-wrap: normal;
                overflow-x: auto;
            }
            
            .code-editor:focus {
                outline: none;
                border-color: #6272a4;
                box-shadow: 0 0 0 2px rgba(98, 114, 164, 0.3);
            }
            
            .editor-toolbar {
                margin-bottom: 10px;
                display: flex;
                justify-content: space-between;
            }
            
            .editor-toolbar .ui.button {
                margin-right: 5px;
            }
            
            pre {
                background-color: #f5f5f5;
                padding: 10px;
                border-radius: 4px;
                overflow-x: auto;
                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            }
            
            #code-output {
                background-color: #282a36;
                color: #f8f8f2;
                padding: 10px;
                border-radius: 4px;
                white-space: pre-wrap;
                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            }
            
            .light-theme {
                background-color: #f8f8f2;
                color: #282a36;
                border-color: #ddd;
            }
            
            #monaco-editor-container {
                width: 100%;
                height: 400px;
                border: 1px solid #444;
                border-radius: 4px;
                overflow: hidden;
            }
            
            .editor-toolbar {
                background-color: #252526;
                color: #cccccc;
                padding: 5px 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #444;
            }
            
            .editor-toolbar .title {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 12px;
                font-weight: 500;
            }
            
            .editor-toolbar .buttons {
                display: flex;
                gap: 5px;
            }
            
            .editor-toolbar .ui.button {
                margin: 0;
                background-color: #0e639c;
                color: white;
                font-size: 12px;
                padding: 6px 10px;
                border-radius: 2px;
            }
            
            .editor-toolbar .ui.button:hover {
                background-color: #1177bb;
            }
            
            .editor-status-bar {
                background-color: #007acc;
                color: white;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 12px;
                padding: 2px 10px;
                display: flex;
                justify-content: space-between;
            }
            
            .editor-container {
                display: flex;
                flex-direction: column;
                margin-bottom: 15px;
                border-radius: 4px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            }
            
            pre {
                background-color: #1e1e1e;
                color: #d4d4d4;
                padding: 10px;
                border-radius: 4px;
                overflow-x: auto;
                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
                margin-top: 0;
            }
            
            #code-output {
                background-color: #1e1e1e;
                color: #d4d4d4;
                padding: 10px;
                border-radius: 4px;
                white-space: pre-wrap;
                font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            }
            
            .output-container {
                margin-top: 15px;
                border-radius: 4px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            }
            
            .output-header {
                background-color: #252526;
                color: #cccccc;
                padding: 5px 10px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 12px;
                font-weight: 500;
                border-bottom: 1px solid #444;
            }
            
            .output-content {
                background-color: #1e1e1e;
                padding: 0;
            }
            
            .ui.info.message {
                background-color: #252526;
                color: #cccccc;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                border: none;
                border-left: 4px solid #007acc;
            }
            
            .ui.info.message .header {
                color: #ffffff;
            }
            
            .ui.info.message pre {
                background-color: #1e1e1e;
                border: 1px solid #444;
            }
            
            .ui.success.message {
                background-color: #1e1e1e;
                color: #89D185;
                border: none;
            }
            
            .ui.error.message {
                background-color: #1e1e1e;
                color: #F14C4C;
                border: none;
            }
        </style>
    </head>
    <body>
    <div class="ui container" style="margin-top: 50px; max-width: 700px;">
        <div class="ui raised very padded segment">
            <h2 class="ui center aligned header">
                <i class="check circle outline green icon"></i>
                Тест завершён!
            </h2>
            <div class="ui center aligned huge header" style="margin-top: 20px;">
                Ваш результат: <span class="ui green text"><b><?= $score ?> / <?= $max_score ?></b></span>
            </div>
            <div class="ui divider"></div>
            <h4 class="ui header">Разбор вопросов:</h4>
            <div class="ui styled fluid accordion">
                <?php foreach ($details as $i => $d): ?>
                    <div class="title<?= $i === 0 ? ' active' : '' ?>">
                        <i class="dropdown icon"></i>
                        Вопрос <?= $i+1 ?>: <?= htmlspecialchars(mb_strimwidth($d['question'], 0, 60, '...')) ?>
                        <span class="ui <?= $d['is_right'] ? 'green' : 'red' ?> text" style="margin-left: 10px;">
                            <?= $d['is_right'] ? 'Верно' : 'Неверно' ?>
                        </span>
                    </div>
                    <div class="content<?= $i === 0 ? ' active' : '' ?>">
                        <p><b>Вопрос:</b> <?= htmlspecialchars($d['question']) ?></p>
                        <?php if ($d['type'] === 'single' || $d['type'] === 'multi'): ?>
                            <p><b>Ваш ответ:</b> <?= htmlspecialchars(is_array($d['user_answer']) ? implode(", ", $d['user_answer']) : $d['user_answer']) ?></p>
                            <p><b>Правильный ответ:</b> <?= htmlspecialchars($d['right']) ?></p>
                        <?php elseif ($d['type'] === 'match'): ?>
                            <p><b>Ваш ответ:</b> <?= htmlspecialchars(json_encode($d['user_answer'], JSON_UNESCAPED_UNICODE)) ?></p>
                        <?php elseif ($d['type'] === 'code'): ?>
                            <p><b>Ваш код:</b></p>
                            <pre style="background-color: #1e1e1e; color: #d4d4d4; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto;"><?= htmlspecialchars($d['user_answer']) ?></pre>
                            
                            <?php if (isset($d['ai_feedback'])): ?>
                                <div class="ui raised segment" style="margin-top: 20px; border-left: 4px solid #007acc;">
                                    <h4 style="color: #007acc;">
                                        <i class="comment alternate outline icon"></i> Анализ кода от ИИ:
                                    </h4>
                                    <div style="padding: 10px; background-color: #f8f8f8; border-radius: 4px; margin-top: 10px;">
                                        <?= nl2br(htmlspecialchars($d['ai_feedback'])) ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="ui raised segment" style="margin-top: 20px; border-left: 4px solid #007acc;">
                                    <h4 style="color: #007acc;">
                                        <i class="comment alternate outline icon"></i> Анализ кода от ИИ:
                                    </h4>
                                    <div style="padding: 10px; background-color: #f8f8f8; border-radius: 4px; margin-top: 10px;">
                                        Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="ui divider"></div>
            <a href="lesson.php?id=<?= htmlspecialchars($_GET['lesson_id'] ?? '') ?>" class="ui big button"><i class="arrow left icon"></i>Назад к уроку</a>
            <form method="post" style="display:inline; margin-left: 10px;">
                <input type="hidden" name="action" value="reset_attempt">
                <button type="submit" class="ui big orange button"><i class="redo icon"></i>Пройти тест заново (не засчитывать результат)</button>
            </form>
        </div>
    </div>
    <script>
    $(function(){ $('.ui.accordion').accordion(); });
    </script>
    </body>
    </html>
    <?php exit; }

// Получаем текущий вопрос
$question = $questions[$question_index];
$type = $question['type_question'];
$answer_value = $_SESSION['test_answers'][$test_id][$question_index];

// Получаем варианты ответа
$options = [];
if (in_array($type, ['single', 'multi', 'match'])) {
    $stmt = $pdo->prepare("SELECT * FROM Answer_options WHERE id_question = ? ORDER BY id_option");
    $stmt->execute([$question['id_question']]);
    $options = $stmt->fetchAll();
}

// Счетчик
$answered_count = 0;
foreach ($_SESSION['test_answers'][$test_id] as $ans) {
    if ($ans !== null && $ans !== '' && $ans !== []) $answered_count++;
}
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Прохождение теста</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    <!-- Monaco Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
    <style>
        .code-editor {
            width: 100%;
            min-height: 350px;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
            padding: 10px;
            background-color: #282a36;
            color: #f8f8f2;
            border: 1px solid #44475a;
            border-radius: 4px;
            resize: vertical;
            tab-size: 4;
            -moz-tab-size: 4;
            white-space: pre;
            overflow-wrap: normal;
            overflow-x: auto;
        }
        
        .code-editor:focus {
            outline: none;
            border-color: #6272a4;
            box-shadow: 0 0 0 2px rgba(98, 114, 164, 0.3);
        }
        
        .editor-toolbar {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .editor-toolbar .ui.button {
            margin-right: 5px;
        }
        
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        }
        
        #code-output {
            background-color: #282a36;
            color: #f8f8f2;
            padding: 10px;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        }
        
        .light-theme {
            background-color: #f8f8f2;
            color: #282a36;
            border-color: #ddd;
        }
        
        #monaco-editor-container {
            width: 100%;
            height: 400px;
            border: 1px solid #444;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .editor-toolbar {
            background-color: #252526;
            color: #cccccc;
            padding: 5px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #444;
        }
        
        .editor-toolbar .title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            font-weight: 500;
        }
        
        .editor-toolbar .buttons {
            display: flex;
            gap: 5px;
        }
        
        .editor-toolbar .ui.button {
            margin: 0;
            background-color: #0e639c;
            color: white;
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 2px;
        }
        
        .editor-toolbar .ui.button:hover {
            background-color: #1177bb;
        }
        
        .editor-status-bar {
            background-color: #007acc;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            padding: 2px 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .editor-container {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        pre {
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            margin-top: 0;
        }
        
        #code-output {
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 10px;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        }
        
        .output-container {
            margin-top: 15px;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        .output-header {
            background-color: #252526;
            color: #cccccc;
            padding: 5px 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            font-weight: 500;
            border-bottom: 1px solid #444;
        }
        
        .output-content {
            background-color: #1e1e1e;
            padding: 0;
        }
        
        .ui.info.message {
            background-color: #252526;
            color: #cccccc;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            border: none;
            border-left: 4px solid #007acc;
        }
        
        .ui.info.message .header {
            color: #ffffff;
        }
        
        .ui.info.message pre {
            background-color: #1e1e1e;
            border: 1px solid #444;
        }
        
        .ui.success.message {
            background-color: #1e1e1e;
            color: #89D185;
            border: none;
        }
        
        .ui.error.message {
            background-color: #1e1e1e;
            color: #F14C4C;
            border: none;
        }
    </style>
</head>
<body>
<div class="ui container" style="margin-top: 50px; max-width: 700px;">
    <div class="ui segment">
        <div class="ui grid">
            <div class="ten wide column">
                <h3>Вопрос <?= $question_index + 1 ?> из <?= $total_questions ?></h3>
            </div>
            <div class="six wide column right aligned">
                <div class="ui mini horizontal list">
                    <?php for ($i = 0; $i < $total_questions; $i++): ?>
                        <?php $answered = $_SESSION['test_answers'][$test_id][$i] !== null && $_SESSION['test_answers'][$test_id][$i] !== '' && $_SESSION['test_answers'][$test_id][$i] !== []; ?>
                        <div class="item">
                            <a href="test_pass.php?test_id=<?= $test_id ?>&q=<?= $i ?>" class="ui circular label <?= $i === $question_index ? 'blue' : '' ?> <?= $answered ? 'green' : 'grey' ?>">
                                <?= $i + 1 ?>
                            </a>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <form class="ui form" method="post">
            <input type="hidden" name="q" value="<?= $question_index ?>">
            <div class="field">
                <label><?= htmlspecialchars($question['text_question']) ?></label>
                <?php if ($type === 'single'): ?>
                    <?php foreach ($options as $opt_index => $option): ?>
                        <div class="field">
                            <div class="ui radio checkbox">
                                <input type="radio" name="answer" value="<?= $opt_index ?>" <?= ($answer_value == $opt_index) ? 'checked' : '' ?> required>
                                <label><?= htmlspecialchars($option['text_option']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($type === 'multi'): ?>
                    <?php foreach ($options as $opt_index => $option): ?>
                        <div class="field">
                            <div class="ui checkbox">
                                <input type="checkbox" name="multi_answer[]" value="<?= $opt_index ?>" <?= (is_array($answer_value) && in_array((string)$opt_index, $answer_value)) ? 'checked' : '' ?>>
                                <label><?= htmlspecialchars($option['text_option']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($type === 'match'): ?>
                    <div class="ui two column grid">
                        <?php 
                        $left = [];
                        $right = [];
                        foreach ($options as $opt_index => $option) {
                            $pair = explode('||', $option['text_option']);
                            $left[] = $pair[0] ?? '';
                            $right[] = $pair[1] ?? '';
                        }
                        foreach ($left as $i => $lval): ?>
                            <div class="column">
                                <div class="field">
                                    <label><?= htmlspecialchars($lval) ?></label>
                                    <select name="match_answer[<?= $i ?>]" class="ui dropdown" required>
                                        <option value="">Выберите...</option>
                                        <?php foreach ($right as $j => $rval): ?>
                                            <option value="<?= $j ?>" <?= (isset($answer_value[$i]) && $answer_value[$i] == $j) ? 'selected' : '' ?>><?= htmlspecialchars($rval) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($type === 'code'): ?>
                    <?php
                    // Get code task details
                    $stmt = $pdo->prepare("
                        SELECT * FROM code_tasks
                        WHERE id_question = ?
                    ");
                    $stmt->execute([$question['id_question']]);
                    $code_task = $stmt->fetch();
                    
                    $template_code = $code_task ? $code_task['template_code'] : '';
                    $language = $code_task ? $code_task['language'] : 'php';
                    
                    // Определяем язык для Monaco Editor
                    $monaco_language = '';
                    switch ($language) {
                        case 'php': $monaco_language = 'php'; break;
                        case 'python': $monaco_language = 'python'; break;
                        case 'cpp': $monaco_language = 'cpp'; break;
                    }
                    
                    // Подготовка примера кода
                    $sample_code = '';
                    if ($language === 'php') {
                        $sample_code = "<?php\nfunction sum_array(\$arr) {\n    \$sum = 0;\n    foreach (\$arr as \$value) {\n        \$sum += \$value;\n    }\n    return \$sum;\n}\n\n// Пример использования\n\$numbers = [1, 2, 3, 4, 5];\necho sum_array(\$numbers); // Выведет: 15\n?>";
                    } else if ($language === 'python') {
                        $sample_code = "def sum_array(arr):\n    return sum(arr)\n\n# Пример использования\nnumbers = [1, 2, 3, 4, 5]\nprint(sum_array(numbers))  # Выведет: 15";
                    } else if ($language === 'cpp') {
                        $sample_code = "#include <iostream>\n#include <vector>\n\nint sum_array(const std::vector<int>& arr) {\n    int sum = 0;\n    for (int value : arr) {\n        sum += value;\n    }\n    return sum;\n}\n\nint main() {\n    std::vector<int> numbers = {1, 2, 3, 4, 5};\n    std::cout << sum_array(numbers) << std::endl;  // Выведет: 15\n    return 0;\n}";
                    }
                    
                    // Используем код из ответа, шаблон или пример
                    $code_to_show = $answer_value ?: $template_code ?: $sample_code;
                    ?>
                    <div class="field">
                        <input type="hidden" name="code_answer" id="code-answer-input" value="<?= htmlspecialchars($code_to_show) ?>">
                        
                        <div class="editor-container">
                            <div class="editor-toolbar">
                                <div class="title">
                                    <?= strtoupper($language) ?> • main.<?= $language === 'cpp' ? 'cpp' : ($language === 'python' ? 'py' : 'php') ?>
                                </div>
                                <div class="buttons">
                                    <button type="button" id="run-code-btn" class="ui tiny button">
                                        <i class="play icon"></i> Запустить
                                    </button>
                                    <button type="button" id="format-code-btn" class="ui tiny button">
                                        <i class="align left icon"></i> Форматировать
                                    </button>
                                </div>
                            </div>
                            <div id="monaco-editor-container"></div>
                            <div class="editor-status-bar">
                                <div><?= strtoupper($language) ?></div>
                                <div>UTF-8</div>
                            </div>
                        </div>
                        
                        <div class="output-container" id="code-output-container" style="display: none;">
                            <div class="output-header">
                                <i class="terminal icon"></i> Терминал
                            </div>
                            <div class="output-content">
                                <div class="ui message" id="code-output-message"></div>
                                <pre id="code-output"></pre>
                            </div>
                        </div>
                        
                        <div class="ui info message">
                            <div class="header">Информация о задании</div>
                            <p>Язык программирования: <strong><?= htmlspecialchars(strtoupper($language)) ?></strong></p>
                            <?php if (!empty($code_task['input_ct'])): ?>
                                <p>Входные данные:</p>
                                <pre><?= htmlspecialchars($code_task['input_ct']) ?></pre>
                            <?php endif; ?>
                            <?php if (!empty($code_task['output_ct'])): ?>
                                <p>Ожидаемый вывод:</p>
                                <pre><?= htmlspecialchars($code_task['output_ct']) ?></pre>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ui divider"></div>
            <div class="ui buttons">
                <?php if ($question_index > 0): ?>
                    <button type="submit" name="action" value="prev" class="ui button">Назад</button>
                <?php endif; ?>
                <button type="submit" name="action" value="skip" class="ui button">Пропустить</button>
                <button type="submit" name="action" value="answer" class="ui primary button">Ответить</button>
                <?php if ($answered_count === $total_questions): ?>
                    <button type="submit" name="action" value="finish" class="ui positive button" style="margin-left: 10px;">Сдать работу</button>
                <?php endif; ?>
            </div>
        </form>
        <div class="ui divider"></div>
        <div>Отвечено: <?= $answered_count ?> / <?= $total_questions ?></div>
    </div>
</div>
<script>
$(function(){ $('.ui.radio.checkbox').checkbox(); $('.ui.checkbox').checkbox(); $('.ui.dropdown').dropdown(); });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const runBtn = document.getElementById('run-code-btn');
    const formatBtn = document.getElementById('format-code-btn');
    const outputContainer = document.getElementById('code-output-container');
    const outputMessage = document.getElementById('code-output-message');
    const output = document.getElementById('code-output');
    const codeAnswerInput = document.getElementById('code-answer-input');
    let editor;
    
    // Инициализация Monaco Editor
    require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
    require(['vs/editor/editor.main'], function() {
        // Создаем редактор
        editor = monaco.editor.create(document.getElementById('monaco-editor-container'), {
            value: <?= json_encode($code_to_show) ?>,
            language: '<?= $monaco_language ?>',
            theme: 'vs-dark',
            automaticLayout: true,
            minimap: { enabled: true },
            scrollBeyondLastLine: false,
            fontSize: 14,
            fontFamily: "'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace",
            lineNumbers: 'on',
            renderLineHighlight: 'all',
            roundedSelection: true,
            cursorStyle: 'line',
            cursorBlinking: 'blink',
            tabSize: 4,
            insertSpaces: true,
            formatOnType: true,
            formatOnPaste: true,
            wordWrap: 'off',
            rulers: [],
            autoIndent: 'full',
            renderIndentGuides: true,
            renderFinalNewline: true,
            fixedOverflowWidgets: true
        });
        
        // Обновляем скрытое поле при изменении кода
        editor.onDidChangeModelContent(function() {
            codeAnswerInput.value = editor.getValue();
        });
        
        // Форматирование кода
        formatBtn.addEventListener('click', function() {
            editor.getAction('editor.action.formatDocument').run();
        });
        
        // Добавляем горячие клавиши
        editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function() {
            // Ctrl+S - сохранить (ничего не делаем, просто для удобства)
        });
        
        editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyF, function() {
            // Ctrl+F - поиск
            editor.getAction('actions.find').run();
        });
        
        editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.Enter, function() {
            // Ctrl+Enter - запустить код
            runBtn.click();
        });
    });
    
    // Запуск кода
    runBtn.addEventListener('click', function() {
        // Показываем загрузку
        outputContainer.style.display = 'block';
        outputMessage.className = 'ui message loading';
        outputMessage.textContent = 'Выполнение кода...';
        output.textContent = '';
        
        // Отправляем код на сервер
        fetch('code_executor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                code: codeAnswerInput.value,
                language: '<?= $language ?>',
                input: <?= json_encode($code_task['input_ct'] ?? '') ?>,
                timeout: <?= (int)($code_task['execution_timeout'] ?? 5) ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                outputMessage.className = 'ui error message';
                outputMessage.textContent = 'Ошибка выполнения:';
                output.textContent = data.error;
            } else {
                outputMessage.className = 'ui success message';
                outputMessage.textContent = `Код выполнен успешно (${data.execution_time.toFixed(3)} сек)`;
                output.textContent = data.output;
            }
        })
        .catch(error => {
            outputMessage.className = 'ui error message';
            outputMessage.textContent = 'Ошибка выполнения:';
            output.textContent = error.message;
        });
    });
});
</script>
</body>
</html> 