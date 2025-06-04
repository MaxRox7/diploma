<?php
/**
 * Test script for Piston API integration
 * 
 * This script tests the code execution using the Piston API
 */

require_once 'config.php';
redirect_unauthenticated();

// Only allow admins to run this script
if (!is_admin()) {
    die('Access denied. Only administrators can run this script.');
}

// Function to test code execution
function test_execution($language, $code, $input = '') {
    $data = [
        'code' => $code,
        'language' => $language,
        'input' => $input,
        'timeout' => 5
    ];
    
    $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/code_executor.php');
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
    
    return [
        'http_code' => $info['http_code'],
        'response' => json_decode($response, true)
    ];
}

// Test cases
$tests = [
    [
        'name' => 'PHP Hello World',
        'language' => 'php',
        'code' => '<?php echo "Hello, World!";',
        'input' => '',
        'expected_output' => 'Hello, World!'
    ],
    [
        'name' => 'Python Hello World',
        'language' => 'python',
        'code' => 'print("Hello, World!")',
        'input' => '',
        'expected_output' => 'Hello, World!'
    ],
    [
        'name' => 'C++ Hello World',
        'language' => 'cpp',
        'code' => '#include <iostream>\nint main() {\n    std::cout << "Hello, World!";\n    return 0;\n}',
        'input' => '',
        'expected_output' => 'Hello, World!'
    ],
    [
        'name' => 'Python with Input',
        'language' => 'python',
        'code' => 'name = input()\nprint(f"Hello, {name}!")',
        'input' => 'User',
        'expected_output' => 'Hello, User!'
    ]
];

// Run tests
$results = [];
foreach ($tests as $test) {
    $result = test_execution($test['language'], $test['code'], $test['input']);
    $success = false;
    $output = '';
    $error = '';
    
    if ($result['http_code'] === 200 && isset($result['response']['output'])) {
        $output = $result['response']['output'];
        $error = $result['response']['error'] ?? '';
        $success = (trim($output) === trim($test['expected_output']));
    }
    
    $results[] = [
        'name' => $test['name'],
        'success' => $success,
        'output' => $output,
        'error' => $error,
        'raw_response' => $result
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piston API Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>
    <div class="ui container" style="margin-top: 50px;">
        <h1 class="ui header">Piston API Test Results</h1>
        
        <div class="ui segment">
            <h3 class="ui header">Test Results</h3>
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>Test Name</th>
                        <th>Status</th>
                        <th>Output</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr class="<?= $result['success'] ? 'positive' : 'negative' ?>">
                            <td><?= htmlspecialchars($result['name']) ?></td>
                            <td>
                                <?php if ($result['success']): ?>
                                    <i class="icon checkmark"></i> Success
                                <?php else: ?>
                                    <i class="icon close"></i> Failed
                                <?php endif; ?>
                            </td>
                            <td><pre><?= htmlspecialchars($result['output']) ?></pre></td>
                            <td><pre><?= htmlspecialchars($result['error']) ?></pre></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="ui segment">
            <h3 class="ui header">Raw API Responses</h3>
            <div class="ui styled accordion">
                <?php foreach ($results as $index => $result): ?>
                    <div class="title">
                        <i class="dropdown icon"></i>
                        <?= htmlspecialchars($result['name']) ?>
                    </div>
                    <div class="content">
                        <pre><?= htmlspecialchars(json_encode($result['raw_response'], JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="ui buttons">
            <a href="index.php" class="ui button">Back to Home</a>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.ui.accordion').accordion();
        });
    </script>
</body>
</html> 