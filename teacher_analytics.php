<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем, что пользователь является преподавателем
if (!is_teacher()) {
    header("Location: index.php");
    exit;
}

$error = '';
$user_id = $_SESSION['user']['id_user'];

try {
    $pdo = get_db_connection();
    
    // Инициализируем переменные значениями по умолчанию
    $general_stats = [
        'total_courses' => 0,
        'total_students' => 0,
        'total_hours' => 0
    ];
    $courses_stats = [];
    $tests_stats = [];
    $students_progress = [];
    
    try {
        // Получаем общую статистику преподавателя
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT c.id_course) as total_courses,
                COUNT(DISTINCT cp.id_user) as total_students,
                SUM(CAST(c.hourse_course AS INTEGER)) as total_hours
            FROM course c
            JOIN create_passes cp1 ON c.id_course = cp1.id_course AND cp1.id_user = ? AND cp1.is_creator = true
            LEFT JOIN create_passes cp ON c.id_course = cp.id_course AND cp.is_creator = false
            WHERE c.status_course = 'approved'
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        if ($result) {
            $general_stats = $result;
        }
    } catch (PDOException $e) {
        // Продолжаем со значениями по умолчанию
    }
    
    try {
        // Получаем статистику по каждому курсу преподавателя
        $stmt = $pdo->prepare("
            SELECT 
                c.id_course,
                c.name_course,
                c.status_course,
                COUNT(DISTINCT cp.id_user) as students_count,
                COUNT(DISTINCT CASE WHEN cp.date_complete IS NOT NULL THEN cp.id_user END) as completed_count,
                AVG(CAST(f.rate_feedback AS INTEGER)) as average_rating,
                COUNT(DISTINCT f.id_feedback) as feedback_count
            FROM course c
            JOIN create_passes cp1 ON c.id_course = cp1.id_course AND cp1.id_user = ? AND cp1.is_creator = true
            LEFT JOIN create_passes cp ON c.id_course = cp.id_course AND cp.is_creator = false
            LEFT JOIN feedback f ON c.id_course = f.id_course
            GROUP BY c.id_course, c.name_course, c.status_course
            ORDER BY students_count DESC
        ");
        $stmt->execute([$user_id]);
        $courses_stats = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Продолжаем с пустым массивом
    }
    
    try {
        // Получаем статистику по результатам тестов в курсах
        $stmt = $pdo->prepare("
            SELECT 
                c.id_course,
                c.name_course,
                t.id_test,
                t.name_test,
                COUNT(DISTINCT ta.id_attempt) as attempts_count,
                AVG(ta.score) as average_score,
                AVG(ta.score * 100.0 / NULLIF(ta.max_score, 0)) as average_percentage
            FROM course c
            JOIN create_passes cp ON c.id_course = cp.id_course AND cp.id_user = ? AND cp.is_creator = true
            JOIN lessons l ON c.id_course = l.id_course
            JOIN steps s ON l.id_lesson = s.id_lesson
            JOIN tests t ON s.id_step = t.id_step
            LEFT JOIN test_attempts ta ON t.id_test = ta.id_test AND ta.status = 'completed'
            GROUP BY c.id_course, c.name_course, t.id_test, t.name_test
            ORDER BY c.name_course, t.name_test
        ");
        $stmt->execute([$user_id]);
        $tests_stats = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Продолжаем с пустым массивом
    }
    
    try {
        // Получаем список студентов с их прогрессом по курсам
        $stmt = $pdo->prepare("
            SELECT 
                u.id_user,
                u.fn_user,
                u.login_user,
                c.id_course,
                c.name_course,
                COUNT(DISTINCT s.id_step) as total_steps,
                COUNT(DISTINCT CASE WHEN ump.id_user IS NOT NULL OR ta.id_user IS NOT NULL THEN s.id_step END) as completed_steps,
                MAX(ta.end_time) as last_activity,
                cp.date_complete
            FROM users u
            JOIN create_passes cp ON u.id_user = cp.id_user AND cp.is_creator = false
            JOIN course c ON cp.id_course = c.id_course
            JOIN create_passes teacher_cp ON c.id_course = teacher_cp.id_course AND teacher_cp.id_user = ? AND teacher_cp.is_creator = true
            LEFT JOIN lessons l ON c.id_course = l.id_course
            LEFT JOIN steps s ON l.id_lesson = s.id_lesson
            LEFT JOIN user_material_progress ump ON s.id_step = ump.id_step AND ump.id_user = u.id_user
            LEFT JOIN tests t ON s.id_step = t.id_step
            LEFT JOIN test_attempts ta ON t.id_test = ta.id_test AND ta.id_user = u.id_user AND ta.status = 'completed'
            GROUP BY u.id_user, u.fn_user, u.login_user, c.id_course, c.name_course, cp.date_complete
            ORDER BY c.name_course, u.fn_user
        ");
        $stmt->execute([$user_id]);
        $students_progress = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Продолжаем с пустым массивом
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
    <title>Аналитика преподавателя - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <h1 class="ui header">
                <i class="chart bar icon"></i>
                <div class="content">
                    Аналитика преподавателя
                    <div class="sub header">Отслеживайте статистику по вашим курсам и студентам</div>
                </div>
            </h1>
            
            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Общая статистика -->
            <div class="ui three small statistics" style="margin-bottom: 30px;">
                <div class="statistic">
                    <div class="value"><?= $general_stats['total_courses'] ?: 0 ?></div>
                    <div class="label">Курсов создано</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $general_stats['total_students'] ?: 0 ?></div>
                    <div class="label">Всего студентов</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $general_stats['total_hours'] ?: 0 ?></div>
                    <div class="label">Общая продолжительность (часов)</div>
                </div>
            </div>
            
            <div class="ui stackable grid">
                <!-- Статистика по курсам -->
                <div class="sixteen wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Статистика по курсам</h3>
                        <?php if (empty($courses_stats)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                У вас пока нет созданных курсов
                            </div>
                        <?php else: ?>
                            <table class="ui celled table">
                                <thead>
                                    <tr>
                                        <th>Название курса</th>
                                        <th>Статус</th>
                                        <th>Количество студентов</th>
                                        <th>Завершили курс</th>
                                        <th>Средняя оценка</th>
                                        <th>Отзывы</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses_stats as $course): ?>
                                        <?php 
                                        $status_labels = [
                                            'draft' => ['Черновик', 'grey'],
                                            'pending' => ['На модерации', 'yellow'],
                                            'correction' => ['Требует доработки', 'orange'],
                                            'approved' => ['Одобрен', 'green'],
                                            'rejected' => ['Отклонен', 'red']
                                        ];
                                        $status_info = $status_labels[$course['status_course']] ?? ['Неизвестно', 'black'];
                                        ?>
                                        <tr>
                                            <td><a href="course.php?id=<?= $course['id_course'] ?>"><?= htmlspecialchars($course['name_course']) ?></a></td>
                                            <td><div class="ui <?= $status_info[1] ?> label"><?= $status_info[0] ?></div></td>
                                            <td><?= $course['students_count'] ?: 0 ?></td>
                                            <td><?= $course['completed_count'] ?: 0 ?> (<?= $course['students_count'] > 0 ? round(($course['completed_count'] / $course['students_count']) * 100) : 0 ?>%)</td>
                                            <td>
                                                <?php if ($course['average_rating']): ?>
                                                    <div class="ui star rating" data-rating="<?= round($course['average_rating']) ?>" data-max-rating="5"></div>
                                                    <?= number_format($course['average_rating'], 1) ?>
                                                <?php else: ?>
                                                    Нет оценок
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $course['feedback_count'] ?: 0 ?></td>
                                            <td>
                                                <a href="course.php?id=<?= $course['id_course'] ?>" class="ui tiny button">Просмотр</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Статистика по тестам -->
                <div class="sixteen wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Статистика по тестам</h3>
                        <?php if (empty($tests_stats)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                В ваших курсах пока нет тестов или никто их не проходил
                            </div>
                        <?php else: ?>
                            <table class="ui celled table">
                                <thead>
                                    <tr>
                                        <th>Курс</th>
                                        <th>Тест</th>
                                        <th>Количество попыток</th>
                                        <th>Средний балл</th>
                                        <th>Средний процент</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tests_stats as $test): ?>
                                        <tr>
                                            <td><a href="course.php?id=<?= $test['id_course'] ?>"><?= htmlspecialchars($test['name_course']) ?></a></td>
                                            <td><?= htmlspecialchars($test['name_test']) ?></td>
                                            <td><?= $test['attempts_count'] ?: 0 ?></td>
                                            <td><?= $test['average_score'] ? round($test['average_score'], 1) : 'Нет данных' ?></td>
                                            <td>
                                                <?php if ($test['average_percentage']): ?>
                                                    <?php 
                                                    $percentage = round($test['average_percentage']);
                                                    $color = $percentage >= 80 ? 'green' : ($percentage >= 60 ? 'yellow' : 'red');
                                                    ?>
                                                    <div class="ui <?= $color ?> progress" data-percent="<?= $percentage ?>" style="margin-bottom: 0;">
                                                        <div class="bar" style="min-width: 0; width: <?= $percentage ?>%;">
                                                            <div class="progress"><?= $percentage ?>%</div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    Нет данных
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Прогресс студентов -->
                <div class="sixteen wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Прогресс студентов</h3>
                        <?php if (empty($students_progress)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                На ваши курсы пока не записаны студенты
                            </div>
                        <?php else: ?>
                            <table class="ui celled table">
                                <thead>
                                    <tr>
                                        <th>Студент</th>
                                        <th>Курс</th>
                                        <th>Прогресс</th>
                                        <th>Последняя активность</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students_progress as $student): ?>
                                        <?php 
                                        $progress_percentage = $student['total_steps'] > 0 
                                            ? round(($student['completed_steps'] / $student['total_steps']) * 100) 
                                            : 0;
                                        $color = $progress_percentage == 100 ? 'green' : ($progress_percentage > 50 ? 'blue' : 'orange');
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['fn_user']) ?> (<?= htmlspecialchars($student['login_user']) ?>)</td>
                                            <td><a href="course.php?id=<?= $student['id_course'] ?>"><?= htmlspecialchars($student['name_course']) ?></a></td>
                                            <td>
                                                <div class="ui <?= $color ?> progress" data-percent="<?= $progress_percentage ?>" style="margin-bottom: 0;">
                                                    <div class="bar" style="min-width: 0; width: <?= $progress_percentage ?>%;">
                                                        <div class="progress"><?= $progress_percentage ?>% (<?= $student['completed_steps'] ?>/<?= $student['total_steps'] ?>)</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= $student['last_activity'] ? date('d.m.Y H:i', strtotime($student['last_activity'])) : 'Нет данных' ?></td>
                                            <td>
                                                <?php if ($student['date_complete']): ?>
                                                    <div class="ui green label">Завершен</div>
                                                <?php else: ?>
                                                    <div class="ui blue label">В процессе</div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.rating').rating('disable');
    $('.ui.progress').progress();
});
</script>

</body>
</html> 