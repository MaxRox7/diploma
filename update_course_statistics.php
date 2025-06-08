<?php
require_once 'config.php';

try {
    $pdo = get_db_connection();
    
    // Обновляем статистику всех курсов
    echo "Начинаем обновление статистики курсов...\n";
    
    // Обновляем базовую статистику для всех курсов
    $stmt = $pdo->prepare("
        INSERT INTO course_statistics (id_course, views_count, enrollment_count, completion_count)
        SELECT 
            c.id_course,
            COALESCE((SELECT COUNT(*) FROM course_views WHERE id_course = c.id_course), 0) as views_count,
            COUNT(DISTINCT cp.id_user) as enrollment_count,
            COUNT(DISTINCT CASE WHEN cp.date_complete IS NOT NULL THEN cp.id_user END) as completion_count
        FROM course c
        LEFT JOIN create_passes cp ON c.id_course = cp.id_course
        GROUP BY c.id_course
        ON CONFLICT (id_course) DO UPDATE SET
            enrollment_count = EXCLUDED.enrollment_count,
            completion_count = EXCLUDED.completion_count,
            last_updated = CURRENT_TIMESTAMP
    ");
    $stmt->execute();
    echo "Базовая статистика курсов обновлена\n";
    
    // Обновляем средний рейтинг курсов
    $stmt = $pdo->prepare("
        UPDATE course_statistics cs SET
            average_rating = (
                SELECT AVG(CAST(f.rate_feedback AS FLOAT))
                FROM feedback f
                WHERE f.id_course = cs.id_course
            ),
            last_updated = CURRENT_TIMESTAMP
        WHERE EXISTS (
            SELECT 1 FROM feedback f WHERE f.id_course = cs.id_course
        )
    ");
    $stmt->execute();
    echo "Рейтинги курсов обновлены\n";
    
    // Обновляем статистику обучения для всех студентов
    $stmt = $pdo->prepare("
        SELECT DISTINCT cp.id_user, cp.id_course
        FROM create_passes cp
        WHERE cp.id_user IS NOT NULL AND cp.id_course IS NOT NULL
    ");
    $stmt->execute();
    $enrollments = $stmt->fetchAll();
    
    echo "Обновляем статистику для " . count($enrollments) . " записей на курсы...\n";
    
    foreach ($enrollments as $enrollment) {
        $user_id = $enrollment['id_user'];
        $course_id = $enrollment['id_course'];
        
        // Обновляем аналитику обучения
        $stmt = $pdo->prepare("
            INSERT INTO student_analytics (
                id_user, id_course, 
                lessons_completed, total_lessons, 
                tests_completed, total_tests, 
                average_test_score, last_activity
            )
            VALUES (
                ?, ?,
                (
                    SELECT COUNT(DISTINCT ump.id_step)
                    FROM user_material_progress ump
                    JOIN steps s ON ump.id_step = s.id_step
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE ump.id_user = ? AND l.id_course = ? AND s.type_step = 'material'
                ),
                (
                    SELECT COUNT(*)
                    FROM steps s
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE l.id_course = ? AND s.type_step = 'material'
                ),
                (
                    SELECT COUNT(DISTINCT ta.id_test)
                    FROM test_attempts ta
                    JOIN tests t ON ta.id_test = t.id_test
                    JOIN steps s ON t.id_step = s.id_step
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE ta.id_user = ? AND l.id_course = ? AND ta.status = 'completed'
                ),
                (
                    SELECT COUNT(*)
                    FROM steps s
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE l.id_course = ? AND s.type_step = 'test'
                ),
                (
                    SELECT COALESCE(AVG(ta.score * 100.0 / ta.max_score), 0)
                    FROM test_attempts ta
                    JOIN tests t ON ta.id_test = t.id_test
                    JOIN steps s ON t.id_step = s.id_step
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE ta.id_user = ? AND l.id_course = ? AND ta.status = 'completed'
                ),
                NOW()
            )
            ON CONFLICT (id_user, id_course) DO UPDATE SET
                lessons_completed = (
                    SELECT COUNT(DISTINCT ump.id_step)
                    FROM user_material_progress ump
                    JOIN steps s ON ump.id_step = s.id_step
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE ump.id_user = ? AND l.id_course = ? AND s.type_step = 'material'
                ),
                total_lessons = (
                    SELECT COUNT(*)
                    FROM steps s
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE l.id_course = ? AND s.type_step = 'material'
                ),
                tests_completed = (
                    SELECT COUNT(DISTINCT ta.id_test)
                    FROM test_attempts ta
                    JOIN tests t ON ta.id_test = t.id_test
                    JOIN steps s ON t.id_step = s.id_step
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE ta.id_user = ? AND l.id_course = ? AND ta.status = 'completed'
                ),
                total_tests = (
                    SELECT COUNT(*)
                    FROM steps s
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE l.id_course = ? AND s.type_step = 'test'
                ),
                average_test_score = (
                    SELECT COALESCE(AVG(ta.score * 100.0 / ta.max_score), 0)
                    FROM test_attempts ta
                    JOIN tests t ON ta.id_test = t.id_test
                    JOIN steps s ON t.id_step = s.id_step
                    JOIN lessons l ON s.id_lesson = l.id_lesson
                    WHERE ta.id_user = ? AND l.id_course = ? AND ta.status = 'completed'
                ),
                last_activity = NOW()
        ");
        $stmt->execute([
            $user_id, $course_id, 
            $user_id, $course_id, 
            $course_id, 
            $user_id, $course_id, 
            $course_id, 
            $user_id, $course_id, 
            $course_id, 
            $user_id, $course_id, 
            $course_id, 
            $user_id, $course_id
        ]);
    }
    
    echo "Статистика обучения студентов обновлена\n";
    echo "Обновление статистики успешно завершено!\n";
    
} catch (PDOException $e) {
    echo "Ошибка при обновлении статистики: " . $e->getMessage() . "\n";
} 