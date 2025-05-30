<?php
require_once 'config.php';
redirect_unauthenticated();

if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = (int)$_GET['id'];
$user_id = $_SESSION['user']['id_user'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Проверяем права доступа (должен быть создателем курса)
    $stmt = $pdo->prepare("
        SELECT c.*, cp.id_user
        FROM course c
        JOIN create_passes cp ON c.id_course = cp.id_course
        WHERE c.id_course = ? AND cp.id_user = ?
    ");
    $stmt->execute([$course_id, $user_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: courses.php');
        exit;
    }
    
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    try {
        // Получаем все пути к материалам курса
        $stmt = $pdo->prepare("
            SELECT m.path_matial 
            FROM Material m
            JOIN Steps s ON m.id_step = s.id_step
            JOIN lessons l ON s.id_lesson = l.id_lesson
            WHERE l.id_course = ? AND m.path_matial IS NOT NULL
        ");
        $stmt->execute([$course_id]);
        $materials = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Сохраняем корневую директорию курса (берем из первого материала)
        $course_root_dir = '';
        if (!empty($materials)) {
            $first_path = $materials[0];
            // Получаем путь до директории materials
            $path_parts = explode('/materials/', $first_path);
            if (count($path_parts) > 1) {
                $course_root_dir = 'materials/' . explode('/', $path_parts[1])[0];
            }
        }
        
        // Удаляем физические файлы
        foreach ($materials as $path) {
            if ($path && file_exists($path)) {
                unlink($path);
                
                // Удаляем родительскую директорию, если она пуста
                $dir = dirname($path);
                if (is_dir($dir) && count(scandir($dir)) == 2) { // 2 because of . and ..
                    rmdir($dir);
                }
            }
        }

        // Рекурсивно удаляем корневую директорию курса, если она существует
        if ($course_root_dir && is_dir($course_root_dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($course_root_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }
            
            rmdir($course_root_dir);
        }

        // Удаляем все связанные данные
        // 1. Удаляем ответы на тесты
        $stmt = $pdo->prepare("
            DELETE FROM Answers 
            WHERE id_question IN (
                SELECT q.id_question 
                FROM Questions q
                JOIN Tests t ON q.id_test = t.id_test
                JOIN Steps s ON t.id_step = s.id_step
                JOIN lessons l ON s.id_lesson = l.id_lesson
                WHERE l.id_course = ?
            )
        ");
        $stmt->execute([$course_id]);
        
        // 2. Удаляем результаты тестов
        $stmt = $pdo->prepare("
            DELETE FROM Results 
            WHERE id_test IN (
                SELECT t.id_test 
                FROM Tests t
                JOIN Steps s ON t.id_step = s.id_step
                JOIN lessons l ON s.id_lesson = l.id_lesson
                WHERE l.id_course = ?
            )
        ");
        $stmt->execute([$course_id]);
        
        // 3. Удаляем варианты ответов
        $stmt = $pdo->prepare("
            DELETE FROM Answer_options 
            WHERE id_question IN (
                SELECT q.id_question 
                FROM Questions q
                JOIN Tests t ON q.id_test = t.id_test
                JOIN Steps s ON t.id_step = s.id_step
                JOIN lessons l ON s.id_lesson = l.id_lesson
                WHERE l.id_course = ?
            )
        ");
        $stmt->execute([$course_id]);
        
        // 4. Удаляем вопросы
        $stmt = $pdo->prepare("
            DELETE FROM Questions 
            WHERE id_test IN (
                SELECT t.id_test 
                FROM Tests t
                JOIN Steps s ON t.id_step = s.id_step
                JOIN lessons l ON s.id_lesson = l.id_lesson
                WHERE l.id_course = ?
            )
        ");
        $stmt->execute([$course_id]);
        
        // 5. Удаляем тесты
        $stmt = $pdo->prepare("
            DELETE FROM Tests 
            WHERE id_step IN (
                SELECT s.id_step 
                FROM Steps s
                JOIN lessons l ON s.id_lesson = l.id_lesson
                WHERE l.id_course = ?
            )
        ");
        $stmt->execute([$course_id]);
        
        // 6. Удаляем материалы
        $stmt = $pdo->prepare("
            DELETE FROM Material 
            WHERE id_step IN (
                SELECT s.id_step 
                FROM Steps s
                JOIN lessons l ON s.id_lesson = l.id_lesson
                WHERE l.id_course = ?
            )
        ");
        $stmt->execute([$course_id]);
        
        // 7. Удаляем шаги
        $stmt = $pdo->prepare("
            DELETE FROM Steps 
            WHERE id_lesson IN (
                SELECT id_lesson 
                FROM lessons 
                WHERE id_course = ?
            )
        ");
        $stmt->execute([$course_id]);
        
        // 8. Удаляем уроки
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id_course = ?");
        $stmt->execute([$course_id]);
        
        // 9. Удаляем отзывы
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE id_course = ?");
        $stmt->execute([$course_id]);
        
        // 10. Удаляем записи на курс
        $stmt = $pdo->prepare("DELETE FROM create_passes WHERE id_course = ?");
        $stmt->execute([$course_id]);
        
        // 11. Наконец, удаляем сам курс
        $stmt = $pdo->prepare("DELETE FROM course WHERE id_course = ?");
        $stmt->execute([$course_id]);
        
        // Завершаем транзакцию
        $pdo->commit();
        
        // Перенаправляем на страницу курсов
        header('Location: courses.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Ошибка при удалении курса: ' . $e->getMessage();
    }
    
} catch (PDOException $e) {
    $error = 'Ошибка базы данных: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление курса - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>
<div class="ui container" style="margin-top: 50px;">
    <?php if ($error): ?>
        <div class="ui error message">
            <div class="header">Ошибка</div>
            <p><?= htmlspecialchars($error) ?></p>
            <a href="course.php?id=<?= $course_id ?>" class="ui button">Вернуться к курсу</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html> 