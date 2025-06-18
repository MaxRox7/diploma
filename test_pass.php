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

// Проверяем ограничения на количество попыток
$user_id = $_SESSION['user']['id_user'];

// Пропускаем проверки в режиме практики
if (!$test_info['practice_mode']) {
    // Получаем информацию о предыдущих попытках
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempt_count, MAX(end_time) as last_attempt_time
        FROM test_attempts
        WHERE id_test = ? AND id_user = ? AND status = 'completed'
    ");
    $stmt->execute([$test_id, $user_id]);
    $attempts_info = $stmt->fetch();
    $attempt_count = (int)$attempts_info['attempt_count'];
    $last_attempt_time = $attempts_info['last_attempt_time'];

    // Получаем дополнительные попытки для студента
    $stmt = $pdo->prepare("
        SELECT additional_attempts 
        FROM student_test_settings
        WHERE id_test = ? AND id_user = ?
    ");
    $stmt->execute([$test_id, $user_id]);
    $additional_attempts = (int)($stmt->fetchColumn() ?: 0);
    
    // Максимальное количество попыток с учетом дополнительных
    $max_attempts = (int)$test_info['max_attempts'] + $additional_attempts;
    
    // Проверяем, не превышено ли максимальное количество попыток
    if ($attempt_count >= $max_attempts) {
        // Проверяем, был ли тест пройден успешно
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM test_attempts
            WHERE id_test = ? AND id_user = ? AND status = 'completed'
            AND score >= (SELECT passing_percentage * max_score / 100 FROM Tests WHERE id_test = ?)
        ");
        $stmt->execute([$test_id, $user_id, $test_id]);
        $has_passed = (int)$stmt->fetchColumn() > 0;
        
        if ($has_passed) {
            // Если тест уже пройден успешно, перенаправляем на результаты
            header('Location: test_results.php?test_id=' . $test_id . '&message=already_passed');
            exit;
        } else {
            // Если тест не пройден и попытки исчерпаны
            echo '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Превышено количество попыток - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>';
            include 'header.php';
            echo '<div class="ui container" style="margin-top: 50px;">
    <div class="ui segment">
        <h2 class="ui header">
            <i class="ban icon"></i>
            <div class="content">
                Превышено максимальное количество попыток
                <div class="sub header">Вы уже использовали все доступные попытки для прохождения этого теста.</div>
            </div>
        </h2>
        <div class="ui warning message">
            <div class="header">Что можно сделать?</div>
            <ul class="list">
                <li>Обратитесь к преподавателю для получения дополнительных попыток</li>
                <li>Вернитесь к материалам урока для лучшей подготовки</li>
            </ul>
        </div>
        <a href="lesson.php?id='.$test_info['id_lesson'].'" class="ui button">Вернуться к уроку</a>
    </div>
</div>
</body>
</html>';
            exit;
        }
    }
    
    // Проверяем интервал между попытками
    if ($last_attempt_time && $test_info['time_between_attempts'] > 0) {
        $last_attempt = new DateTime($last_attempt_time);
        $now = new DateTime();
        $interval_minutes = $test_info['time_between_attempts'];
        $required_wait_time = clone $last_attempt;
        $required_wait_time->add(new DateInterval('PT' . $interval_minutes . 'M'));
        
        if ($now < $required_wait_time) {
            $wait_time_remaining = $now->diff($required_wait_time);
            $hours = $wait_time_remaining->h + ($wait_time_remaining->d * 24);
            $minutes = $wait_time_remaining->i;
            
            echo '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Необходимо подождать - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>';
            include 'header.php';
            echo '<div class="ui container" style="margin-top: 50px;">
    <div class="ui segment">
        <h2 class="ui header">
            <i class="clock icon"></i>
            <div class="content">
                Необходимо подождать перед новой попыткой
                <div class="sub header">Между попытками должно пройти не менее '.$interval_minutes.' минут.</div>
            </div>
        </h2>
        <div class="ui info message">
            <div class="header">Оставшееся время ожидания</div>
            <p>'.($hours > 0 ? $hours.' ч. ' : '').$minutes.' мин.</p>
        </div>
        <a href="lesson.php?id='.$test_info['id_lesson'].'" class="ui button">Вернуться к уроку</a>
    </div>
</div>
</body>
</html>';
            exit;
        }
    }
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
                   AND ta.score >= (SELECT t2.passing_percentage * ta.max_score / 100 FROM Tests t2 WHERE t2.id_test = ta.id_test)
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

// Создаем уникальный идентификатор сессии прохождения теста для перемешивания вариантов
if (!isset($_SESSION['test_session_id'])) {
    $_SESSION['test_session_id'] = [];
}
if (!isset($_SESSION['test_session_id'][$test_id])) {
    $_SESSION['test_session_id'][$test_id] = uniqid('test_', true) . '_' . time();
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
    }
}

// Завершение теста
if (
    $is_finish || $question_index === -1
) {
    // Считаем результат
    $correct = 0;
    $details = [];
    $ai_pending = false;
    $ai_errors = [];
    foreach ($questions as $i => $q) {
        // Initialize the details entry for this question first
        $details[$i] = [
            'question' => $q['text_question'] ?? '',
            'type' => $q['type_question'],
            'user_answer' => $_SESSION['test_answers'][$test_id][$i],
            'is_right' => false,
            'right' => $q['answer_question'] ?? '',
            'ai_feedback' => '',
            'ai_feedback_error' => null,
            'output_matches' => false,
        ];
        
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
                    
                    // Сохраняем все детали результата проверки
                    $details[$i]['ai_feedback'] = $result['ai_feedback'] ?? 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.';
                    $details[$i]['ai_feedback_error'] = $result['ai_feedback_error'] ?? null;
                    $details[$i]['output_matches'] = $result['output_matches'] ?? false;
                    
                    // Проверяем результат выполнения
                    if (isset($result['ai_feedback'])) {
                        $is_right = $result['success'] && $result['ai_is_correct'];
                        
                        // Если не получили нормальный ответ от AI — помечаем как pending
                        if ($result['ai_feedback'] === 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.' || 
                            strpos($result['ai_feedback'], 'Ошибка AI:') !== false) {
                            $ai_pending = true;
                            $ai_errors[] = $i;
                            error_log("Помечаем вопрос #$i как pending из-за ошибки AI: " . ($result['ai_feedback_error'] ?? 'неизвестная ошибка'));
                        }
                    } else {
                        // Если не получили ответ от AI — помечаем как pending
                        $ai_pending = true;
                        $ai_errors[] = $i;
                        error_log("Помечаем вопрос #$i как pending из-за отсутствия ответа AI");
                        $is_right = $result['output_matches'] ?? (trim($result['output']) === trim($code_task['output_ct']));
                    }
                } else {
                    $is_right = false;
                }
            }
        }
        
        // Update the is_right value in the details array
        $details[$i]['is_right'] = $is_right;
        
        if ($is_right) $correct++;
    }
    // Если есть хотя бы один незавершённый/ошибочный AI-запрос — не показываем результат
    if ($ai_pending) {
        $total_code = 0;
        $checked_code = 0;
        $error_msgs = [];
        $pending_questions = [];
        
        foreach ($questions as $i => $q) {
            if ($q['type_question'] === 'code') {
                $total_code++;
                if (isset($details[$i]['ai_feedback']) && 
                    $details[$i]['ai_feedback'] !== 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.' && 
                    strpos($details[$i]['ai_feedback'], 'Ошибка AI:') === false && 
                    $details[$i]['ai_feedback'] !== '') {
                    $checked_code++;
                } else {
                    $pending_questions[] = $i + 1; // Номера вопросов для пользователя начинаются с 1
                    if (isset($details[$i]['ai_feedback_error']) && $details[$i]['ai_feedback_error']) {
                        $error_msgs[] = 'Ошибка проверки задания #' . ($i+1) . ': ' . htmlspecialchars($details[$i]['ai_feedback_error']);
                    }
                }
            }
        }
        
        echo '<div class="ui info message" style="max-width:600px;margin:auto;margin-top:40px;">'
            .'<b>Пожалуйста, подождите, идёт проверка всех заданий с кодом...</b><br>'
            .'Не закрывайте вкладку. Проверка может занять до 30 секунд.<br><br>'
            .'<div id="progress-bar-container" style="margin-top:20px;">'
            .'<div class="ui indicating progress" id="ai-progress" data-total="' . $total_code . '" data-value="' . $checked_code . '" style="height:30px;">'
            .'<div class="bar" style="transition-duration: 300ms; width: ' . ($total_code > 0 ? round($checked_code/$total_code*100) : 0) . '%;"></div>'
            .'<div class="label" id="progress-label">Проверено ' . $checked_code . ' из ' . $total_code . '</div>'
            .'</div>'
            .'</div>';
            
        if (!empty($pending_questions)) {
            echo '<div class="ui warning message" style="margin-top:20px;">';
            echo '<b>Ожидают проверки задания:</b> #' . implode(', #', $pending_questions);
            echo '</div>';
        }
        
        if (!empty($error_msgs)) {
            echo '<div class="ui error message" style="margin-top:20px;">';
            echo '<b>Обнаружены ошибки при проверке:</b><ul>';
            foreach ($error_msgs as $msg) echo '<li>' . $msg . '</li>';
            echo '</ul></div>';
        }
        
        echo '</div>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>';
        echo '<script>';
        echo 'function checkProgress() {';
        echo '  $.get(window.location.href, {progress_check: 1}, function(data) {';
        echo '    if (data && data.status === "done") {';
        echo '      location.reload();';
        echo '    } else if (data && data.status === "progress") {';
        echo '      var checked = data.checked_code, total = data.total_code;';
        echo '      var percent = total > 0 ? Math.round(checked/total*100) : 0;';
        echo '      $("#ai-progress .bar").css("width", percent+"%");';
        echo '      $("#progress-label").text("Проверено "+checked+" из "+total);';
        
        echo '      // Обновляем список ожидающих проверки заданий';
        echo '      if (data.pending && data.pending.length > 0) {';
        echo '        var pendingHtml = "<b>Ожидают проверки задания:</b> #" + data.pending.join(", #");';
        echo '        if ($("#pending-tasks").length) { $("#pending-tasks").html(pendingHtml); } else { $("#progress-bar-container").after("<div class=\"ui warning message\" id=\"pending-tasks\" style=\"margin-top:20px;\">"+pendingHtml+"</div>"); }';
        echo '      } else {';
        echo '        $("#pending-tasks").remove();';
        echo '      }';
        
        echo '      // Обновляем список ошибок';
        echo '      if (data.errors && data.errors.length > 0) {';
        echo '        var html = "<b>Обнаружены ошибки при проверке:</b><ul>";';
        echo '        for (var i=0; i<data.errors.length; i++) html += "<li>"+data.errors[i]+"</li>";';
        echo '        html += "</ul>";';
        echo '        if ($("#ai-errors").length) { $("#ai-errors").html(html); } else { $("#progress-bar-container").after("<div class=\"ui error message\" id=\"ai-errors\" style=\"margin-top:20px;\">"+html+"</div>"); }';
        echo '      } else {';
        echo '        $("#ai-errors").remove();';
        echo '      }';
        
        echo '      setTimeout(checkProgress, 3000);';
        echo '    } else {';
        echo '      setTimeout(checkProgress, 5000);';
        echo '    }';
        echo '  }, "json").fail(function() {';
        echo '    setTimeout(checkProgress, 5000);'; // В случае ошибки запроса пробуем снова через 5 секунд
        echo '  });';
        echo '}';
        echo 'checkProgress();';
        echo '</script>';
        exit;
    }
    $score = $correct;
    $max_score = count($details);
    
    // Вычисляем процент правильных ответов
    $score_percentage = round(($score / $max_score) * 100);
    
    // Проверяем, достигнут ли проходной балл
    $is_passed = $score_percentage >= $test_info['passing_percentage'];
    
    // Создаём новую попытку с соответствующим статусом
    $stmt = $pdo->prepare("INSERT INTO test_attempts (id_test, id_user, score, max_score, status, end_time) VALUES (?, ?, ?, ?, ?, NOW()) RETURNING id_attempt");
    $stmt->execute([$test_id, $user_id, $score, $max_score, 'completed']);
    $id_attempt = $stmt->fetchColumn();
    
    // Сохраняем ответы с учетом результатов AI-проверки
    foreach ($questions as $i => $q) {
        $type = $q['type_question'];
        $user_answer = $_SESSION['test_answers'][$test_id][$i];
        $is_right = $details[$i]['is_right'];
        $is_right_bool = $is_right;
        // answer_text для сложных типов
        $answer_text = null;
        // Инициализируем переменную для хранения отзыва ИИ для текущего вопроса
        $ai_feedback_text = null;
        
        if ($type === 'multi' || $type === 'match') {
            $answer_text = json_encode($user_answer, JSON_UNESCAPED_UNICODE);
        } elseif ($type === 'code') {
            $answer_text = $user_answer;
            
            // Получаем отзыв ИИ для текущего вопроса
            if (isset($details[$i]['ai_feedback'])) {
                $ai_feedback_text = $details[$i]['ai_feedback'];
                error_log("Вопрос #$i: Установлен отзыв ИИ: " . substr($ai_feedback_text, 0, 50) . "...");
            } else {
                $ai_feedback_text = 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.';
                error_log("Вопрос #$i: Отзыв ИИ не найден, используем значение по умолчанию");
            }
            
            // Принудительно создаем колонку ai_feedback, если её нет
            try {
                $pdo->exec("ALTER TABLE test_answers ADD COLUMN IF NOT EXISTS ai_feedback TEXT");
            } catch (PDOException $e) {
                error_log("Ошибка при создании колонки ai_feedback: " . $e->getMessage());
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO test_answers (id_attempt, id_question, id_selected_option, is_correct, answer_text, ai_feedback) VALUES (?, ?, ?, ?, ?, ?)");
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
        $stmt->bindValue(6, $ai_feedback_text, $ai_feedback_text === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();
        
        // Логируем информацию о сохранении
        if ($type === 'code') {
            error_log("Сохранен ответ на вопрос #{$i}, id_question={$q['id_question']}, тип={$type}, ai_feedback=" . 
                      ($ai_feedback_text ? substr($ai_feedback_text, 0, 30) . "..." : "NULL"));
        }
    }
    // Очищаем сессию
    unset($_SESSION['test_answers'][$test_id]);
    unset($_SESSION['test_session_id'][$test_id]);
    
    // Показываем результаты теста
    ?><!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Результаты теста</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
        <style>
            body {
                background-color: #f9f9f9;
            }
            
            .ui.container {
                padding: 20px;
            }
            
            .ui.segment {
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
            
            .ui.accordion .title {
                padding: 15px;
            }
            
            .ui.accordion .content {
                padding: 15px;
            }
            
            .editor-toolbar {
                background-color: #252526;
                padding: 5px 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
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
                <i class="check circle outline <?= $is_passed ? 'green' : 'red' ?> icon"></i>
                Тест завершён!
            </h2>
            <div class="ui center aligned huge header" style="margin-top: 20px;">
                Ваш результат: <span class="ui <?= $score_percentage >= $test_info['passing_percentage'] ? 'green' : 'red' ?> text"><b><?= $score ?> / <?= $max_score ?></b></span>
            </div>
            
            <?php if($score_percentage < $test_info['passing_percentage']): ?>
                <div class="ui error message">
                    <div class="header">Тест не пройден</div>
                    <p>Вы не набрали минимальный проходной балл (<?= $test_info['passing_percentage'] ?>%). Попробуйте пройти тест ещё раз!</p>
                </div>
                
                <div class="ui center aligned container" style="margin-top: 20px;">
                    <a href="test_pass.php?test_id=<?= $test_id ?>" class="ui primary button">
                        <i class="redo icon"></i> Пройти тест снова
                    </a>
                </div>
            <?php else: ?>
                <div class="ui success message">
                    <div class="header">Тест успешно пройден!</div>
                    <p>Поздравляем! Вы успешно прошли тест, набрав <?= $score_percentage ?>% при минимальном проходном балле <?= $test_info['passing_percentage'] ?>%.</p>
                </div>
            <?php endif; ?>
            
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
                        <?php $type = $d['type']; ?>
                        <?php if ($type === 'single' || $type === 'multi'): ?>
                            <p><b>Ваш ответ:</b> <?= htmlspecialchars($d['user_answer'] !== null ? (is_array($d['user_answer']) ? implode(", ", $d['user_answer']) : $d['user_answer']) : 'Нет ответа') ?></p>
                            <p><b>Правильный ответ:</b> <?= htmlspecialchars($d['right']) ?></p>
                        <?php elseif ($type === 'match'): ?>
                            <p><b>Ваш ответ:</b> <?= htmlspecialchars($d['user_answer'] !== null ? json_encode($d['user_answer'], JSON_UNESCAPED_UNICODE) : 'Нет ответа') ?></p>
                        <?php elseif ($type === 'code'): ?>
                            <p><b>Ваш код:</b></p>
                            <pre style="background-color: #1e1e1e; color: #d4d4d4; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto;"><?= htmlspecialchars($d['user_answer'] !== null ? $d['user_answer'] : '') ?></pre>
                            
                            <?php if (!empty($d['ai_feedback'])): ?>
                                <div class="ui raised segment" style="margin-top: 20px; border-left: 4px solid <?= strpos($d['ai_feedback'], 'Ошибка AI:') !== false ? '#db2828' : '#007acc' ?>;">
                                    <h4 style="color: <?= strpos($d['ai_feedback'], 'Ошибка AI:') !== false ? '#db2828' : '#007acc' ?>;">
                                        <i class="<?= strpos($d['ai_feedback'], 'Ошибка AI:') !== false ? 'exclamation triangle' : 'comment alternate outline' ?> icon"></i> 
                                        <?= strpos($d['ai_feedback'], 'Ошибка AI:') !== false ? 'Ошибка проверки кода:' : 'Анализ кода от ИИ:' ?>
                                    </h4>
                                    <div style="padding: 10px; background-color: #f8f8f8; border-radius: 4px; margin-top: 10px;">
                                        <?= nl2br(htmlspecialchars($d['ai_feedback'])) ?>
                                    </div>
                                    
                                    <?php if ($d['ai_feedback'] === 'Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.'): ?>
                                        <div style="margin-top: 10px; padding: 10px; background-color: #fffaf3; border-left: 4px solid #f2711c; border-radius: 4px;">
                                            <i class="info circle icon"></i>
                                            Код проверен по соответствию выходных данных: 
                                            <b><?= $d['output_matches'] ? 'Выходные данные совпадают' : 'Выходные данные не совпадают' ?></b>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($d['ai_feedback_error']) && $d['ai_feedback_error']): ?>
                                        <div style="margin-top: 10px; padding: 10px; background-color: #fff6f6; border-left: 4px solid #db2828; border-radius: 4px;">
                                            <i class="exclamation triangle icon"></i>
                                            <b>Детали ошибки:</b> <?= htmlspecialchars($d['ai_feedback_error']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="ui raised segment" style="margin-top: 20px; border-left: 4px solid #f2711c;">
                                    <h4 style="color: #f2711c;">
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
    
    // Перемешиваем варианты ответов для студентов (уникально для каждой попытки)
    if (!empty($options)) {
        // Создаем уникальный seed на основе ID студента, теста, вопроса, ID сессии и PHP session ID
        $session_id = $_SESSION['test_session_id'][$test_id];
        $php_session_id = session_id();
        $seed = $user_id . '_' . $test_id . '_' . $question['id_question'] . '_' . $session_id . '_' . $php_session_id;
        $seed_number = crc32($seed);
        
        // Устанавливаем seed для воспроизводимого перемешивания
        mt_srand($seed_number);
        
        // Создаем массив индексов и перемешиваем их
        $indices = array_keys($options);
        shuffle($indices);
        
        // Переупорядочиваем массив вариантов
        $shuffled_options = [];
        foreach ($indices as $i => $original_index) {
            $shuffled_options[$i] = $options[$original_index];
            // Сохраняем оригинальный индекс для корректной обработки ответов
            $shuffled_options[$i]['original_index'] = $original_index;
        }
        $options = $shuffled_options;
        
        // Восстанавливаем обычный seed
        mt_srand();
    }
}

// Счетчик
$answered_count = 0;
foreach ($_SESSION['test_answers'][$test_id] as $ans) {
    if ($ans !== null && $ans !== '' && $ans !== []) $answered_count++;
}

// Информация о доступных попытках (для отображения пользователю)
$attempt_info = '';
if (!$test_info['practice_mode']) {
    // Получаем информацию о предыдущих попытках
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempt_count, MAX(end_time) as last_attempt_time
        FROM test_attempts
        WHERE id_test = ? AND id_user = ? AND status = 'completed'
    ");
    $stmt->execute([$test_id, $user_id]);
    $attempts_info = $stmt->fetch();
    $attempt_count = (int)$attempts_info['attempt_count'];
    $last_attempt_time = $attempts_info['last_attempt_time'];
    
    // Получаем дополнительные попытки для студента
    $stmt = $pdo->prepare("
        SELECT additional_attempts 
        FROM student_test_settings
        WHERE id_test = ? AND id_user = ?
    ");
    $stmt->execute([$test_id, $user_id]);
    $additional_attempts = (int)($stmt->fetchColumn() ?: 0);
    
    // Максимальное количество попыток с учетом дополнительных
    $max_attempts = (int)$test_info['max_attempts'] + $additional_attempts;
    $remaining_attempts = $max_attempts - $attempt_count;
    
    // Получаем результаты предыдущих попыток
    $stmt = $pdo->prepare("
        SELECT score, max_score, end_time, status
        FROM test_attempts
        WHERE id_test = ? AND id_user = ? AND status = 'completed'
        ORDER BY end_time DESC
    ");
    $stmt->execute([$test_id, $user_id]);
    $previous_attempts = $stmt->fetchAll();
    
    $attempt_info = "<div class='ui info message'>
        <div class='header'>Информация о тесте</div>
        <ul>
            <li>Проходной балл: <b>{$test_info['passing_percentage']}%</b></li>
            <li>Оставшиеся попытки: <b>{$remaining_attempts} из {$max_attempts}</b></li>";
    
    // Добавляем информацию о времени между попытками
    if ($test_info['time_between_attempts'] > 0) {
        $attempt_info .= "<li>Интервал между попытками: <b>{$test_info['time_between_attempts']} мин.</b></li>";
    }
    
    $attempt_info .= "</ul>";
    
    // Добавляем информацию о предыдущих попытках, если они есть
    if (!empty($previous_attempts)) {
        $attempt_info .= "<div class='ui divider'></div>
            <div class='header'>Предыдущие попытки</div>
            <table class='ui very compact small table'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Дата</th>
                        <th>Результат</th>
                        <th>Процент</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($previous_attempts as $index => $attempt) {
            $date = new DateTime($attempt['end_time']);
            $score_percentage = round(($attempt['score'] / $attempt['max_score']) * 100);
            $is_passed = $score_percentage >= $test_info['passing_percentage'];
            
            $attempt_info .= "<tr>
                <td>" . ($attempt_count - $index) . "</td>
                <td>" . $date->format('d.m.Y H:i') . "</td>
                <td>{$attempt['score']} / {$attempt['max_score']}</td>
                <td>{$score_percentage}%</td>
                <td>" . ($is_passed ? '<span class="ui green text">Пройден</span>' : '<span class="ui red text">Не пройден</span>') . "</td>
            </tr>";
        }
        
        $attempt_info .= "</tbody></table>";
    }
    
    $attempt_info .= "</div>";
} else {
    $attempt_info = "<div class='ui info message'>
        <div class='header'>Режим практики</div>
        <p>Этот тест работает в режиме практики. Результаты не будут учитываться в общем прогрессе, и количество попыток не ограничено.</p>
    </div>";
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
    <div class="ui grid">
        <div class="sixteen wide column">
            <div class="ui segment">
                <h2 class="ui header">
                    <?= htmlspecialchars($test_info['name_test']) ?>
                </h2>
                <?php if (!empty($test_info['desc_test'])): ?>
                <div class="ui message">
                    <?= htmlspecialchars($test_info['desc_test']) ?>
                </div>
                <?php endif; ?>
                
                <?= $attempt_info ?>
                
                <div class="ui divider"></div>
                
                <div class="ui tiny steps" style="width: 100%">
                    <?php for ($i = 0; $i < count($questions); $i++): ?>
                        <a class="<?= $i === $question_index ? 'active' : '' ?> <?= isset($_SESSION['test_answers'][$test_id][$i]) && $_SESSION['test_answers'][$test_id][$i] !== null && $_SESSION['test_answers'][$test_id][$i] !== '' && $_SESSION['test_answers'][$test_id][$i] !== [] ? 'completed' : '' ?> step" href="?test_id=<?= $test_id ?>&q=<?= $i ?>">
                            <div class="content">
                                <div class="title"><?= $i + 1 ?></div>
                            </div>
                        </a>
                    <?php endfor; ?>
                </div>
                
                <!-- Кнопки навигации из верхней панели удалены -->
            </div>
        </div>
    </div>
    <form class="ui form" method="post">
        <input type="hidden" name="q" value="<?= $question_index ?>">
        <div class="field">
            <label><?= htmlspecialchars($question['text_question']) ?></label>
            <?php if ($type === 'single'): ?>
                <?php foreach ($options as $opt_index => $option): ?>
                    <?php 
                    // Используем оригинальный индекс для value, если варианты были перемешаны
                    $value_index = isset($option['original_index']) ? $option['original_index'] : $opt_index;
                    ?>
                    <div class="field">
                        <div class="ui radio checkbox">
                            <input type="radio" name="answer" value="<?= $value_index ?>" <?= ($answer_value == $value_index) ? 'checked' : '' ?> required>
                            <label><?= htmlspecialchars($option['text_option']) ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif ($type === 'multi'): ?>
                <?php foreach ($options as $opt_index => $option): ?>
                    <?php 
                    // Используем оригинальный индекс для value, если варианты были перемешаны
                    $value_index = isset($option['original_index']) ? $option['original_index'] : $opt_index;
                    ?>
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="multi_answer[]" value="<?= $value_index ?>" <?= (is_array($answer_value) && in_array((string)$value_index, $answer_value)) ? 'checked' : '' ?>>
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