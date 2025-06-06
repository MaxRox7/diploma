<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load();

require_once 'config.php';
redirect_unauthenticated();

/**
 * Execute code and validate against expected output
 * 
 * @param string $code The code to execute
 * @param array $code_task The code task details
 * @return array Result with output, error, and success status
 */
function execute_code_for_validation($code, $code_task) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT text_question FROM Questions WHERE id_question = ?");
    $stmt->execute([$code_task['id_question']]);
    $question = $stmt->fetch();
    $task_description = $question ? $question['text_question'] : '';
    
    // Логируем начало проверки
    error_log("Начало проверки кода для задания ID: " . $code_task['id_question']);

    // Выполняем код (если нужно) и получаем вывод
    $actual_output = '';
    
    // Формируем промпт для YandexGPT
    $prompt = "Задание: $task_description\n" .
              "Код студента:\n$code\n" .
              ($code_task['template_code'] ? "Ожидаемый шаблон кода:\n{$code_task['template_code']}\n" : "") .
              ($code_task['output_ct'] ? "Ожидаемый вывод: {$code_task['output_ct']}\n" : "") .
              ($actual_output ? "Фактический вывод: $actual_output\n" : "") .
              "\nПроверь, правильно ли решена задача. Ответ должен начинаться со слова ПРАВИЛЬНО или НЕПРАВИЛЬНО (с большой буквы), затем дай краткое объяснение. Ответь строго в формате JSON: {\"is_correct\": true/false, \"feedback\": \"ПРАВИЛЬНО/НЕПРАВИЛЬНО: объяснение\"}.";
    
    error_log("Сформирован промпт для YandexGPT: " . substr($prompt, 0, 100) . "...");
    
    // Отправляем запрос к YandexGPT
    $api_key = getenv('API_KEY');
    
    if (!$api_key) {
        error_log("API_KEY не найден в переменных окружения");
        return [
            'output' => $actual_output,
            'ai_feedback' => "Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.",
            'ai_feedback_error' => "API_KEY не найден в переменных окружения",
            'ai_is_correct' => false,
            'output_matches' => (trim($actual_output) === trim($code_task['output_ct'])),
            'success' => (trim($actual_output) === trim($code_task['output_ct']))
        ];
    }
    
    $api_data = [
        "modelUri" => "gpt://b1gr2hqj20frbeet0cet/yandexgpt-lite",
        "completionOptions" => [
            "stream" => false,
            "temperature" => 0.1,
            "maxTokens" => 512
        ],
        "messages" => [
            [
                "role" => "user",
                "text" => $prompt
            ]
        ]
    ];
    
    error_log("Отправляем запрос к YandexGPT API");
    
    $url = 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Api-Key ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    error_log("Получен ответ от YandexGPT API: HTTP " . $info['http_code']);
    
    // Значения по умолчанию
    $ai_feedback = "Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.";
    $ai_is_correct = false;
    $ai_feedback_error = null;
    
    // Обрабатываем ответ YandexGPT
    if ($info['http_code'] === 200) {
        $result = json_decode($response, true);
        
        if (isset($result['result']['alternatives'][0]['message']['text'])) {
            $gpt_text = $result['result']['alternatives'][0]['message']['text'];
            error_log("Текст ответа YandexGPT: " . $gpt_text);
            
            // Очищаем текст от обратных кавычек и маркеров форматирования
            $cleaned_text = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $gpt_text);
            
            // Пытаемся распарсить JSON из очищенного ответа
            $ai_result = json_decode($cleaned_text, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Если не удалось распарсить, попробуем найти JSON в тексте
                if (preg_match('/\{.*"is_correct".*"feedback".*\}/s', $gpt_text, $matches)) {
                    $json_text = $matches[0];
                    $ai_result = json_decode($json_text, true);
                }
            }
            
            if (is_array($ai_result) && isset($ai_result['is_correct']) && isset($ai_result['feedback'])) {
                $ai_feedback = $ai_result['feedback'];
                $ai_is_correct = $ai_result['is_correct'] ? true : false;
                error_log("Анализ кода: is_correct=" . ($ai_is_correct ? "true" : "false"));
            } else {
                $ai_feedback_error = "Ошибка парсинга ответа YandexGPT";
                error_log($ai_feedback_error . ": " . $gpt_text);
            }
        } else {
            $ai_feedback_error = "Неожиданный формат ответа YandexGPT";
            error_log($ai_feedback_error . ": " . $response);
        }
    } else {
        $ai_feedback_error = "Ошибка YandexGPT: HTTP " . $info['http_code'];
        if ($curl_error) $ai_feedback_error .= " (" . $curl_error . ")";
        error_log($ai_feedback_error . ": " . $response);
    }
    
    // Проверяем соответствие выходных данных как запасной вариант
    $output_matches = (trim($actual_output) === trim($code_task['output_ct']));
    
    // Если AI не смог дать оценку, используем соответствие выходных данных
    if ($ai_feedback === "Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.") {
        $ai_is_correct = $output_matches;
        error_log("AI анализ недоступен, используем сравнение выходных данных: " . ($output_matches ? "совпадает" : "не совпадает"));
    }
    
    return [
        'output' => $actual_output,
        'ai_feedback' => $ai_feedback,
        'ai_feedback_error' => $ai_feedback_error,
        'ai_is_correct' => $ai_is_correct,
        'output_matches' => $output_matches,
        'success' => $ai_is_correct || $output_matches
    ];
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

// Получаем текущий вопрос
if ($question_index >= 0 && $question_index < $total_questions) {
    $question = $questions[$question_index];
} else {
    // Если индекс недопустимый, перенаправляем на первый вопрос
    header('Location: test_pass.php?test_id=' . $test_id . '&q=0');
    exit;
}
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

// AJAX endpoint для прогресса проверки кода
if (isset($_GET['progress_check'])) {
    $total_code = 0;
    $checked_code = 0;
    $error_msgs = [];
    $pending_questions = [];
    
    header('Content-Type: application/json');
    
    // Получаем все вопросы теста
    $stmt = $pdo->prepare("SELECT * FROM Questions WHERE id_test = ? ORDER BY id_question");
    $stmt->execute([$test_id]);
    $questions = $stmt->fetchAll();
    
    // Проверяем статус каждого вопроса с кодом
    foreach ($questions as $i => $q) {
        if ($q['type_question'] === 'code') {
            $total_code++;
            $user_answer = $_SESSION['test_answers'][$test_id][$i] ?? null;
            
            if (!$user_answer) {
                continue; // Нет ответа на этот вопрос
            }
            
            // Получаем задание с кодом
            $stmt = $pdo->prepare("SELECT * FROM code_tasks WHERE id_question = ?");
            $stmt->execute([$q['id_question']]);
            $code_task = $stmt->fetch();
            
            if (!$code_task) {
                continue; // Нет задания с кодом для этого вопроса
            }
            
            // Повторно выполняем проверку кода
            $result = execute_code_for_validation($user_answer, $code_task);
            
            // Обновляем детали результата
            $details[$i]['ai_feedback'] = $result['ai_feedback'] ?? 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.';
            $details[$i]['ai_feedback_error'] = $result['ai_feedback_error'] ?? null;
            $details[$i]['output_matches'] = $result['output_matches'] ?? false;
            $details[$i]['ai_is_correct'] = $result['ai_is_correct'] ?? false;
            
            // Проверяем, завершена ли проверка AI
            if (isset($result['ai_feedback']) && 
                $result['ai_feedback'] !== 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.' && 
                strpos($result['ai_feedback'], 'Ошибка AI:') === false && 
                $result['ai_feedback'] !== '') {
                $checked_code++;
            } else {
                $pending_questions[] = $i + 1; // Номера вопросов для пользователя начинаются с 1
                
                if (isset($result['ai_feedback_error']) && $result['ai_feedback_error']) {
                    $error_msgs[] = 'Ошибка проверки задания #' . ($i+1) . ': ' . $result['ai_feedback_error'];
                }
            }
        }
    }
    
    // Если все проверки завершены - возвращаем статус "done"
    if ($checked_code >= $total_code || empty($pending_questions)) {
        echo json_encode([
            'status' => 'done',
            'checked_code' => $checked_code,
            'total_code' => $total_code,
            'errors' => $error_msgs
        ]);
        exit;
    }
    
    // Иначе возвращаем статус "progress"
    echo json_encode([
        'status' => 'progress',
        'checked_code' => $checked_code,
        'total_code' => $total_code,
        'pending' => $pending_questions,
        'errors' => $error_msgs
    ]);
    exit;
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