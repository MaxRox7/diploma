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
$log_file = fopen('generate_code_log.txt', 'a');
fwrite($log_file, "=== " . date('Y-m-d H:i:s') . " ===\n");

// Получаем данные из запроса
$raw_input = file_get_contents('php://input');
fwrite($log_file, "Raw input: " . $raw_input . "\n");

$data = json_decode($raw_input, true);
$question = $data['question'] ?? '';
$language = $data['language'] ?? 'php';
$difficulty = $data['difficulty'] ?? 'medium';
$input_example = $data['input_example'] ?? '';

if (empty($question) || empty($language)) {
    fwrite($log_file, "Error: Missing required parameters\n");
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    fclose($log_file);
    exit;
}

// API-ключ Яндекс GPT
$api_key = getenv('API_KEY');

// Формируем запрос к API в зависимости от языка программирования
$prompt = "Задача: {$question}\n\n";
$prompt .= "Язык программирования: {$language}\n";
$prompt .= "Сложность: {$difficulty}\n";

if (!empty($input_example)) {
    $prompt .= "Пример входных данных: {$input_example}\n";
}

$prompt .= "\nСоздай для этой задачи:\n";
$prompt .= "1. Шаблон кода, который будет предоставлен студенту в качестве отправной точки. Шаблон должен содержать основную структуру решения, но не полное решение.\n";
$prompt .= "2. Правильное решение задачи, которое должно корректно работать и соответствовать всем требованиям.\n";
$prompt .= "3. Пример выходных данных, которые должна генерировать программа при указанных входных данных.\n\n";
$prompt .= "Формат ответа:\n";
$prompt .= "ШАБЛОН:\n[код шаблона]\n\nРЕШЕНИЕ:\n[код решения]\n\nВЫХОДНЫЕ ДАННЫЕ:\n[ожидаемый вывод]";

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

// Настраиваем параметры запроса
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

// Проверяем структуру ответа от Yandex GPT API
if (!isset($result['result']['alternatives'][0]['message']['text'])) {
    $error_msg = 'Unexpected API response format';
    fwrite($log_file, $error_msg . "\n");
    fwrite($log_file, "Response structure: " . print_r($result, true) . "\n");
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    fclose($log_file);
    exit;
}

// Получаем текст ответа
$generated_text = $result['result']['alternatives'][0]['message']['text'];
fwrite($log_file, "Generated text: " . $generated_text . "\n");

// Парсим ответ для извлечения шаблона, решения и выходных данных
$template = '';
$solution = '';
$output = '';

// Извлекаем шаблон
if (preg_match('/ШАБЛОН:\s*\n(.*?)(?=\n\nРЕШЕНИЕ:)/s', $generated_text, $matches)) {
    $template = trim($matches[1]);
}

// Извлекаем решение
if (preg_match('/РЕШЕНИЕ:\s*\n(.*?)(?=\n\nВЫХОДНЫЕ ДАННЫЕ:)/s', $generated_text, $matches)) {
    $solution = trim($matches[1]);
}

// Извлекаем выходные данные
if (preg_match('/ВЫХОДНЫЕ ДАННЫЕ:\s*\n(.*?)$/s', $generated_text, $matches)) {
    $output = trim($matches[1]);
}

// Если не удалось извлечь все компоненты, пробуем альтернативный подход
if (empty($template) || empty($solution) || empty($output)) {
    // Разделяем по ключевым словам
    $parts = explode("\n\n", $generated_text);
    foreach ($parts as $part) {
        if (strpos($part, 'ШАБЛОН:') === 0) {
            $template = trim(substr($part, 8));
        } elseif (strpos($part, 'РЕШЕНИЕ:') === 0) {
            $solution = trim(substr($part, 8));
        } elseif (strpos($part, 'ВЫХОДНЫЕ ДАННЫЕ:') === 0) {
            $output = trim(substr($part, 16));
        }
    }
}

fwrite($log_file, "Extracted template: " . $template . "\n");
fwrite($log_file, "Extracted solution: " . $solution . "\n");
fwrite($log_file, "Extracted output: " . $output . "\n");
fwrite($log_file, "=== END ===\n\n");
fclose($log_file);

// Возвращаем результат
echo json_encode([
    'success' => true,
    'template' => $template,
    'solution' => $solution,
    'output' => $output,
    'language' => $language
]); 