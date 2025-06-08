<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем, что пользователь имеет права преподавателя или администратора
if (!is_teacher() && !is_admin()) {
    header('Location: courses.php');
    exit;
}

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$error = '';
$success = '';

if (!$test_id) {
    header('Location: courses.php');
    exit;
}

try {
    $pdo = get_db_connection();
    
    // Получаем информацию о тесте и проверяем права доступа
    if (is_admin()) {
        $stmt = $pdo->prepare("
            SELECT t.*, s.id_step, s.number_steps, l.id_lesson, l.name_lesson, c.id_course, c.name_course
            FROM Tests t
            JOIN Steps s ON t.id_step = s.id_step
            JOIN lessons l ON s.id_lesson = l.id_lesson
            JOIN course c ON l.id_course = c.id_course
            WHERE t.id_test = ?
        ");
        $stmt->execute([$test_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT t.*, s.id_step, s.number_steps, l.id_lesson, l.name_lesson, c.id_course, c.name_course
            FROM Tests t
            JOIN Steps s ON t.id_step = s.id_step
            JOIN lessons l ON s.id_lesson = l.id_lesson
            JOIN course c ON l.id_course = c.id_course
            JOIN create_passes cp ON c.id_course = cp.id_course
            WHERE t.id_test = ? AND cp.id_user = ? AND cp.is_creator = true
        ");
        $stmt->execute([$test_id, $_SESSION['user']['id_user']]);
    }
    $test = $stmt->fetch();
    
    if (!$test) {
        header('Location: courses.php');
        exit;
    }
    
    // Получаем список студентов, записанных на курс
    $stmt = $pdo->prepare("
        SELECT u.id_user, u.fn_user, u.login_user,
               COALESCE(sts.additional_attempts, 0) as additional_attempts,
               (SELECT COUNT(*) FROM test_attempts ta 
                WHERE ta.id_test = ? AND ta.id_user = u.id_user) as attempt_count,
               (SELECT MAX(CAST(ta.score * 100.0 / NULLIF(ta.max_score, 0) AS INTEGER)) 
                FROM test_attempts ta 
                WHERE ta.id_test = ? AND ta.id_user = u.id_user) as best_score
        FROM users u
        JOIN create_passes cp ON u.id_user = cp.id_user
        LEFT JOIN student_test_settings sts ON u.id_user = sts.id_user AND sts.id_test = ?
        WHERE cp.id_course = ? AND u.role_user = ? AND cp.is_creator = false
        ORDER BY u.fn_user
    ");
    $stmt->execute([$test_id, $test_id, $test_id, $test['id_course'], ROLE_STUDENT]);
    $students = $stmt->fetchAll();
    
    // Обработка POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            // Добавление дополнительных попыток
            if ($_POST['action'] === 'add_attempts') {
                $student_id = (int)$_POST['student_id'];
                $additional_attempts = (int)$_POST['additional_attempts'];
                
                if ($additional_attempts < 0) {
                    $error = 'Количество дополнительных попыток не может быть отрицательным';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO student_test_settings (id_user, id_test, additional_attempts)
                            VALUES (?, ?, ?)
                            ON CONFLICT (id_user, id_test) 
                            DO UPDATE SET additional_attempts = ?
                        ");
                        $stmt->execute([$student_id, $test_id, $additional_attempts, $additional_attempts]);
                        
                        $success = 'Настройки студента успешно обновлены';
                        
                        // Обновляем список студентов
                        $stmt = $pdo->prepare("
                            SELECT u.id_user, u.fn_user, u.login_user,
                                   COALESCE(sts.additional_attempts, 0) as additional_attempts,
                                   (SELECT COUNT(*) FROM test_attempts ta 
                                    WHERE ta.id_test = ? AND ta.id_user = u.id_user) as attempt_count,
                                   (SELECT MAX(CAST(ta.score * 100.0 / NULLIF(ta.max_score, 0) AS INTEGER)) 
                                    FROM test_attempts ta 
                                    WHERE ta.id_test = ? AND ta.id_user = u.id_user) as best_score
                            FROM users u
                            JOIN create_passes cp ON u.id_user = cp.id_user
                            LEFT JOIN student_test_settings sts ON u.id_user = sts.id_user AND sts.id_test = ?
                            WHERE cp.id_course = ? AND u.role_user = ? AND cp.is_creator = false
                            ORDER BY u.fn_user
                        ");
                        $stmt->execute([$test_id, $test_id, $test_id, $test['id_course'], ROLE_STUDENT]);
                        $students = $stmt->fetchAll();
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                }
            }
            // Массовое добавление попыток
            elseif ($_POST['action'] === 'mass_add_attempts') {
                $additional_attempts = (int)$_POST['additional_attempts'];
                $student_ids = $_POST['student_ids'] ?? [];
                
                if (empty($student_ids)) {
                    $error = 'Не выбрано ни одного студента';
                } elseif ($additional_attempts < 0) {
                    $error = 'Количество дополнительных попыток не может быть отрицательным';
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        foreach ($student_ids as $student_id) {
                            $stmt = $pdo->prepare("
                                INSERT INTO student_test_settings (id_user, id_test, additional_attempts)
                                VALUES (?, ?, ?)
                                ON CONFLICT (id_user, id_test) 
                                DO UPDATE SET additional_attempts = ?
                            ");
                            $stmt->execute([(int)$student_id, $test_id, $additional_attempts, $additional_attempts]);
                        }
                        
                        $pdo->commit();
                        $success = 'Настройки для выбранных студентов успешно обновлены';
                        
                        // Обновляем список студентов
                        $stmt = $pdo->prepare("
                            SELECT u.id_user, u.fn_user, u.login_user,
                                   COALESCE(sts.additional_attempts, 0) as additional_attempts,
                                   (SELECT COUNT(*) FROM test_attempts ta 
                                    WHERE ta.id_test = ? AND ta.id_user = u.id_user) as attempt_count,
                                   (SELECT MAX(CAST(ta.score * 100.0 / NULLIF(ta.max_score, 0) AS INTEGER)) 
                                    FROM test_attempts ta 
                                    WHERE ta.id_test = ? AND ta.id_user = u.id_user) as best_score
                            FROM users u
                            JOIN create_passes cp ON u.id_user = cp.id_user
                            LEFT JOIN student_test_settings sts ON u.id_user = sts.id_user AND sts.id_test = ?
                            WHERE cp.id_course = ? AND u.role_user = ? AND cp.is_creator = false
                            ORDER BY u.fn_user
                        ");
                        $stmt->execute([$test_id, $test_id, $test_id, $test['id_course'], ROLE_STUDENT]);
                        $students = $stmt->fetchAll();
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = $e->getMessage();
                    }
                }
            }
        }
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
    <title>Управление настройками тестов студентов</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="ui container" style="padding: 20px;">
        <div class="ui breadcrumb">
            <a href="courses.php" class="section">Курсы</a>
            <i class="right chevron icon divider"></i>
            <a href="course.php?id=<?= $test['id_course'] ?>" class="section"><?= htmlspecialchars($test['name_course']) ?></a>
            <i class="right chevron icon divider"></i>
            <a href="lesson.php?id=<?= $test['id_lesson'] ?>" class="section"><?= htmlspecialchars($test['name_lesson']) ?></a>
            <i class="right chevron icon divider"></i>
            <a href="edit_test.php?test_id=<?= $test_id ?>" class="section"><?= htmlspecialchars($test['name_test']) ?></a>
            <i class="right chevron icon divider"></i>
            <div class="active section">Настройки для студентов</div>
        </div>
        
        <h1 class="ui header">Настройки теста для студентов</h1>
        <h2 class="ui header"><?= htmlspecialchars($test['name_test']) ?></h2>
        
        <?php if ($error): ?>
            <div class="ui error message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="ui success message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="ui segment">
            <h3>Информация о тесте</h3>
            <div class="ui list">
                <div class="item">
                    <div class="header">Проходной балл</div>
                    <?= $test['passing_percentage'] ?>%
                </div>
                <div class="item">
                    <div class="header">Максимальное количество попыток</div>
                    <?= $test['max_attempts'] ?>
                </div>
                <div class="item">
                    <div class="header">Время между попытками</div>
                    <?= $test['time_between_attempts'] ?> мин.
                </div>
                <div class="item">
                    <div class="header">Режим</div>
                    <?= $test['practice_mode'] ? 'Практика' : 'Экзамен' ?>
                </div>
            </div>
        </div>
        
        <div class="ui segment">
            <h3>Массовое добавление попыток</h3>
            <form method="post" class="ui form">
                <input type="hidden" name="action" value="mass_add_attempts">
                
                <div class="fields">
                    <div class="field">
                        <label>Добавить попыток</label>
                        <input type="number" name="additional_attempts" min="0" value="1" required>
                    </div>
                    <div class="field">
                        <label>&nbsp;</label>
                        <button type="submit" class="ui primary button">Применить к выбранным</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="ui segment">
            <h3>Список студентов</h3>
            
            <?php if (empty($students)): ?>
                <div class="ui info message">
                    <p>На курс не записано ни одного студента</p>
                </div>
            <?php else: ?>
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th>
                                <div class="ui checkbox">
                                    <input type="checkbox" id="select-all">
                                    <label></label>
                                </div>
                            </th>
                            <th>Студент</th>
                            <th>Логин</th>
                            <th>Использовано попыток</th>
                            <th>Лучший результат</th>
                            <th>Доп. попытки</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <div class="ui checkbox">
                                        <input type="checkbox" name="student_ids[]" value="<?= $student['id_user'] ?>" form="mass-form">
                                        <label></label>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($student['fn_user']) ?></td>
                                <td><?= htmlspecialchars($student['login_user']) ?></td>
                                <td><?= $student['attempt_count'] ?? 0 ?></td>
                                <td>
                                    <?php if ($student['best_score'] !== null): ?>
                                        <?= $student['best_score'] ?>%
                                        <?php if ($student['best_score'] >= $test['passing_percentage']): ?>
                                            <i class="green check icon" title="Тест пройден"></i>
                                        <?php else: ?>
                                            <i class="red times icon" title="Тест не пройден"></i>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= $student['additional_attempts'] ?></td>
                                <td>
                                    <form method="post" class="ui form">
                                        <input type="hidden" name="action" value="add_attempts">
                                        <input type="hidden" name="student_id" value="<?= $student['id_user'] ?>">
                                        <div class="ui action input">
                                            <input type="number" name="additional_attempts" min="0" value="<?= $student['additional_attempts'] ?>">
                                            <button type="submit" class="ui button">Сохранить</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Скрытая форма для массового добавления -->
                <form id="mass-form" method="post">
                    <input type="hidden" name="action" value="mass_add_attempts">
                    <input type="hidden" name="additional_attempts" id="mass-attempts" value="1">
                </form>
            <?php endif; ?>
        </div>
        
        <a href="edit_test.php?test_id=<?= $test_id ?>" class="ui button">Вернуться к редактированию теста</a>
    </div>
    
    <script>
    $(function(){
        $('.ui.checkbox').checkbox();
        
        // Обработка выбора всех студентов
        $('#select-all').change(function() {
            if ($(this).is(':checked')) {
                $('input[name="student_ids[]"]').prop('checked', true);
            } else {
                $('input[name="student_ids[]"]').prop('checked', false);
            }
        });
    });
    </script>
</body>
</html> 