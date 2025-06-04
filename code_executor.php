<?php
/**
 * Code Executor for Programming Tasks
 * 
 * This file handles secure execution of code submitted by students
 * for programming tasks in PHP, Python, and C++.
 */

require_once 'config.php';
redirect_unauthenticated();

// Set execution time limit
ini_set('max_execution_time', 10);
set_time_limit(10);

/**
 * Execute code and return the result
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
    
    // Create a temporary directory with a random name
    $temp_dir = sys_get_temp_dir() . '/code_exec_' . uniqid();
    if (!mkdir($temp_dir, 0755, true)) {
        $result['error'] = 'Failed to create temporary directory';
        return $result;
    }
    
    // Start timing
    $start_time = microtime(true);
    
    try {
        switch ($language) {
            case 'php':
                $result = execute_php($code, $input, $temp_dir, $timeout);
                break;
                
            case 'python':
                $result = execute_python($code, $input, $temp_dir, $timeout);
                break;
                
            case 'cpp':
                $result = execute_cpp($code, $input, $temp_dir, $timeout);
                break;
                
            default:
                $result['error'] = 'Unsupported language';
        }
    } catch (Exception $e) {
        $result['error'] = 'Execution error: ' . $e->getMessage();
    } finally {
        // Clean up temporary directory
        array_map('unlink', glob("$temp_dir/*"));
        rmdir($temp_dir);
    }
    
    // Calculate execution time
    $result['execution_time'] = microtime(true) - $start_time;
    
    return $result;
}

/**
 * Execute PHP code
 */
function execute_php($code, $input, $temp_dir, $timeout) {
    $result = [
        'output' => '',
        'error' => '',
        'success' => false
    ];
    
    // Create PHP file with the code
    $file_path = $temp_dir . '/code.php';
    
    // Check if code already contains PHP tags
    $has_php_tags = (stripos($code, '<?php') !== false);
    
    // Wrap code in a function to prevent global scope pollution
    if ($has_php_tags) {
        // If code already has PHP tags, use it directly
        $wrapped_code = $code;
    } else {
        // Otherwise wrap it in PHP tags
        $wrapped_code = "<?php
// Disable dangerous functions
ini_set('disable_functions', 'exec,passthru,shell_exec,system,proc_open,popen,curl_exec,parse_ini_file,show_source,dl,mail');

// Capture output
ob_start();

// Run the code
try {
    // User code starts here
$code
    // User code ends here
} catch (Throwable \$e) {
    echo \"Error: \" . \$e->getMessage();
}

// Get output
\$output = ob_get_clean();
echo \$output;
?>";
    }
    
    file_put_contents($file_path, $wrapped_code);
    
    // Execute with process control
    $cmd = "php -f " . escapeshellarg($file_path);
    if (!empty($input)) {
        $input_file = $temp_dir . '/input.txt';
        file_put_contents($input_file, $input);
        $cmd .= " < " . escapeshellarg($input_file);
    }
    
    $descriptors = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    $process = proc_open($cmd, $descriptors, $pipes);
    if (is_resource($process)) {
        // Set streams to non-blocking
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        
        // Set timeout
        $start = time();
        $output = '';
        $error = '';
        
        do {
            $status = proc_get_status($process);
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            
            // Check if process is still running
            if (!$status['running']) {
                break;
            }
            
            // Check timeout
            if (time() - $start > $timeout) {
                proc_terminate($process);
                $error .= "Execution timed out after {$timeout} seconds";
                break;
            }
            
            usleep(100000); // Sleep for 100ms to reduce CPU usage
        } while (true);
        
        // Close pipes and process
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        $result['output'] = $output;
        $result['error'] = $error;
        $result['success'] = empty($error);
    } else {
        $result['error'] = 'Failed to execute PHP code';
    }
    
    return $result;
}

/**
 * Execute Python code
 */
function execute_python($code, $input, $temp_dir, $timeout) {
    $result = [
        'output' => '',
        'error' => '',
        'success' => false
    ];
    
    // Create Python file with the code
    $file_path = $temp_dir . '/code.py';
    file_put_contents($file_path, $code);
    
    // Execute with process control
    $cmd = "python3 " . escapeshellarg($file_path);
    if (!empty($input)) {
        $input_file = $temp_dir . '/input.txt';
        file_put_contents($input_file, $input);
        $cmd .= " < " . escapeshellarg($input_file);
    }
    
    $descriptors = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    $process = proc_open($cmd, $descriptors, $pipes);
    if (is_resource($process)) {
        // Set streams to non-blocking
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        
        // Set timeout
        $start = time();
        $output = '';
        $error = '';
        
        do {
            $status = proc_get_status($process);
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            
            // Check if process is still running
            if (!$status['running']) {
                break;
            }
            
            // Check timeout
            if (time() - $start > $timeout) {
                proc_terminate($process);
                $error .= "Execution timed out after {$timeout} seconds";
                break;
            }
            
            usleep(100000); // Sleep for 100ms to reduce CPU usage
        } while (true);
        
        // Close pipes and process
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        $result['output'] = $output;
        $result['error'] = $error;
        $result['success'] = empty($error);
    } else {
        $result['error'] = 'Failed to execute Python code';
    }
    
    return $result;
}

/**
 * Execute C++ code
 */
function execute_cpp($code, $input, $temp_dir, $timeout) {
    $result = [
        'output' => '',
        'error' => '',
        'success' => false
    ];
    
    // Create C++ file with the code
    $file_path = $temp_dir . '/code.cpp';
    file_put_contents($file_path, $code);
    
    // Compile the code
    $executable = $temp_dir . '/code';
    $compile_cmd = "g++ -std=c++11 " . escapeshellarg($file_path) . " -o " . escapeshellarg($executable);
    
    exec($compile_cmd . " 2>&1", $compile_output, $compile_return);
    
    if ($compile_return !== 0) {
        $result['error'] = "Compilation error:\n" . implode("\n", $compile_output);
        return $result;
    }
    
    // Execute with process control
    $cmd = $executable;
    if (!empty($input)) {
        $input_file = $temp_dir . '/input.txt';
        file_put_contents($input_file, $input);
        $cmd .= " < " . escapeshellarg($input_file);
    }
    
    $descriptors = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    $process = proc_open($cmd, $descriptors, $pipes);
    if (is_resource($process)) {
        // Set streams to non-blocking
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        
        // Set timeout
        $start = time();
        $output = '';
        $error = '';
        
        do {
            $status = proc_get_status($process);
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            
            // Check if process is still running
            if (!$status['running']) {
                break;
            }
            
            // Check timeout
            if (time() - $start > $timeout) {
                proc_terminate($process);
                $error .= "Execution timed out after {$timeout} seconds";
                break;
            }
            
            usleep(100000); // Sleep for 100ms to reduce CPU usage
        } while (true);
        
        // Close pipes and process
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        $result['output'] = $output;
        $result['error'] = $error;
        $result['success'] = empty($error);
    } else {
        $result['error'] = 'Failed to execute C++ code';
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