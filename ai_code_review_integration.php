<?php
/**
 * AI Code Review Integration
 * 
 * This file contains functions to integrate AI code review with test submissions.
 */

/**
 * Get AI feedback for a code submission
 * 
 * @param string $code The student's code
 * @param array $code_task The code task details
 * @param string $task_description The description of the task
 * @return array The AI feedback and correctness assessment
 */
function get_ai_code_feedback($code, $code_task, $task_description) {
    // Default values
    $result = [
        'ai_feedback' => "Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.",
        'ai_is_correct' => true
    ];
    
    // Prepare data for AI review
    $ai_review_data = [
        'student_code' => $code,
        'expected_code' => $code_task['template_code'] ?? '',
        'language' => $code_task['language'],
        'task_description' => $task_description,
        'expected_output' => $code_task['output_ct'],
        'actual_output' => $code_task['actual_output'] ?? ''
    ];
    
    // Log the data being sent
    error_log("AI review data: " . json_encode($ai_review_data));
    
    // Call the AI code reviewer
    $ai_ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/ai_code_reviewer.php');
    curl_setopt($ai_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ai_ch, CURLOPT_POST, true);
    curl_setopt($ai_ch, CURLOPT_POSTFIELDS, json_encode($ai_review_data));
    curl_setopt($ai_ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ai_ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    curl_setopt($ai_ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    
    $ai_response = curl_exec($ai_ch);
    $ai_info = curl_getinfo($ai_ch);
    curl_close($ai_ch);
    
    // Log the response
    error_log("AI response status: " . $ai_info['http_code']);
    error_log("AI response: " . substr($ai_response, 0, 1000)); // Log only first 1000 characters
    
    // Process the response
    if ($ai_info['http_code'] === 200) {
        $ai_result = json_decode($ai_response, true);
        if ($ai_result && isset($ai_result['feedback'])) {
            $result['ai_feedback'] = $ai_result['feedback'];
            $result['ai_is_correct'] = $ai_result['is_correct'] ?? true;
        }
    } else {
        error_log("AI API error: HTTP " . $ai_info['http_code']);
    }
    
    return $result;
}

/**
 * Save AI feedback to the database
 * 
 * @param int $attempt_id The test attempt ID
 * @param int $question_id The question ID
 * @param string $feedback The AI feedback
 * @return bool Whether the save was successful
 */
function save_ai_feedback($attempt_id, $question_id, $feedback) {
    try {
        $pdo = get_db_connection();
        
        // Ensure the ai_feedback column exists
        $pdo->exec("ALTER TABLE test_answers ADD COLUMN IF NOT EXISTS ai_feedback TEXT");
        
        // Update the test answer with AI feedback
        $stmt = $pdo->prepare("
            UPDATE test_answers 
            SET ai_feedback = ? 
            WHERE id_attempt = ? AND id_question = ?
        ");
        $stmt->execute([$feedback, $attempt_id, $question_id]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error saving AI feedback: " . $e->getMessage());
        return false;
    }
}

/**
 * Get AI feedback for a test answer
 * 
 * @param int $answer_id The test answer ID
 * @return string|null The AI feedback or null if not found
 */
function get_ai_feedback($answer_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT ai_feedback FROM test_answers WHERE id_answer = ?");
        $stmt->execute([$answer_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['ai_feedback'] : null;
    } catch (Exception $e) {
        error_log("Error getting AI feedback: " . $e->getMessage());
        return null;
    }
} 