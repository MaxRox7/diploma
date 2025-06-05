<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load();

header('Content-Type: application/json');

function log_ai($msg) {
    file_put_contents('/tmp/ai_code_reviewer.log', date('c') . "\n" . $msg . "\n\n", FILE_APPEND);
}

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Получаем данные от клиента
$raw_input = file_get_contents('php://input');
log_ai("RAW INPUT: $raw_input");
$data = json_decode($raw_input, true);

if (!$data || !isset($data['student_code']) || !isset($data['task_description'])) {
    $err = 'Некорректный запрос: отсутствует код студента или описание задания.';
    log_ai($err);
    echo json_encode([
        'is_correct' => false,
        'feedback' => $err
    ]);
    exit;
}

$student_code = $data['student_code'];
$expected_code = $data['expected_code'] ?? '';
$language = $data['language'] ?? 'php';
$task_description = $data['task_description'];
$expected_output = $data['expected_output'] ?? '';
$actual_output = $data['actual_output'] ?? '';

$prompt = "Задание: $task_description\n" .
          "Код студента:\n$student_code\n" .
          ($expected_code ? "Ожидаемый шаблон кода:\n$expected_code\n" : "") .
          ($expected_output ? "Ожидаемый вывод: $expected_output\n" : "") .
          ($actual_output ? "Фактический вывод: $actual_output\n" : "") .
          "\nПроверь, правильно ли решена задача. Ответь строго в формате JSON: {\"is_correct\": true/false, \"feedback\": \"комментарий\"}.";

// API-ключ Яндекс GPT - точно так же, как в generate_options.php
$api_key = getenv('API_KEY');

// Точно такая же структура запроса, как в generate_options.php
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

log_ai("API request data: " . json_encode($api_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$url = 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion';
$ch = curl_init($url);

// Настройки curl точно как в generate_options.php
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

// Создаем файл для записи verbose информации
$verbose_file = fopen('/tmp/curl_verbose.log', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose_file);

$response = curl_exec($ch);
$info = curl_getinfo($ch);

// Получаем verbose информацию
rewind($verbose_file);
$verbose_log = stream_get_contents($verbose_file);
fclose($verbose_file);
log_ai("CURL Verbose Log: " . $verbose_log);

log_ai("HTTP-код: " . $info['http_code'] . "\nВремя запроса: " . $info['total_time'] . " сек");

if (curl_errno($ch)) {
    $error_msg = 'cURL error: ' . curl_error($ch);
    log_ai($error_msg);
    http_response_code(500);
    echo json_encode(['is_correct' => false, 'feedback' => $error_msg]);
    curl_close($ch);
    exit;
}
curl_close($ch);

log_ai("API response: " . $response);

// Проверяем HTTP код
if ($info['http_code'] !== 200) {
    $error_msg = "Ошибка YandexGPT: HTTP " . $info['http_code'] . " — " . $response;
    log_ai($error_msg);
    http_response_code($info['http_code']);
    echo json_encode(['is_correct' => false, 'feedback' => $error_msg]);
    exit;
}

if (strpos($response, '<!DOCTYPE html>') !== false || strpos($response, '<html>') !== false || strpos($response, '<br') !== false) {
    $error_msg = 'Получен HTML вместо JSON!';
    log_ai($error_msg . "\nПервые 500 символов ответа: " . substr($response, 0, 500));
    http_response_code(500);
    echo json_encode(['is_correct' => false, 'feedback' => $error_msg]);
    exit;
}

$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $error_msg = 'Ошибка декодирования JSON: ' . json_last_error_msg();
    log_ai($error_msg);
    http_response_code(500);
    echo json_encode(['is_correct' => false, 'feedback' => $error_msg]);
    exit;
}

// Подробно логируем структуру ответа
log_ai("Response structure: " . print_r($result, true));

// Проверяем наличие ожидаемых полей в ответе
if (!isset($result['result']['alternatives'][0]['message']['text'])) {
    $error_msg = 'Неожиданный формат ответа API: отсутствуют ожидаемые поля';
    log_ai($error_msg . "\nResponse structure: " . print_r($result, true));
    http_response_code(500);
    echo json_encode(['is_correct' => false, 'feedback' => $error_msg]);
    exit;
}

$gpt_text = $result['result']['alternatives'][0]['message']['text'];
log_ai("GPT_TEXT: $gpt_text");

// Очищаем текст от обратных кавычек и маркеров форматирования
$cleaned_text = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $gpt_text);
log_ai("Cleaned text: $cleaned_text");

// Пытаемся распарсить JSON из очищенного ответа модели
$ai_result = json_decode($cleaned_text, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // Если не удалось распарсить, попробуем найти JSON в тексте
    if (preg_match('/\{.*"is_correct".*"feedback".*\}/s', $gpt_text, $matches)) {
        $json_text = $matches[0];
        log_ai("Extracted JSON: $json_text");
        $ai_result = json_decode($json_text, true);
    }
}

// Проверяем наличие необходимых полей в распарсенном JSON
if (is_array($ai_result) && isset($ai_result['is_correct']) && isset($ai_result['feedback'])) {
    log_ai("AI_RESULT: " . json_encode($ai_result, JSON_UNESCAPED_UNICODE));
    echo json_encode([
        'is_correct' => (bool)$ai_result['is_correct'],
        'feedback' => $ai_result['feedback']
    ]);
    exit;
}

// Если не удалось распарсить JSON или отсутствуют нужные поля
if ($gpt_text) {
    $err = 'Ответ YandexGPT не содержит ожидаемых полей is_correct и feedback. Ответ модели: ' . $gpt_text;
    log_ai($err);
    echo json_encode([
        'is_correct' => false,
        'feedback' => $err
    ]);
    exit;
}

$err = 'Пустой ответ от YandexGPT.';
log_ai($err);
echo json_encode([
    'is_correct' => false,
    'feedback' => $err
]); 