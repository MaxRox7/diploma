<?php
/**
 * Apply Code Execution SQL Changes
 * 
 * This script applies the necessary SQL changes to support code execution
 * for programming tasks in the test system.
 */

require_once 'config.php';
redirect_unauthenticated();

// Only allow admins to run this script
if (!is_admin()) {
    die('Access denied. Only administrators can run this script.');
}

$pdo = get_db_connection();
$sql_file = 'add_code_execution.sql';

if (!file_exists($sql_file)) {
    die("SQL file not found: $sql_file");
}

$sql = file_get_contents($sql_file);
$queries = explode(';', $sql);

$success = true;
$messages = [];

try {
    $pdo->beginTransaction();
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) {
            continue;
        }
        
        try {
            $pdo->exec($query);
            $messages[] = "Successfully executed: " . substr($query, 0, 50) . "...";
        } catch (PDOException $e) {
            $messages[] = "Error executing query: " . $e->getMessage() . " Query: " . substr($query, 0, 50) . "...";
            $success = false;
        }
    }
    
    if ($success) {
        $pdo->commit();
        $messages[] = "All SQL changes applied successfully.";
    } else {
        $pdo->rollBack();
        $messages[] = "Some SQL changes failed. Rolling back changes.";
    }
} catch (PDOException $e) {
    $success = false;
    $messages[] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Code Execution SQL Changes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>
    <div class="ui container" style="margin-top: 50px;">
        <h1 class="ui header">Apply Code Execution SQL Changes</h1>
        
        <div class="ui <?= $success ? 'success' : 'error' ?> message">
            <div class="header"><?= $success ? 'Success' : 'Error' ?></div>
            <p><?= $success ? 'SQL changes applied successfully.' : 'Failed to apply SQL changes.' ?></p>
        </div>
        
        <div class="ui segment">
            <h3 class="ui header">Execution Log</h3>
            <div class="ui list">
                <?php foreach ($messages as $message): ?>
                    <div class="item"><?= htmlspecialchars($message) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="ui segment">
            <h3 class="ui header">Next Steps</h3>
            <div class="ui list">
                <div class="item">1. Make sure the <code>code_executor.php</code> file has proper permissions.</div>
                <div class="item">2. Test code execution with different programming languages.</div>
                <div class="item">3. Create some test questions with code tasks.</div>
            </div>
        </div>
        
        <div class="ui buttons">
            <a href="index.php" class="ui button">Back to Home</a>
        </div>
    </div>
</body>
</html> 