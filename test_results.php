<?php
require_once 'config.php';
redirect_unauthenticated();

// Инициализируем соединение с базой данных в начале файла
try {
    $pdo = get_db_connection();
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
$is_admin_view = is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1;
$error = '';

// Добавим обработку запроса на изменение статуса ответа
$success_message = '';
$error_message = '';

// Обработка формы оценивания ответа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'grade_answer') {
    try {
        $answer_id = isset($_POST['answer_id']) ? (int)$_POST['answer_id'] : 0;
        $is_correct = isset($_POST['is_correct']) ? (int)$_POST['is_correct'] : 0;
        $attempt_id = isset($_POST['attempt_id']) ? (int)$_POST['attempt_id'] : 0;
        
        if ($answer_id && $attempt_id) {
            // Проверяем права доступа (только преподаватель или админ)
            if (is_admin() || is_teacher()) {
                // Обновляем статус ответа
                $stmt = $pdo->prepare("UPDATE test_answers SET is_correct = ? WHERE id_answer = ?");
                $stmt->execute([$is_correct, $answer_id]);
                
                // Пересчитываем общий балл за тест
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as total, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as correct 
                    FROM test_answers 
                    WHERE id_attempt = ?
                ");
                $stmt->execute([$attempt_id]);
                $result = $stmt->fetch();
                
                $score = $result['correct'] ?? 0;
                $max_score = $result['total'] ?? 0;
                
                // Обновляем результат попытки
                $stmt = $pdo->prepare("UPDATE test_attempts SET score = ? WHERE id_attempt = ?");
                $stmt->execute([$score, $attempt_id]);
                
                $success_message = 'Оценка успешно обновлена!';
                
                // Перенаправляем на ту же страницу, чтобы обновить данные
                header("Location: test_results.php?test_id={$test_id}&attempt_id={$attempt_id}&success=1");
                exit;
            } else {
                $error_message = 'У вас нет прав для оценивания ответов.';
            }
        } else {
            $error_message = 'Неверные параметры запроса.';
        }
    } catch (PDOException $e) {
        $error_message = 'Ошибка базы данных: ' . $e->getMessage();
    }
}

// Показываем сообщение об успехе, если есть
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Оценка успешно обновлена!';
}

try {
    // Get test information and check access rights
    if ($is_admin_view) {
        // Admin in view mode can access any test
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
        // Regular access check
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
    }
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
    <style>
        .grade-buttons {
            margin-top: 10px;
        }
        .grade-buttons .button {
            margin-right: 5px;
        }
        .ui.success.message {
            margin-top: 15px;
        }
    </style>
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

            <?php if ($is_admin_view): ?>
                <div class="ui info message">
                    <i class="eye icon"></i>
                    <strong>Режим администратора:</strong> Вы просматриваете результаты теста как преподаватель
                    <a href="test_results.php?test_id=<?= $test_id ?><?= isset($attempt_id) && $attempt_id ? '&attempt_id='.$attempt_id : '' ?>" class="ui small right floated button">Выйти из режима просмотра</a>
                </div>
            <?php endif; ?>

            <?php if ($error || $error_message): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error ?: $error_message) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="ui success message">
                    <div class="header">Успех</div>
                    <p><?= htmlspecialchars($success_message) ?></p>
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
                                            // Одиночный выбор
                                            $opt = null;
                                            foreach ($answer['options'] as $o) {
                                                if ($o['id_option'] == $answer['id_selected_option']) $opt = $o['text_option'];
                                            }
                                            echo $opt ? htmlspecialchars($opt) : '<span class="text-muted">Нет ответа</span>';
                                        } 
                                        elseif ($answer['type_question'] === 'multi') {
                                            // Множественный выбор
                                            $selected = [];
                                            if (!empty($answer['answer_text'])) {
                                                $indices = json_decode($answer['answer_text'], true);
                                                if (is_array($indices)) {
                                                    foreach ($indices as $idx) {
                                                        if (isset($answer['options'][$idx])) {
                                                            $selected[] = $answer['options'][$idx]['text_option'];
                                                        }
                                                    }
                                                }
                                            }
                                            echo $selected ? htmlspecialchars(implode(', ', $selected)) : '<span class="text-muted">Нет ответа</span>';
                                        } 
                                        elseif ($answer['type_question'] === 'match') {
                                            // Сопоставление
                                            if (!empty($answer['answer_text'])) {
                                                $pairs = json_decode($answer['answer_text'], true);
                                                if (is_array($pairs)) {
                                                    echo '<ul>';
                                                    foreach ($pairs as $left_idx => $right_idx) {
                                                        $left = isset($answer['options'][$left_idx]) ? explode('||', $answer['options'][$left_idx]['text_option'])[0] : $left_idx;
                                                        $right = isset($answer['options'][$right_idx]) ? explode('||', $answer['options'][$right_idx]['text_option'])[1] : $right_idx;
                                                        echo '<li>' . htmlspecialchars($left) . ' → ' . htmlspecialchars($right) . '</li>';
                                                    }
                                                    echo '</ul>';
                                                } else {
                                                    echo '<span class="text-muted">Нет ответа</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Нет ответа</span>';
                                            }
                                        }
                                        elseif ($answer['type_question'] === 'code') {
                                            // Программирование
                                            echo '</p>'; // Закрываем тег <p> перед выводом кода
                                        ?>
                                            <div class="field">
                                                <label>Код студента:</label>
                                                <pre style="background-color: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto;"><?= htmlspecialchars($answer['answer_text'] ?? 'Код не предоставлен') ?></pre>
                                                
                                                <?php
                                                // Get code task details
                                                $stmt = $pdo->prepare("
                                                    SELECT * FROM code_tasks
                                                    WHERE id_question = ?
                                                ");
                                                $stmt->execute([$answer['id_question']]);
                                                $code_task = $stmt->fetch();
                                                
                                                if ($code_task): ?>
                                                    <div class="ui segment">
                                                        <div class="ui two column grid">
                                                            <div class="column">
                                                                <h5>Ожидаемый вывод:</h5>
                                                                <pre style="background-color: #f5f5f5; padding: 10px; border-radius: 4px;"><?= htmlspecialchars($code_task['output_ct']) ?></pre>
                                                            </div>
                                                            <div class="column">
                                                                <h5>Фактический вывод:</h5>
                                                                <?php if (!empty($answer['answer_text'])): ?>
                                                                    <button class="ui mini button run-code-btn" data-code="<?= htmlspecialchars($answer['answer_text']) ?>" data-language="<?= htmlspecialchars($code_task['language']) ?>" data-input="<?= htmlspecialchars($code_task['input_ct']) ?>" data-timeout="<?= (int)$code_task['execution_timeout'] ?>" data-target="output-<?= $answer['id_answer'] ?>">
                                                                        <i class="play icon"></i> Запустить код
                                                                    </button>
                                                                    <pre id="output-<?= $answer['id_answer'] ?>" style="background-color: #f5f5f5; padding: 10px; border-radius: 4px;">Нажмите кнопку для выполнения кода</pre>
                                                                <?php else: ?>
                                                                    <div class="ui warning message">Код не был предоставлен</div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ((is_admin() || is_teacher()) && !empty($answer['ai_feedback'])): ?>
                                                <div class="ui segment">
                                                    <h5>Комментарий ИИ:</h5>
                                                    <div style="background-color: #f9f9f9; padding: 10px; border-radius: 4px; border-left: 3px solid #2185d0;">
                                                        <?= nl2br(htmlspecialchars($answer['ai_feedback'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <p> <!-- Открываем новый тег <p> для продолжения -->
                                        <?php
                                        } else {
                                            // Неизвестный тип вопроса
                                            echo '<span class="text-muted">Нет ответа</span>';
                                        }
                                        ?>
                                    </p>
                                    <?php if (!$answer['is_correct']): ?>
                                        <p><strong>Правильный ответ:</strong> <?= $answer['correct_option'] !== null ? htmlspecialchars($answer['correct_option']) : '<span class="text-muted">Нет данных</span>' ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (is_admin() || is_teacher()): ?>
                                        <div class="grade-buttons">
                                            <form method="post" action="" style="display: inline-block;">
                                                <input type="hidden" name="action" value="grade_answer">
                                                <input type="hidden" name="answer_id" value="<?= $answer['id_answer'] ?>">
                                                <input type="hidden" name="attempt_id" value="<?= $attempt_id ?>">
                                                <input type="hidden" name="is_correct" value="1">
                                                <button type="submit" class="ui tiny green button <?= $answer['is_correct'] ? 'active' : '' ?>">
                                                    <i class="check icon"></i> Правильно
                                                </button>
                                            </form>
                                            <form method="post" action="" style="display: inline-block;">
                                                <input type="hidden" name="action" value="grade_answer">
                                                <input type="hidden" name="answer_id" value="<?= $answer['id_answer'] ?>">
                                                <input type="hidden" name="attempt_id" value="<?= $attempt_id ?>">
                                                <input type="hidden" name="is_correct" value="0">
                                                <button type="submit" class="ui tiny red button <?= !$answer['is_correct'] ? 'active' : '' ?>">
                                                    <i class="times icon"></i> Неправильно
                                                </button>
                                            </form>
                                        </div>
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
                                    <a href="test_results.php?test_id=<?= $test_id ?>&attempt_id=<?= $attempt['id_attempt'] ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" 
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
                <a href="edit_test.php?test_id=<?= $test_id ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui button">
                    Вернуться к тесту
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.accordion').accordion();
});

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to run code buttons
    document.querySelectorAll('.run-code-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const code = this.getAttribute('data-code');
            const language = this.getAttribute('data-language');
            const input = this.getAttribute('data-input');
            const timeout = parseInt(this.getAttribute('data-timeout')) || 5;
            const targetId = this.getAttribute('data-target');
            const outputElement = document.getElementById(targetId);
            
            // Show loading
            this.classList.add('loading');
            outputElement.textContent = 'Выполнение кода...';
            
            // Send code to server
            fetch('code_executor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    code: code,
                    language: language,
                    input: input,
                    timeout: timeout
                })
            })
            .then(response => response.json())
            .then(data => {
                this.classList.remove('loading');
                if (data.error) {
                    outputElement.textContent = 'Ошибка: ' + data.error;
                } else {
                    outputElement.textContent = data.output;
                }
            })
            .catch(error => {
                this.classList.remove('loading');
                outputElement.textContent = 'Ошибка: ' + error.message;
            });
        });
    });
});
</script>

</body>
</html> 