<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load();

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
redirect_unauthenticated();

// Проверяем, что запрос пришел методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Проверяем права доступа (только для преподавателей и админов)
if (!is_teacher() && !is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Логируем запрос для отладки
$log_file = fopen('generate_options_log.txt', 'a');
fwrite($log_file, "=== " . date('Y-m-d H:i:s') . " ===\n");

// Получаем данные из запроса
$raw_input = file_get_contents('php://input');
fwrite($log_file, "Raw input: " . $raw_input . "\n");

$data = json_decode($raw_input, true);
$question = $data['question'] ?? '';
$correct_answer = $data['correct_answer'] ?? '';
$num_options = $data['num_options'] ?? 3; // По умолчанию генерируем 3 варианта
$question_type = $data['question_type'] ?? 'single'; // По умолчанию тип вопроса 'single'

if (empty($question) || empty($correct_answer)) {
    fwrite($log_file, "Error: Missing required parameters\n");
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    fclose($log_file);
    exit;
}

// API-ключ Яндекс GPT
$api_key = getenv('API_KEY');

// Формируем запрос к API в зависимости от типа вопроса
$prompt = "";
switch ($question_type) {
    case 'single':
        $prompt = "Вопрос: {$question}\n\nПравильный ответ: {$correct_answer}\n\nСгенерируй {$num_options} неправильных, но правдоподобных вариантов ответа. Ответы должны быть короткими (не более 1-2 предложений) и относиться к той же теме, что и вопрос. Выведи только варианты ответов, каждый с новой строки, без нумерации.";
        break;
    case 'multi':
        $prompt = "Вопрос с множественным выбором: {$question}\n\nОдин из правильных ответов: {$correct_answer}\n\nСгенерируй {$num_options} вариантов ответа, включающих как правильные, так и неправильные варианты. Ответы должны быть короткими (не более 1-2 предложений) и относиться к той же теме, что и вопрос. Выведи только варианты ответов, каждый с новой строки, без нумерации. Варианты должны быть разнообразными, некоторые могут быть правильными, а некоторые неправильными.";
        break;
    case 'match':
        $prompt = "Вопрос на сопоставление: {$question}\n\nПример пары для сопоставления: {$correct_answer}\n\nСгенерируй {$num_options} пар для сопоставления по этой теме. Каждая пара должна содержать левую и правую части, разделенные символом '||'. Выведи только пары для сопоставления, каждую с новой строки, без нумерации.";
        break;
    default:
        $prompt = "Вопрос: {$question}\n\nПравильный ответ: {$correct_answer}\n\nСгенерируй {$num_options} неправильных, но правдоподобных вариантов ответа. Ответы должны быть короткими (не более 1-2 предложений) и относиться к той же теме, что и вопрос. Выведи только варианты ответов, каждый с новой строки, без нумерации.";
}

$api_data = [
    "modelUri" => "gpt://b1gr2hqj20frbeet0cet/yandexgpt-lite",
    "completionOptions" => [
        "stream" => false,
        "temperature" => 0.6,
        "maxTokens" => 2000
    ],
    "messages" => [
        [
            "role" => "user",
            "text" => $prompt
        ]
    ]
];

// Логируем данные запроса
fwrite($log_file, "API request data: " . json_encode($api_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");

// URL API Яндекс GPT
$url = 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion';

// Инициализируем cURL
$ch = curl_init($url);

// Настраиваем параметры запроса - ТОЧНО КАК В CURL_TEST.PHP
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Api-Key ' . $api_key
]);
// Добавляем информацию о запросе
curl_setopt($ch, CURLOPT_VERBOSE, true);
// Отключаем проверку SSL сертификата
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// Устанавливаем таймаут
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Выполняем запрос
$response = curl_exec($ch);

// Получаем информацию о запросе
$info = curl_getinfo($ch);
fwrite($log_file, "HTTP-код: " . $info['http_code'] . "\n");
fwrite($log_file, "Время запроса: " . $info['total_time'] . " сек\n");

// Проверяем на ошибки
if (curl_errno($ch)) {
    $error_msg = 'cURL error: ' . curl_error($ch);
    fwrite($log_file, $error_msg . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    curl_close($ch);
    fclose($log_file);
    exit;
}

// Закрываем соединение
curl_close($ch);

// Логируем ответ
fwrite($log_file, "API response: " . $response . "\n");

// Проверяем, не является ли ответ HTML
if (strpos($response, '<!DOCTYPE html>') !== false || 
    strpos($response, '<html>') !== false || 
    strpos($response, '<br') !== false) {
    
    $error_msg = 'Получен HTML вместо JSON!';
    fwrite($log_file, $error_msg . "\n");
    fwrite($log_file, "Первые 500 символов ответа: " . substr($response, 0, 500) . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    fclose($log_file);
    exit;
}

// Обрабатываем ответ
$result = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $error_msg = 'Ошибка декодирования JSON: ' . json_last_error_msg();
    fwrite($log_file, $error_msg . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    fclose($log_file);
    exit;
}

// Исправлено: проверяем структуру ответа от Yandex GPT API
if (!isset($result['result']['alternatives'][0]['message']['text'])) {
    $error_msg = 'Unexpected API response format';
    fwrite($log_file, $error_msg . "\n");
    fwrite($log_file, "Response structure: " . print_r($result, true) . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    fclose($log_file);
    exit;
}

// Получаем текст ответа (исправлено для новой структуры)
$generated_text = $result['result']['alternatives'][0]['message']['text'];
fwrite($log_file, "Generated text: " . $generated_text . "\n");

// Разбиваем текст на строки и очищаем от лишних символов
$options = array_map('trim', explode("\n", $generated_text));
$options = array_filter($options, function($line) {
    return !empty($line) && strpos($line, 'Вариант') === false && strpos($line, ':') === false;
});

$final_options = array_values($options);
fwrite($log_file, "Final options: " . print_r($final_options, true) . "\n");
fwrite($log_file, "=== END ===\n\n");
fclose($log_file);

// Возвращаем результат
echo json_encode([
    'success' => true,
    'options' => $final_options,
    'correct_answer' => $correct_answer,
    'question_type' => $question_type
]); 