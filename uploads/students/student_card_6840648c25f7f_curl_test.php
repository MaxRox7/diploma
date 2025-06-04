<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// API-ключ Яндекс GPT
$api_key = 'AQVNyhcT-k2ec-45ag_8yiZXy9MPuSuhH7ARRFWd';

echo "<h1>Тест API Яндекс GPT через curl</h1>";
echo "<pre>";

// Формируем данные запроса
$data = [
    "modelUri" => "gpt://b1gr2hqj20frbeet0cet/yandexgpt-lite",
    "completionOptions" => [
        "stream" => false,
        "temperature" => 0.6,
        "maxTokens" => 2000
    ],
    "messages" => [
        [
            "role" => "user",
            "text" => "КАК ПРОГАММИРОВАТЬ НА PHP?"
        ]
    ]
];

// URL API Яндекс GPT
$url = 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion';

// Инициализируем cURL
$ch = curl_init($url);

// Настраиваем параметры запроса
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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

echo "Отправка запроса к API...\n";
echo "URL: " . $url . "\n";
echo "Данные запроса: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Выполняем запрос
$response = curl_exec($ch);

// Получаем информацию о запросе
$info = curl_getinfo($ch);
echo "HTTP-код: " . $info['http_code'] . "\n";
echo "Время запроса: " . $info['total_time'] . " сек\n\n";

// Проверяем на ошибки
if (curl_errno($ch)) {
    echo "Ошибка curl: " . curl_error($ch) . "\n";
} else {
    echo "Получен ответ:\n";
    
    // Проверяем, не является ли ответ HTML
    if (strpos($response, '<!DOCTYPE html>') !== false || 
        strpos($response, '<html>') !== false || 
        strpos($response, '<br') !== false) {
        
        echo "ВНИМАНИЕ: Получен HTML вместо JSON!\n";
        echo "Первые 500 символов ответа:\n";
        echo htmlspecialchars(substr($response, 0, 500)) . "\n\n";
        
        echo "Полный ответ (закодированный):\n";
        echo htmlspecialchars($response) . "\n\n";
    } else {
        echo $response . "\n\n";
        
        // Декодируем JSON-ответ
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Ошибка декодирования JSON: " . json_last_error_msg() . "\n";
            echo "Первые 500 символов ответа:\n";
            echo htmlspecialchars(substr($response, 0, 500)) . "\n\n";
        } else if (isset($result['alternatives'][0]['message']['text'])) {
            echo "Ответ модели: " . $result['alternatives'][0]['message']['text'] . "\n";
        } else {
            echo "Структура ответа:\n";
            print_r($result);
        }
    }
}

// Закрываем соединение
curl_close($ch);

echo "</pre>";
?> 