<?php
// Тестовый скрипт для проверки работы ai_code_reviewer.php через терминал

$student_code = <<<'CODE'
<?php
function sum($arr) {
    $s = 0;
    foreach ($arr as $v) $s += $v;
    return $s;
}
echo sum([1,2,3]);
CODE;

$data = [
    'student_code' => $student_code,
    'expected_code' => '',
    'language' => 'php',
    'task_description' => 'Напишите функцию sum, которая возвращает сумму всех элементов массива.',
    'expected_output' => '6',
    'actual_output' => '6',
];

$url = 'http://localhost:81/ai_code_reviewer.php'; // Измени на свой адрес, если не localhost

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo "Ошибка запроса: $err\n";
    exit(1);
}

if ($info['http_code'] !== 200) {
    echo "HTTP ошибка: {$info['http_code']}\n";
    echo $response . "\n";
    exit(1);
}

$result = json_decode($response, true);
if (!$result) {
    echo "Ошибка парсинга ответа:\n$response\n";
    exit(1);
}

// Выводим результат
if (isset($result['is_correct'])) {
    echo "is_correct: ".($result['is_correct'] ? 'true' : 'false')."\n";
}
if (isset($result['feedback'])) {
    echo "AI feedback:\n" . $result['feedback'] . "\n";
} 