<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT name_tag FROM tags ORDER BY name_tag");
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($tags);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
} 