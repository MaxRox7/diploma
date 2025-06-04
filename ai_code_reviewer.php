<?php
/**
 * AI Code Reviewer using YandexGPT API
 * 
 * This script analyzes student code submissions and provides feedback
 * on code quality, correctness, and suggestions for improvement.
 */

require 'vendor/autoload.php';
Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load();

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
redirect_unauthenticated();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Log requests for debugging
$log_file = fopen('ai_code_review_log.txt', 'a');
fwrite($log_file, "=== " . date('Y-m-d H:i:s') . " ===\n");

// Get data from request
$raw_input = file_get_contents('php://input');
fwrite($log_file, "Raw input: " . $raw_input . "\n");

$data = json_decode($raw_input, true);

// Validate required fields
$student_code = $data['student_code'] ?? '';
$expected_code = $data['expected_code'] ?? '';
$language = $data['language'] ?? '';
$task_description = $data['task_description'] ?? '';
$expected_output = $data['expected_output'] ?? '';
$actual_output = $data['actual_output'] ?? '';

if (empty($student_code) || empty($language)) {
    fwrite($log_file, "Error: Missing required parameters\n");
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    fclose($log_file);
    exit;
}

// API key for YandexGPT
$api_key = getenv('API_KEY');

// Build the prompt for the AI based on the language and task
$prompt = buildPrompt($student_code, $expected_code, $language, $task_description, $expected_output, $actual_output);

// Log the prompt
fwrite($log_file, "Prompt: " . $prompt . "\n");

// Prepare API request data
$api_data = [
    "modelUri" => "gpt://b1gr2hqj20frbeet0cet/yandexgpt-lite",
    "completionOptions" => [
        "stream" => false,
        "temperature" => 0.5,
        "maxTokens" => 2000
    ],
    "messages" => [
        [
            "role" => "user",
            "text" => $prompt
        ]
    ]
];

// Log API request data
fwrite($log_file, "API request data: " . json_encode($api_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");

// YandexGPT API URL
$url = 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion';

// Initialize cURL
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Api-Key ' . $api_key
]);
// Add request information
curl_setopt($ch, CURLOPT_VERBOSE, true);
// Disable SSL certificate verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// Set timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute request
$response = curl_exec($ch);

// Get request information
$info = curl_getinfo($ch);
fwrite($log_file, "HTTP code: " . $info['http_code'] . "\n");
fwrite($log_file, "Request time: " . $info['total_time'] . " seconds\n");

// Check for errors
if (curl_errno($ch)) {
    $error_msg = 'cURL error: ' . curl_error($ch);
    fwrite($log_file, $error_msg . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    curl_close($ch);
    fclose($log_file);
    exit;
}

// Close connection
curl_close($ch);

// Log response
fwrite($log_file, "API response: " . $response . "\n");

// Check if response is HTML
if (strpos($response, '<!DOCTYPE html>') !== false || 
    strpos($response, '<html>') !== false || 
    strpos($response, '<br') !== false) {
    
    $error_msg = 'Received HTML instead of JSON!';
    fwrite($log_file, $error_msg . "\n");
    fwrite($log_file, "First 500 characters of response: " . substr($response, 0, 500) . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    fclose($log_file);
    exit;
}

// Process response
$result = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $error_msg = 'JSON decoding error: ' . json_last_error_msg();
    fwrite($log_file, $error_msg . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    fclose($log_file);
    exit;
}

// Check response structure
if (!isset($result['result']['alternatives'][0]['message']['text'])) {
    $error_msg = 'Unexpected API response format';
    fwrite($log_file, $error_msg . "\n");
    fwrite($log_file, "Response structure: " . print_r($result, true) . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    fclose($log_file);
    exit;
}

// Get response text
$ai_feedback = $result['result']['alternatives'][0]['message']['text'];
fwrite($log_file, "AI feedback: " . $ai_feedback . "\n");

// Parse the AI's verdict on correctness
$is_correct = determineCorrectness($ai_feedback);
fwrite($log_file, "Is correct: " . ($is_correct ? 'true' : 'false') . "\n");

fwrite($log_file, "=== END ===\n\n");
fclose($log_file);

// Return result
echo json_encode([
    'success' => true,
    'feedback' => $ai_feedback,
    'is_correct' => $is_correct
]);

/**
 * Build prompt for the AI based on the code and task
 * 
 * @param string $student_code The student's code submission
 * @param string $expected_code The template or expected code (if available)
 * @param string $language The programming language
 * @param string $task_description The description of the task
 * @param string $expected_output The expected output
 * @param string $actual_output The actual output from the student's code
 * @return string The prompt for the AI
 */
function buildPrompt($student_code, $expected_code, $language, $task_description, $expected_output, $actual_output) {
    $prompt = "Ты — опытный преподаватель программирования. Проанализируй код студента и дай подробную оценку.

ЯЗЫК ПРОГРАММИРОВАНИЯ: {$language}

ЗАДАНИЕ:
{$task_description}

КОД СТУДЕНТА:
```
{$student_code}
```

";

    if (!empty($expected_output)) {
        $prompt .= "ОЖИДАЕМЫЙ ВЫВОД:
```
{$expected_output}
```

";
    }

    if (!empty($actual_output)) {
        $prompt .= "ФАКТИЧЕСКИЙ ВЫВОД:
```
{$actual_output}
```

";
    }

    if (!empty($expected_code)) {
        $prompt .= "ШАБЛОН КОДА:
```
{$expected_code}
```

";
    }

    $prompt .= "Проанализируй код по следующим критериям:
1. Корректность: Решает ли код поставленную задачу? Соответствует ли вывод ожидаемому?
2. Эффективность: Насколько эффективен алгоритм? Какова его сложность?
3. Стиль кода: Соответствует ли код стандартам для данного языка программирования?
4. Потенциальные проблемы: Есть ли в коде ошибки или потенциальные проблемы?

В начале своего ответа обязательно укажи ВЕРДИКТ: Решение правильное. или ВЕРДИКТ: Решение неправильное.

Затем дай подробный анализ и рекомендации по улучшению кода.";

    return $prompt;
}

/**
 * Determine if the AI considers the solution correct
 * 
 * @param string $feedback The AI's feedback
 * @return bool Whether the solution is correct
 */
function determineCorrectness($feedback) {
    // Look for the verdict at the beginning of the feedback
    if (preg_match('/ВЕРДИКТ:\s*Решение\s+правильное/i', $feedback)) {
        return true;
    }
    
    // Check for other positive indicators
    $positive_indicators = [
        'решение верное',
        'код работает правильно',
        'задача решена верно',
        'решение корректно',
        'код корректен'
    ];
    
    foreach ($positive_indicators as $indicator) {
        if (stripos($feedback, $indicator) !== false) {
            return true;
        }
    }
    
    // Default to false if no positive indicators found
    return false;
} 