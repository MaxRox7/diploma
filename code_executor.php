<?php
/**
 * Code Executor for Programming Tasks using Piston API
 * 
 * This file handles secure execution of code submitted by students
 * for programming tasks in PHP, Python, and C++.
 * Using Piston API (https://github.com/engineer-man/piston)
 */

require_once 'config.php';
redirect_unauthenticated();

// Set execution time limit for API requests
ini_set('max_execution_time', 30);
set_time_limit(30);

/**
 * Execute code using Piston API and return the result
 * 
 * @param string $code The code to execute
 * @param string $language The programming language (php, python, cpp)
 * @param string $input Optional input for the program
 * @param int $timeout Maximum execution time in seconds
 * @return array Result with output, error, and execution status
 */
function execute_code($code, $language, $input = '', $timeout = 5) {
    $result = [
        'output' => '',
        'error' => '',
        'success' => false,
        'execution_time' => 0
    ];
    
    // Start timing
    $start_time = microtime(true);
    
    try {
        // Map our language names to Piston language codes and versions
        $language_map = [
            'php' => ['language' => 'php', 'version' => '8.2.3'],
            'python' => ['language' => 'python', 'version' => '3.10.0'],
            'cpp' => ['language' => 'c++', 'version' => '10.2.0'],
            'c++' => ['language' => 'c++', 'version' => '10.2.0']
        ];
        
        // Check if language is supported
        if (!isset($language_map[$language])) {
            $result['error'] = 'Unsupported language: ' . $language;
            return $result;
        }
        
        $piston_language = $language_map[$language]['language'];
        $piston_version = $language_map[$language]['version'];
        
        // Piston API base URL - using the public instance
        $api_url = 'https://emkc.org/api/v2/piston/execute';
        
        // Prepare the request data
        $post_data = [
            'language' => $piston_language,
            'version' => $piston_version,
            'files' => [
                [
                    'name' => 'main.' . ($piston_language === 'c++' ? 'cpp' : $piston_language),
                    'content' => $code
                ]
            ],
            'stdin' => $input,
            'args' => [],
            'compile_timeout' => $timeout * 1000, // milliseconds
            'run_timeout' => $timeout * 1000,     // milliseconds
            'compile_memory_limit' => -1,         // no limit
            'run_memory_limit' => -1              // no limit
        ];
        
        // Initialize cURL session
        $ch = curl_init($api_url);
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout + 5); // Add 5 seconds for API overhead
        
        // Execute the API request
        $response = curl_exec($ch);
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            $result['error'] = 'API request error: ' . curl_error($ch);
            curl_close($ch);
            return $result;
        }
        
        // Get HTTP status code
        $http_code = curl_getinfo($ch);
        curl_close($ch);
        
        // Parse the API response
        $api_result = json_decode($response, true);
        
        if ($http_code['http_code'] != 200) {
            $result['error'] = 'API error: ' . ($api_result['message'] ?? json_encode($api_result));
            return $result;
        }
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['error'] = 'Invalid API response: ' . json_last_error_msg();
            return $result;
        }
        
        // Process the API response
        if (isset($api_result['run'])) {
            // Set output from the run result
            $result['output'] = $api_result['run']['stdout'] ?? '';
            
            // Check for errors in stderr
            if (!empty($api_result['run']['stderr'])) {
                $result['error'] = $api_result['run']['stderr'];
            }
            
            // Check for compilation errors
            if (isset($api_result['compile']) && !empty($api_result['compile']['stderr'])) {
                if (!empty($result['error'])) {
                    $result['error'] .= "\n\nCompilation errors:\n";
                }
                $result['error'] .= $api_result['compile']['stderr'];
            }
            
            // Set execution time
            if (isset($api_result['run']['time'])) {
                $result['execution_time'] = floatval($api_result['run']['time']) / 1000; // Convert ms to seconds
            }
            
            // Set success flag based on exit code
            $exit_code = $api_result['run']['code'] ?? -1;
            $result['success'] = ($exit_code === 0 && empty($result['error']));
            
            // If exit code is not 0 but no error message, add a generic one
            if ($exit_code !== 0 && empty($result['error'])) {
                $result['error'] = 'Program exited with code ' . $exit_code;
            }
        } else {
            $result['error'] = 'Invalid API response format';
        }
    } catch (Exception $e) {
        $result['error'] = 'Execution error: ' . $e->getMessage();
    }
    
    // If API didn't provide execution time, calculate it locally
    if ($result['execution_time'] == 0) {
        $result['execution_time'] = microtime(true) - $start_time;
    }
    
    return $result;
}

// API endpoint for code execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }
    
    // Validate required fields
    if (!isset($data['code']) || !isset($data['language'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    // Execute code
    $result = execute_code(
        $data['code'],
        $data['language'],
        $data['input'] ?? '',
        $data['timeout'] ?? 5
    );
    
    // Return result as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?> 