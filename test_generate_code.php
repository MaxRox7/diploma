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
$log_file = 'test_generate_code_log.txt';
$log_handle = fopen($log_file, 'a');
fwrite($log_handle, "=== " . date('Y-m-d H:i:s') . " ===\n");

// Функция для тестирования генерации шаблонов кода
function test_generate_code($question, $language, $difficulty = 'medium', $input_example = '') {
    global $log_handle;
    
    fwrite($log_handle, "Testing code generation\n");
    fwrite($log_handle, "Question: $question\n");
    fwrite($log_handle, "Language: $language\n");
    fwrite($log_handle, "Difficulty: $difficulty\n");
    if (!empty($input_example)) {
        fwrite($log_handle, "Input example: $input_example\n");
    }
    
    // Формируем данные запроса
    $data = [
        'question' => $question,
        'language' => $language,
        'difficulty' => $difficulty
    ];
    
    if (!empty($input_example)) {
        $data['input_example'] = $input_example;
    }
    
    // Отправляем запрос к API
    $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/generate_code_template.php');
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

// Тестируем разные языки и сложности
$tests = [
    [
        'question' => 'Напишите функцию, которая находит сумму всех элементов в массиве.',
        'language' => 'php',
        'difficulty' => 'easy'
    ],
    [
        'question' => 'Напишите программу для проверки, является ли строка палиндромом.',
        'language' => 'python',
        'difficulty' => 'medium',
        'input_example' => 'level'
    ],
    [
        'question' => 'Реализуйте алгоритм сортировки слиянием для массива целых чисел.',
        'language' => 'cpp',
        'difficulty' => 'hard',
        'input_example' => '[5, 2, 9, 1, 7, 6, 3]'
    ]
];

$results = [];
foreach ($tests as $test) {
    $input_example = $test['input_example'] ?? '';
    $result = test_generate_code($test['question'], $test['language'], $test['difficulty'], $input_example);
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
    <title>Тестирование генерации шаблонов кода</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <style>
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 14px;
        }
        .language-label {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }
        .language-php { background-color: #8892BF; }
        .language-python { background-color: #3776AB; }
        .language-cpp { background-color: #00599C; }
    </style>
</head>
<body>
    <div class="ui container" style="margin-top: 50px;">
        <h1 class="ui header">Тестирование генерации шаблонов кода</h1>
        
        <?php foreach ($results as $index => $data): ?>
            <div class="ui segment">
                <h3 class="ui header">
                    Тест #<?= $index + 1 ?>: 
                    <span class="language-label language-<?= htmlspecialchars($data['test']['language']) ?>">
                        <?= strtoupper(htmlspecialchars($data['test']['language'])) ?>
                    </span>
                    <span class="ui label"><?= htmlspecialchars(ucfirst($data['test']['difficulty'])) ?></span>
                </h3>
                <p><strong>Задача:</strong> <?= htmlspecialchars($data['test']['question']) ?></p>
                
                <?php if (!empty($data['test']['input_example'])): ?>
                    <p><strong>Пример входных данных:</strong> <?= htmlspecialchars($data['test']['input_example']) ?></p>
                <?php endif; ?>
                
                <?php if (isset($data['result']['success']) && $data['result']['success']): ?>
                    <div class="ui success message">
                        <div class="header">Успешно сгенерирован код:</div>
                    </div>
                    
                    <h4>Шаблон кода:</h4>
                    <pre><code><?= htmlspecialchars($data['result']['template']) ?></code></pre>
                    
                    <h4>Решение:</h4>
                    <pre><code><?= htmlspecialchars($data['result']['solution']) ?></code></pre>
                    
                    <h4>Ожидаемый вывод:</h4>
                    <pre><code><?= htmlspecialchars($data['result']['output']) ?></code></pre>
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