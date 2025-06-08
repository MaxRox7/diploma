<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем, что пользователь является администратором
if (!is_admin()) {
    header("Location: index.php");
    exit;
}

$error = '';

try {
    $pdo = get_db_connection();
    
    // Инициализируем значения по умолчанию для общей статистики
    $platform_stats = [
        'total_students' => 0,
        'total_teachers' => 0,
        'total_courses' => 0,
        'approved_courses' => 0,
        'total_lessons' => 0,
        'total_enrollments' => 0,
        'completed_enrollments' => 0,
        'total_test_attempts' => 0,
        'completed_test_attempts' => 0
    ];
    
    try {
        // Получаем общую статистику платформы
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE role_user = 'student') as total_students,
                (SELECT COUNT(*) FROM users WHERE role_user = 'teacher') as total_teachers,
                (SELECT COUNT(*) FROM course) as total_courses,
                (SELECT COUNT(*) FROM course WHERE status_course = 'approved') as approved_courses,
                (SELECT COUNT(*) FROM lessons) as total_lessons,
                (SELECT COUNT(*) FROM create_passes WHERE is_creator = false) as total_enrollments,
                (SELECT COUNT(*) FROM create_passes WHERE is_creator = false AND date_complete IS NOT NULL) as completed_enrollments,
                (SELECT COUNT(*) FROM test_attempts) as total_test_attempts,
                (SELECT COUNT(*) FROM test_attempts WHERE status = 'completed') as completed_test_attempts
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            $platform_stats = $result;
        }
    } catch (PDOException $e) {
        // В случае ошибки используем значения по умолчанию
    }
    
    // В таблице users нет поля date_reg, поэтому просто установим нули
    $user_stats = [
        'new_students' => 0,
        'new_teachers' => 0
    ];
    
    // Получаем статистику активности по дням за последние 30 дней
    $activity_by_day = [];
    $days = [];
    $counts = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(activity_date) as day,
                COUNT(*) as activity_count
            FROM student_analytics
            WHERE activity_date >= NOW() - INTERVAL '30 days'
            GROUP BY day
            ORDER BY day
        ");
        $stmt->execute();
        $activity_by_day = $stmt->fetchAll();
        
        // Преобразуем данные для графика активности
        foreach ($activity_by_day as $day) {
            $days[] = date('d.m', strtotime($day['day']));
            $counts[] = (int)$day['activity_count'];
        }
    } catch (PDOException $e) {
        // Если таблица не существует, продолжаем с пустыми данными
    }
    
    // Если нет данных, создаем пустой график за последние 30 дней
    if (empty($days)) {
        for ($i = 29; $i >= 0; $i--) {
            $date = date('d.m', strtotime("-$i days"));
            $days[] = $date;
            $counts[] = 0;
        }
    }
    
    // Получаем список самых популярных курсов
    $stmt = $pdo->prepare("
        SELECT 
            c.id_course,
            c.name_course,
            c.status_course,
            COUNT(DISTINCT cp.id_user) as students_count,
            COUNT(DISTINCT CASE WHEN cp.date_complete IS NOT NULL THEN cp.id_user END) as completed_count,
            AVG(CAST(f.rate_feedback AS INTEGER)) as average_rating,
            COUNT(DISTINCT f.id_feedback) as feedback_count,
            u.fn_user as creator_name
        FROM course c
        LEFT JOIN create_passes cp ON c.id_course = cp.id_course AND cp.is_creator = false
        LEFT JOIN feedback f ON c.id_course = f.id_course
        LEFT JOIN create_passes cp_creator ON c.id_course = cp_creator.id_course AND cp_creator.is_creator = true
        LEFT JOIN users u ON cp_creator.id_user = u.id_user
        GROUP BY c.id_course, c.name_course, c.status_course, u.fn_user
        ORDER BY students_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $popular_courses = $stmt->fetchAll();
    
    // Получаем статистику по тегам
    $tag_stats = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                t.name_tag,
                COUNT(DISTINCT ct.id_course) as courses_count,
                COUNT(DISTINCT cp.id_user) as students_count
            FROM tags t
            JOIN course_tags ct ON t.id_tag = ct.id_tag
            JOIN course c ON ct.id_course = c.id_course
            LEFT JOIN create_passes cp ON c.id_course = cp.id_course AND cp.is_creator = false
            GROUP BY t.name_tag
            ORDER BY students_count DESC
            LIMIT 10
        ");
        $stmt->execute();
        $tag_stats = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Если таблицы tags или course_tags не существуют, продолжаем с пустыми данными
    }
    
    // Получаем список самых активных студентов
    $active_students = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.id_user,
                u.fn_user,
                u.login_user,
                COUNT(DISTINCT cp.id_course) as enrolled_courses,
                COUNT(DISTINCT CASE WHEN cp.date_complete IS NOT NULL THEN cp.id_course END) as completed_courses,
                COUNT(ta.id_attempt) as test_attempts,
                AVG(ta.score * 100.0 / NULLIF(ta.max_score, 0)) as average_score
            FROM users u
            LEFT JOIN create_passes cp ON u.id_user = cp.id_user AND cp.is_creator = false
            LEFT JOIN test_attempts ta ON u.id_user = ta.id_user
            WHERE u.role_user = 'student'
            GROUP BY u.id_user, u.fn_user, u.login_user
            ORDER BY enrolled_courses DESC, completed_courses DESC
            LIMIT 10
        ");
        $stmt->execute();
        $active_students = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Продолжаем с пустыми данными
    }
    
    // Получаем список самых популярных преподавателей
    $popular_teachers = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.id_user,
                u.fn_user,
                u.login_user,
                COUNT(DISTINCT c.id_course) as courses_count,
                COUNT(DISTINCT cp.id_user) as students_count,
                AVG(CAST(f.rate_feedback AS INTEGER)) as average_rating
            FROM users u
            JOIN create_passes cp_creator ON u.id_user = cp_creator.id_user AND cp_creator.is_creator = true
            JOIN course c ON cp_creator.id_course = c.id_course
            LEFT JOIN create_passes cp ON c.id_course = cp.id_course AND cp.is_creator = false
            LEFT JOIN feedback f ON c.id_course = f.id_course
            WHERE u.role_user = 'teacher'
            GROUP BY u.id_user, u.fn_user, u.login_user
            ORDER BY students_count DESC, courses_count DESC
            LIMIT 10
        ");
        $stmt->execute();
        $popular_teachers = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Продолжаем с пустыми данными
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
    <title>Аналитика администратора - CodeSphere</title>
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
                <i class="chart line icon"></i>
                <div class="content">
                    Аналитика платформы
                    <div class="sub header">Глобальная статистика по всем пользователям и курсам</div>
                </div>
            </h1>
            
            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Общая статистика платформы -->
            <div class="ui four small statistics" style="margin-bottom: 30px;">
                <div class="statistic">
                    <div class="value"><?= $platform_stats['total_students'] ?: 0 ?></div>
                    <div class="label">Студентов</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $platform_stats['total_teachers'] ?: 0 ?></div>
                    <div class="label">Преподавателей</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $platform_stats['approved_courses'] ?: 0 ?></div>
                    <div class="label">Активных курсов</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $platform_stats['total_enrollments'] ?: 0 ?></div>
                    <div class="label">Записей на курсы</div>
                </div>
            </div>
            
            <div class="ui four small statistics" style="margin-bottom: 30px;">
                <div class="statistic">
                    <div class="value"><?= $user_stats['new_students'] ?: 0 ?></div>
                    <div class="label">Новых студентов за 30 дней</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $user_stats['new_teachers'] ?: 0 ?></div>
                    <div class="label">Новых преподавателей за 30 дней</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $platform_stats['completed_enrollments'] ?: 0 ?></div>
                    <div class="label">Завершенных курсов</div>
                </div>
                <div class="statistic">
                    <div class="value"><?= $platform_stats['completed_test_attempts'] ?: 0 ?></div>
                    <div class="label">Пройденных тестов</div>
                </div>
            </div>
            
            <div class="ui stackable grid">
                <!-- График активности -->
                <div class="sixteen wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Активность пользователей за последние 30 дней</h3>
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
                
                <!-- Самые популярные курсы -->
                <div class="sixteen wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Самые популярные курсы</h3>
                        <?php if (empty($popular_courses)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                Нет данных о курсах
                            </div>
                        <?php else: ?>
                            <table class="ui celled table">
                                <thead>
                                    <tr>
                                        <th>Название курса</th>
                                        <th>Преподаватель</th>
                                        <th>Статус</th>
                                        <th>Студентов</th>
                                        <th>Завершили</th>
                                        <th>Средняя оценка</th>
                                        <th>Отзывы</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popular_courses as $course): ?>
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
                                            <td><?= htmlspecialchars($course['creator_name'] ?: 'Нет данных') ?></td>
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
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Статистика по тегам -->
                <div class="eight wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Популярные теги</h3>
                        <?php if (empty($tag_stats)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                Нет данных о тегах
                            </div>
                        <?php else: ?>
                            <canvas id="tagStatsChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Самые активные студенты -->
                <div class="eight wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Самые активные студенты</h3>
                        <?php if (empty($active_students)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                Нет данных о студентах
                            </div>
                        <?php else: ?>
                            <table class="ui celled table">
                                <thead>
                                    <tr>
                                        <th>Студент</th>
                                        <th>Курсов записано</th>
                                        <th>Курсов завершено</th>
                                        <th>Попыток тестов</th>
                                        <th>Средний балл</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_students as $student): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['fn_user']) ?> (<?= htmlspecialchars($student['login_user']) ?>)</td>
                                            <td><?= $student['enrolled_courses'] ?: 0 ?></td>
                                            <td><?= $student['completed_courses'] ?: 0 ?></td>
                                            <td><?= $student['test_attempts'] ?: 0 ?></td>
                                            <td>
                                                <?php if ($student['average_score']): ?>
                                                    <?php 
                                                    $percentage = round($student['average_score']);
                                                    $color = $percentage >= 80 ? 'green' : ($percentage >= 60 ? 'yellow' : 'red');
                                                    ?>
                                                    <div class="ui <?= $color ?> label"><?= $percentage ?>%</div>
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
                
                <!-- Самые популярные преподаватели -->
                <div class="sixteen wide column">
                    <div class="ui segment">
                        <h3 class="ui header">Самые популярные преподаватели</h3>
                        <?php if (empty($popular_teachers)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                Нет данных о преподавателях
                            </div>
                        <?php else: ?>
                            <table class="ui celled table">
                                <thead>
                                    <tr>
                                        <th>Преподаватель</th>
                                        <th>Количество курсов</th>
                                        <th>Количество студентов</th>
                                        <th>Средняя оценка курсов</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popular_teachers as $teacher): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($teacher['fn_user']) ?> (<?= htmlspecialchars($teacher['login_user']) ?>)</td>
                                            <td><?= $teacher['courses_count'] ?: 0 ?></td>
                                            <td><?= $teacher['students_count'] ?: 0 ?></td>
                                            <td>
                                                <?php if ($teacher['average_rating']): ?>
                                                    <div class="ui star rating" data-rating="<?= round($teacher['average_rating']) ?>" data-max-rating="5"></div>
                                                    <?= number_format($teacher['average_rating'], 1) ?>
                                                <?php else: ?>
                                                    Нет оценок
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
    
    // График активности
    const activityChart = new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($days) ?>,
            datasets: [{
                label: 'Активность пользователей',
                data: <?= json_encode($counts) ?>,
                fill: true,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Активность пользователей по дням'
                }
            }
        }
    });
    
    <?php if (!empty($tag_stats)): ?>
    // График статистики по тегам
    const tagLabels = <?= json_encode(array_column($tag_stats, 'name_tag')) ?>;
    const courseCounts = <?= json_encode(array_column($tag_stats, 'courses_count')) ?>;
    const studentCounts = <?= json_encode(array_column($tag_stats, 'students_count')) ?>;
    
    new Chart(document.getElementById('tagStatsChart'), {
        type: 'bar',
        data: {
            labels: tagLabels,
            datasets: [
                {
                    label: 'Количество курсов',
                    data: courseCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Количество студентов',
                    data: studentCounts,
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
                    text: 'Статистика по тегам'
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

</body>
</html> 