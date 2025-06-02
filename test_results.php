<?php
require_once 'config.php';
redirect_unauthenticated();

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
$error = '';

try {
    $pdo = get_db_connection();
    
    // Get test information and check access rights
    $stmt = $pdo->prepare("
        SELECT t.*, s.id_step, s.number_steps, l.id_lesson, l.name_lesson, c.id_course, c.name_course
        FROM Tests t
        JOIN Steps s ON t.id_step = s.id_step
        JOIN lessons l ON s.id_lesson = l.id_lesson
        JOIN course c ON l.id_course = c.id_course
        JOIN create_passes cp ON c.id_course = cp.id_course
        WHERE t.id_test = ? AND cp.id_user = ?
    ");
    $stmt->execute([$test_id, $_SESSION['user']['id_user']]);
    $test = $stmt->fetch();
    
    if (!$test) {
        $error = 'Тест не найден или у вас нет доступа.';
    }

    // Get attempt information
    if ($attempt_id) {
        $stmt = $pdo->prepare("
            SELECT ta.*, u.fn_user
            FROM test_attempts ta
            JOIN users u ON ta.id_user = u.id_user
            WHERE ta.id_attempt = ? AND (
                ta.id_user = ? OR 
                EXISTS (
                    SELECT 1 FROM create_passes cp 
                    WHERE cp.id_course = ? AND cp.id_user = ? AND 
                    EXISTS (
                        SELECT 1 FROM users u2 
                        WHERE u2.id_user = cp.id_user AND u2.role_user IN (?, ?)
                    )
                )
            )
        ");
        $stmt->execute([
            $attempt_id, 
            $_SESSION['user']['id_user'],
            $test['id_course'],
            $_SESSION['user']['id_user'],
            ROLE_ADMIN,
            ROLE_TEACHER
        ]);
        $attempt = $stmt->fetch();

        if ($attempt) {
            // Получаем подробные ответы
            $stmt = $pdo->prepare("
                SELECT 
                    ta.*, 
                    q.text_question, 
                    q.type_question, 
                    (
                        SELECT text_option 
                        FROM Answer_options 
                        WHERE id_question = q.id_question AND is_correct = true
                        LIMIT 1
                    ) as correct_option
                FROM test_answers ta
                JOIN Questions q ON ta.id_question = q.id_question
                WHERE ta.id_attempt = ?
                ORDER BY q.id_question
            ");
            $stmt->execute([$attempt_id]);
            $answers = $stmt->fetchAll();
            // Для каждого ответа подгружаем варианты и декодируем данные
            foreach ($answers as $k => $ans) {
                // Получаем все варианты ответа
                $stmt_opts = $pdo->prepare("SELECT * FROM Answer_options WHERE id_question = ? ORDER BY id_option");
                $stmt_opts->execute([$ans['id_question']]);
                $answers[$k]['options'] = $stmt_opts->fetchAll();
                // Для multi/match/code декодируем текст ответа, если есть
                if ($ans['type_question'] === 'multi' || $ans['type_question'] === 'match' || $ans['type_question'] === 'code') {
                    if (isset($ans['answer_text'])) {
                        $answers[$k]['answer_text'] = $ans['answer_text'];
                    }
                }
            }
        }
    }

    // Get all attempts for this test
    $attempts = [];
    if ($test) {
        $stmt = $pdo->prepare("
            SELECT ta.*, u.fn_user
            FROM test_attempts ta
            JOIN users u ON ta.id_user = u.id_user
            WHERE ta.id_test = ?
            ORDER BY ta.start_time DESC
        ");
        $stmt->execute([$test_id]);
        $attempts = $stmt->fetchAll();
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
    <title>Результаты теста - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <h1 class="ui header">
                Результаты теста
                <div class="sub header">
                    Курс: <?= htmlspecialchars($test['name_course']) ?><br>
                    Урок: <?= htmlspecialchars($test['name_lesson']) ?><br>
                    Шаг: <?= htmlspecialchars($test['number_steps']) ?>
                </div>
            </h1>

            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($attempt) && $attempt): ?>
                <div class="ui segment">
                    <h3>Детали попытки</h3>
                    <table class="ui celled table">
                        <tbody>
                            <tr>
                                <td><strong>Студент</strong></td>
                                <td><?= htmlspecialchars($attempt['fn_user']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Дата начала</strong></td>
                                <td><?= htmlspecialchars($attempt['start_time']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Дата завершения</strong></td>
                                <td><?= htmlspecialchars($attempt['end_time'] ?? 'Не завершен') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Результат</strong></td>
                                <td><?= htmlspecialchars($attempt['score']) ?> из <?= htmlspecialchars($attempt['max_score']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Статус</strong></td>
                                <td><?= htmlspecialchars($attempt['status']) ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php if (isset($answers) && $answers): ?>
                        <h4>Ответы на вопросы</h4>
                        <div class="ui styled fluid accordion">
                            <?php foreach ($answers as $index => $answer): ?>
                                <div class="<?= $index === 0 ? 'active' : '' ?> title">
                                    <i class="dropdown icon"></i>
                                    Вопрос <?= $index + 1 ?> 
                                    <i class="<?= $answer['is_correct'] ? 'green check' : 'red times' ?> icon"></i>
                                </div>
                                <div class="<?= $index === 0 ? 'active' : '' ?> content">
                                    <p><strong>Вопрос:</strong> <?= htmlspecialchars($answer['text_question']) ?></p>
                                    <p><strong>Ваш ответ:</strong>
                                        <?php
                                        if ($answer['type_question'] === 'single') {
                                            // Для single — текст выбранного варианта
                                            $opt = null;
                                            foreach ($answer['options'] as $o) {
                                                if ($o['id_option'] == $answer['id_selected_option']) $opt = $o['text_option'];
                                            }
                                            echo $opt ? htmlspecialchars($opt) : '<span class="text-muted">Нет ответа</span>';
                                        } elseif ($answer['type_question'] === 'multi') {
                                            // Для multi — список выбранных вариантов (answer_text — JSON с id_option)
                                            $selected = [];
                                            if (!empty($answer['answer_text'])) {
                                                $ids = json_decode($answer['answer_text'], true);
                                                if (is_array($ids)) {
                                                    foreach ($answer['options'] as $o) {
                                                        if (in_array($o['id_option'], $ids)) $selected[] = $o['text_option'];
                                                    }
                                                }
                                            }
                                            echo $selected ? htmlspecialchars(implode(', ', $selected)) : '<span class="text-muted">Нет ответа</span>';
                                        } elseif ($answer['type_question'] === 'match') {
                                            // Для match — пары (answer_text — JSON с парами)
                                            if (!empty($answer['answer_text'])) {
                                                $pairs = json_decode($answer['answer_text'], true);
                                                if (is_array($pairs)) {
                                                    echo '<ul>';
                                                    foreach ($pairs as $left => $right) {
                                                        echo '<li>' . htmlspecialchars($left) . ' → ' . htmlspecialchars($right) . '</li>';
                                                    }
                                                    echo '</ul>';
                                                } else {
                                                    echo '<span class="text-muted">Нет ответа</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Нет ответа</span>';
                                            }
                                        } elseif ($answer['type_question'] === 'code') {
                                            // Для code — текст кода (answer_text)
                                            if (!empty($answer['answer_text'])) {
                                                echo '<pre>' . htmlspecialchars($answer['answer_text']) . '</pre>';
                                            } else {
                                                echo '<span class="text-muted">Ответ не сохранён</span>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">Нет ответа</span>';
                                        }
                                        ?>
                                    </p>
                                    <?php if (!$answer['is_correct']): ?>
                                        <p><strong>Правильный ответ:</strong> <?= htmlspecialchars($answer['correct_option']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="ui segment">
                <h3>Все попытки</h3>
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th>Студент</th>
                            <th>Дата начала</th>
                            <th>Статус</th>
                            <th>Результат</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $attempt): ?>
                            <tr>
                                <td><?= htmlspecialchars($attempt['fn_user']) ?></td>
                                <td><?= htmlspecialchars($attempt['start_time']) ?></td>
                                <td><?= htmlspecialchars($attempt['status']) ?></td>
                                <td>
                                    <?php if ($attempt['status'] === 'completed'): ?>
                                        <?= htmlspecialchars($attempt['score']) ?> из <?= htmlspecialchars($attempt['max_score']) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="test_results.php?test_id=<?= $test_id ?>&attempt_id=<?= $attempt['id_attempt'] ?>" 
                                       class="ui tiny button">
                                        Подробнее
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="ui segment">
                <a href="course.php?id=<?= $test['id_course'] ?>" class="ui button">
                    Вернуться к курсу
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.accordion').accordion();
});
</script>

</body>
</html> 