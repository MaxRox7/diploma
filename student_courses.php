<?php
require_once 'config.php';
redirect_unauthenticated();

// Only students can access this page
if (!is_student()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user']['id_user'];
$pdo = get_db_connection();
$error = '';
$success = '';

// Get all courses enrolled by this student
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cp.date_complete,
               (SELECT COUNT(*) FROM lessons WHERE id_course = c.id_course) as lessons_count,
               (SELECT COUNT(*) 
                FROM user_material_progress ump 
                JOIN Steps s ON ump.id_step = s.id_step 
                JOIN lessons l ON s.id_lesson = l.id_lesson 
                WHERE l.id_course = c.id_course AND ump.id_user = ?) as completed_materials,
               (SELECT COUNT(*) 
                FROM test_attempts ta 
                JOIN Tests t ON ta.id_test = t.id_test 
                JOIN Steps s ON t.id_step = s.id_step 
                JOIN lessons l ON s.id_lesson = l.id_lesson 
                WHERE l.id_course = c.id_course AND ta.id_user = ? AND ta.status = 'completed' 
                AND ta.score >= (SELECT t2.passing_percentage * ta.max_score / 100 FROM Tests t2 WHERE t2.id_test = ta.id_test)) as completed_tests,
               (SELECT COUNT(*) 
                FROM Steps s 
                JOIN lessons l ON s.id_lesson = l.id_lesson 
                WHERE l.id_course = c.id_course) as total_steps
        FROM course c 
        JOIN create_passes cp ON c.id_course = cp.id_course 
        WHERE cp.id_user = ? AND cp.is_creator = false 
        ORDER BY c.id_course DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Ошибка при получении списка курсов: ' . $e->getMessage();
    $courses = [];
}

// Calculate progress for each course
foreach ($courses as &$course) {
    $total_steps = $course['total_steps'];
    $completed_steps = $course['completed_materials'] + $course['completed_tests'];
    $course['progress'] = $total_steps > 0 ? round(($completed_steps / $total_steps) * 100) : 0;
}
unset($course); // Break the reference
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои курсы - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <div class="ui clearing segment">
                <h2 class="ui left floated header">
                    Мои курсы
                    <div class="sub header">Курсы, на которые вы записаны</div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="courses.php" class="ui primary button">
                        <i class="search icon"></i> Найти новые курсы
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="ui success message">
                    <div class="header">Успех</div>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($courses)): ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="book icon"></i>
                        Вы пока не записаны ни на один курс
                    </div>
                    <a href="courses.php" class="ui primary button">Найти курсы</a>
                </div>
            <?php else: ?>
                <div class="ui three stackable cards">
                    <?php foreach ($courses as $course): ?>
                        <?php render_course_card($course, false, true); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.progress').progress();
});
</script>

</body>
</html> 