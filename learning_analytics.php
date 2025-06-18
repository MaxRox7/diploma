<?php
require_once 'config.php';
redirect_unauthenticated();

$error = '';
$user_id = $_SESSION['user']['id_user'];

try {
    $pdo = get_db_connection();
    
    // Получаем общую статистику обучения студента
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT cp.id_course) as enrolled_courses,
            COUNT(DISTINCT CASE WHEN cp.date_complete IS NOT NULL THEN cp.id_course END) as completed_courses,
            SUM(CAST(c.hourse_course AS INTEGER)) as total_hours,
            SUM(CASE WHEN cp.date_complete IS NOT NULL THEN CAST(c.hourse_course AS INTEGER) ELSE 0 END) as completed_hours
        FROM create_passes cp
        JOIN course c ON cp.id_course = c.id_course
        WHERE cp.id_user = ?
    ");
    $stmt->execute([$user_id]);
    $general_stats = $stmt->fetch();
    
    // Инициализируем переменные статистики
    $day_stats = [];
    $time_stats = [];
    $activity_by_day = array_fill(0, 7, 0);
    $activity_by_hour = array_fill(0, 24, 0);
    
    try {
        // Получаем статистику по дням недели
        $stmt = $pdo->prepare("
            SELECT 
                EXTRACT(DOW FROM sa.activity_date) as day_of_week,
                COUNT(*) as activity_count
            FROM student_analytics sa
            WHERE sa.id_user = ?
            GROUP BY day_of_week
            ORDER BY day_of_week
        ");
        $stmt->execute([$user_id]);
        $day_stats = $stmt->fetchAll();
        
        // Преобразуем дни недели в удобочитаемый формат
        $days_of_week = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        
        foreach ($day_stats as $day) {
            $activity_by_day[(int)$day['day_of_week']] = (int)$day['activity_count'];
        }
        
        // Получаем активность студента по времени
        $stmt = $pdo->prepare("
            SELECT 
                EXTRACT(HOUR FROM sa.activity_time) as hour_of_day,
                COUNT(*) as activity_count
            FROM student_analytics sa
            WHERE sa.id_user = ?
            GROUP BY hour_of_day
            ORDER BY hour_of_day
        ");
        $stmt->execute([$user_id]);
        $time_stats = $stmt->fetchAll();
        
        foreach ($time_stats as $time) {
            $activity_by_hour[(int)$time['hour_of_day']] = (int)$time['activity_count'];
        }
    } catch (PDOException $e) {
        // Игнорируем ошибки - возможно, таблицы еще не созданы
        // В массивах уже есть нулевые значения по умолчанию
    }
    
    // Получаем прогресс по всем курсам студента
    $stmt = $pdo->prepare("
        SELECT 
            c.id_course,
            c.name_course,
            COUNT(DISTINCT s.id_step) as total_steps,
            COUNT(DISTINCT CASE WHEN s.id_step IS NOT NULL AND (
                EXISTS(
                    SELECT 1 FROM Material m 
                    JOIN Steps s2 ON m.id_step = s2.id_step 
                    JOIN user_material_progress ump ON s2.id_step = ump.id_step
                    WHERE s2.id_step = s.id_step
                    AND ump.id_user = ?
                )
                OR
                EXISTS(
                    SELECT 1 FROM Tests t 
                    JOIN Steps s2 ON t.id_step = s2.id_step 
                    JOIN test_attempts ta ON t.id_test = ta.id_test
                    WHERE s2.id_step = s.id_step
                    AND ta.id_user = ?
                    AND ta.status = 'completed'
                )
            ) THEN s.id_step END) as completed_steps,
            cp.date_complete
        FROM course c
        JOIN create_passes cp ON c.id_course = cp.id_course
        LEFT JOIN lessons l ON c.id_course = l.id_course
        LEFT JOIN Steps s ON l.id_lesson = s.id_lesson
        WHERE cp.id_user = ?
        GROUP BY c.id_course, c.name_course, cp.date_complete
        ORDER BY cp.date_complete DESC, c.name_course
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $course_progress = $stmt->fetchAll();
    
    // Инициализируем массив тегов
    $tag_stats = [];
    
    try {
        // Получаем статистику по тегам курсов пользователя
        $stmt = $pdo->prepare("
            SELECT 
                t.name_tag, 
                COUNT(DISTINCT ct.id_course) as course_count,
                SUM(CASE WHEN cp.date_complete IS NOT NULL THEN 1 ELSE 0 END) as completed_count
            FROM tags t
            JOIN course_tags ct ON t.id_tag = ct.id_tag
            JOIN create_passes cp ON ct.id_course = cp.id_course
            WHERE cp.id_user = ?
            GROUP BY t.name_tag
            ORDER BY course_count DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $tag_stats = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Игнорируем ошибки - таблица tags может отсутствовать
    }
    
    // Получаем результаты тестов студента
    $stmt = $pdo->prepare("
        SELECT 
            t.name_test,
            ta.score,
            ta.max_score,
            ta.end_time as completion_date,
            c.name_course
        FROM test_attempts ta
        JOIN Tests t ON ta.id_test = t.id_test
        JOIN Steps s ON t.id_step = s.id_step
        JOIN lessons l ON s.id_lesson = l.id_lesson
        JOIN course c ON l.id_course = c.id_course
        WHERE ta.id_user = ? AND ta.status = 'completed'
        ORDER BY ta.end_time DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $test_results = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Ошибка базы данных: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналитика обучения - CodeSphere</title>
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
                    Аналитика обучения
                    <div class="sub header">Отслеживайте свой прогресс и анализируйте результаты</div>
                </div>
            </h1>
            
            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Общая статистика -->
            <div class="ui four small statistics" style="margin-bottom: 30px;">
                <div class="statistic">
                    <div class="value"><?= $general_stats['enrolled_courses'] ?></div>
                    <div class="label">Курсов записано</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $general_stats['completed_courses'] ?></div>
                    <div class="label">Курсов завершено</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $general_stats['total_hours'] ?: 0 ?></div>
                    <div class="label">Общее время (часов)</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $general_stats['completed_hours'] ?: 0 ?></div>
                    <div class="label">Завершено (часов)</div>
                </div>
            </div>
            
            <div class="ui stackable grid">
                <!-- Прогресс по курсам -->
                <div class="eight wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Прогресс по курсам</h3>
                        <?php if (empty($course_progress)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                У вас пока нет записанных курсов
                            </div>
                        <?php else: ?>
                            <div class="ui relaxed divided list">
                                <?php foreach ($course_progress as $course): ?>
                                    <?php 
                                    $progress_percentage = $course['total_steps'] > 0 
                                        ? round(($course['completed_steps'] / $course['total_steps']) * 100) 
                                        : 0;
                                    $color = $progress_percentage == 100 ? 'green' : ($progress_percentage > 50 ? 'blue' : 'orange');
                                    ?>
                                    <div class="item">
                                        <div class="content">
                                            <div class="header">
                                                <a href="course.php?id=<?= $course['id_course'] ?>"><?= htmlspecialchars($course['name_course']) ?></a>
                                                <?php if ($course['date_complete']): ?>
                                                    <span class="ui green tiny label">Завершен</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="description">
                                                <div class="ui <?= $color ?> tiny progress" data-percent="<?= $progress_percentage ?>">
                                                    <div class="bar" style="width: <?= $progress_percentage ?>%;">
                                                        <div class="progress"><?= $progress_percentage ?>%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Статистика по тегам -->
                <div class="eight wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Популярные теги ваших курсов</h3>
                        <?php if (empty($tag_stats)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                Недостаточно данных для анализа тегов
                            </div>
                        <?php else: ?>
                            <canvas id="tagStatsChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Результаты тестов -->
                <div class="sixteen wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Последние результаты тестов</h3>
                        <?php if (empty($test_results)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                У вас пока нет завершенных тестов
                            </div>
                        <?php else: ?>
                            <table class="ui celled table">
                                <thead>
                                    <tr>
                                        <th>Тест</th>
                                        <th>Курс</th>
                                        <th>Результат</th>
                                        <th>Процент</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($test_results as $test): ?>
                                        <?php 
                                        $percentage = $test['max_score'] > 0 
                                            ? round(($test['score'] / $test['max_score']) * 100) 
                                            : 0;
                                        $color = $percentage >= 80 ? 'positive' : ($percentage >= 60 ? 'warning' : 'negative');
                                        ?>
                                        <tr class="<?= $color ?>">
                                            <td><?= htmlspecialchars($test['name_test']) ?></td>
                                            <td><?= htmlspecialchars($test['name_course']) ?></td>
                                            <td><?= $test['score'] ?> / <?= $test['max_score'] ?></td>
                                            <td><?= $percentage ?>%</td>
                                            <td><?= date('d.m.Y H:i', strtotime($test['completion_date'])) ?></td>
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
    $('.ui.progress').progress();
    

    <?php if (!empty($tag_stats)): ?>
    // График статистики по тегам
    const tagLabels = <?= json_encode(array_column($tag_stats, 'name_tag')) ?>;
    const tagData = <?= json_encode(array_column($tag_stats, 'course_count')) ?>;
    const completedData = <?= json_encode(array_column($tag_stats, 'completed_count')) ?>;
    
    new Chart(document.getElementById('tagStatsChart'), {
        type: 'bar',
        data: {
            labels: tagLabels,
            datasets: [
                {
                    label: 'Всего курсов',
                    data: tagData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Завершено',
                    data: completedData,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: false
                },
                x: {
                    stacked: false
                }
            },
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Статистика по тегам курсов'
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

</body>
</html>
