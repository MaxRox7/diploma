<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем права доступа (только для преподавателей и админов)
if (!is_teacher() && !is_admin()) {
    header('Location: index.php');
    exit;
}

$pdo = get_db_connection();

// Логирование результатов
$log_file = 'test_generate_options_log.txt';
$log_handle = fopen($log_file, 'a');
fwrite($log_handle, "=== " . date('Y-m-d H:i:s') . " ===\n");

// Функция для тестирования генерации вариантов
function test_generate_options($question_type, $question, $correct_answer, $num_options = 3) {
    global $log_handle;
    
    fwrite($log_handle, "Testing question type: $question_type\n");
    fwrite($log_handle, "Question: $question\n");
    fwrite($log_handle, "Correct answer: $correct_answer\n");
    fwrite($log_handle, "Number of options: $num_options\n");
    
    // Формируем данные запроса
    $data = [
        'question' => $question,
        'correct_answer' => $correct_answer,
        'num_options' => $num_options,
        'question_type' => $question_type
    ];
    
    // Отправляем запрос к API
    $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/generate_options.php');
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
    
    fwrite($log_handle, "HTTP Code: " . $info['http_code'] . "\n");
    fwrite($log_handle, "Response: " . $response . "\n\n");
    
    return json_decode($response, true);
}

// Тестируем разные типы вопросов
$tests = [
    [
        'type' => 'single',
        'question' => 'Какой язык программирования используется для веб-разработки на стороне сервера?',
        'correct_answer' => 'PHP',
        'num_options' => 2
    ],
    [
        'type' => 'multi',
        'question' => 'Какие из следующих языков программирования используются для веб-разработки?',
        'correct_answer' => 'JavaScript',
        'num_options' => 4
    ],
    [
        'type' => 'match',
        'question' => 'Сопоставьте языки программирования и их типичные области применения',
        'correct_answer' => 'PHP||Веб-разработка на стороне сервера',
        'num_options' => 3
    ]
];

$results = [];
foreach ($tests as $test) {
    $result = test_generate_options($test['type'], $test['question'], $test['correct_answer'], $test['num_options']);
    $results[] = [
        'test' => $test,
        'result' => $result
    ];
}

fwrite($log_handle, "=== END ===\n\n");
fclose($log_handle);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестирование генерации вариантов ответов</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>
    <div class="ui container" style="margin-top: 50px;">
        <h1 class="ui header">Тестирование генерации вариантов ответов</h1>
        
        <?php foreach ($results as $index => $data): ?>
            <div class="ui segment">
                <h3 class="ui header">Тест #<?= $index + 1 ?>: <?= htmlspecialchars($data['test']['type']) ?></h3>
                <p><strong>Вопрос:</strong> <?= htmlspecialchars($data['test']['question']) ?></p>
                <p><strong>Правильный ответ:</strong> <?= htmlspecialchars($data['test']['correct_answer']) ?></p>
                <p><strong>Запрошено вариантов:</strong> <?= htmlspecialchars($data['test']['num_options']) ?></p>
                
                <?php if (isset($data['result']['success']) && $data['result']['success']): ?>
                    <div class="ui success message">
                        <div class="header">Успешно сгенерированы варианты:</div>
                        <ul class="list">
                            <?php foreach ($data['result']['options'] as $option): ?>
                                <li><?= htmlspecialchars($option) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="ui error message">
                        <div class="header">Ошибка:</div>
                        <p><?= htmlspecialchars($data['result']['error'] ?? 'Неизвестная ошибка') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="ui segment">
            <p>Результаты тестирования также записаны в файл <?= htmlspecialchars($log_file) ?></p>
            <a href="edit_test.php?test_id=1" class="ui button">Вернуться к редактированию тестов</a>
        </div>
    </div>
</body>
</html> 