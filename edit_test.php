<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем наличие ID теста
if (!isset($_GET['test_id'])) {
    header('Location: courses.php');
    exit;
}

$test_id = (int)$_GET['test_id'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Получаем информацию о тесте и проверяем права доступа
    $stmt = $pdo->prepare("
        SELECT t.*, s.id_step, s.name_step, l.id_lesson, l.name_lesson, c.id_course, c.name_course, cp.id_user
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
        header('Location: courses.php');
        exit;
    }
    
    // Получаем вопросы теста
    $stmt = $pdo->prepare("
        SELECT q.*, 
               (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
        FROM Questions q
        WHERE q.id_test = ?
        ORDER BY q.id_question
    ");
    $stmt->execute([$test_id]);
    $questions = $stmt->fetchAll();
    
    // Обработка POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            // Добавление нового вопроса
            if ($_POST['action'] === 'add_question') {
                $question_text = trim($_POST['question_text']);
                $options = array_filter($_POST['options'], 'strlen');
                $correct_option = (int)$_POST['correct_option'];
                
                if (empty($question_text)) {
                    $error = 'Введите текст вопроса';
                } elseif (count($options) < 2) {
                    $error = 'Добавьте как минимум два варианта ответа';
                } elseif ($correct_option >= count($options)) {
                    $error = 'Выберите правильный вариант ответа';
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        // Добавляем вопрос
                        $stmt = $pdo->prepare("
                            INSERT INTO Questions (id_test, text_question)
                            VALUES (?, ?)
                            RETURNING id_question
                        ");
                        $stmt->execute([$test_id, $question_text]);
                        $question_id = $stmt->fetchColumn();
                        
                        // Добавляем варианты ответов
                        $stmt = $pdo->prepare("
                            INSERT INTO Answer_options (id_question, text_option, is_correct)
                            VALUES (?, ?, ?)
                        ");
                        
                        foreach ($options as $index => $option) {
                            $stmt->execute([
                                $question_id,
                                $option,
                                $index === $correct_option ? true : false
                            ]);
                        }
                        
                        $pdo->commit();
                        $success = 'Вопрос успешно добавлен';
                        
                        // Перезагружаем список вопросов
                        $stmt = $pdo->prepare("
                            SELECT q.*, 
                                   (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
                            FROM Questions q
                            WHERE q.id_test = ?
                            ORDER BY q.id_question
                        ");
                        $stmt->execute([$test_id]);
                        $questions = $stmt->fetchAll();
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = $e->getMessage();
                    }
                }
            }
            // Удаление вопроса
            elseif ($_POST['action'] === 'delete_question') {
                $question_id = (int)$_POST['question_id'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Удаляем варианты ответов
                    $stmt = $pdo->prepare("DELETE FROM Answer_options WHERE id_question = ?");
                    $stmt->execute([$question_id]);
                    
                    // Удаляем вопрос
                    $stmt = $pdo->prepare("DELETE FROM Questions WHERE id_question = ? AND id_test = ?");
                    $stmt->execute([$question_id, $test_id]);
                    
                    $pdo->commit();
                    $success = 'Вопрос успешно удален';
                    
                    // Перезагружаем список вопросов
                    $stmt = $pdo->prepare("
                        SELECT q.*, 
                               (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
                        FROM Questions q
                        WHERE q.id_test = ?
                        ORDER BY q.id_question
                    ");
                    $stmt->execute([$test_id]);
                    $questions = $stmt->fetchAll();
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = $e->getMessage();
                }
            }
            // Редактирование вопроса
            elseif ($_POST['action'] === 'edit_question') {
                $question_id = (int)$_POST['question_id'];
                $question_text = trim($_POST['question_text']);
                $options = array_filter($_POST['options'], 'strlen');
                $correct_option = (int)$_POST['correct_option'];
                
                if (empty($question_text)) {
                    $error = 'Введите текст вопроса';
                } elseif (count($options) < 2) {
                    $error = 'Добавьте как минимум два варианта ответа';
                } elseif ($correct_option >= count($options)) {
                    $error = 'Выберите правильный вариант ответа';
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        // Обновляем вопрос
                        $stmt = $pdo->prepare("
                            UPDATE Questions 
                            SET text_question = ?
                            WHERE id_question = ? AND id_test = ?
                        ");
                        $stmt->execute([$question_text, $question_id, $test_id]);
                        
                        // Удаляем старые варианты ответов
                        $stmt = $pdo->prepare("DELETE FROM Answer_options WHERE id_question = ?");
                        $stmt->execute([$question_id]);
                        
                        // Добавляем новые варианты ответов
                        $stmt = $pdo->prepare("
                            INSERT INTO Answer_options (id_question, text_option, is_correct)
                            VALUES (?, ?, ?)
                        ");
                        
                        foreach ($options as $index => $option) {
                            $stmt->execute([
                                $question_id,
                                $option,
                                $index === $correct_option ? true : false
                            ]);
                        }
                        
                        $pdo->commit();
                        $success = 'Вопрос успешно обновлен';
                        
                        // Перезагружаем список вопросов
                        $stmt = $pdo->prepare("
                            SELECT q.*, 
                                   (SELECT COUNT(*) FROM Answer_options ao WHERE ao.id_question = q.id_question) as options_count
                            FROM Questions q
                            WHERE q.id_test = ?
                            ORDER BY q.id_question
                        ");
                        $stmt->execute([$test_id]);
                        $questions = $stmt->fetchAll();
                        
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

// Функция для получения вариантов ответов вопроса
function get_question_options($pdo, $question_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM Answer_options
        WHERE id_question = ?
        ORDER BY id_option
    ");
    $stmt->execute([$question_id]);
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование теста - CodeSphere</title>
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
                    Редактирование теста
                    <div class="sub header">
                        Курс: <?= htmlspecialchars($test['name_course']) ?><br>
                        Урок: <?= htmlspecialchars($test['name_lesson']) ?><br>
                        Шаг: <?= htmlspecialchars($test['name_step']) ?>
                    </div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="edit_steps.php?lesson_id=<?= $test['id_lesson'] ?>" class="ui button">
                        Назад к шагам
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

            <!-- Форма добавления вопроса -->
            <div class="ui segment">
                <h3 class="ui header">Добавить новый вопрос</h3>
                <form class="ui form" method="post" id="addQuestionForm">
                    <input type="hidden" name="action" value="add_question">
                    
                    <div class="field">
                        <label>Текст вопроса</label>
                        <input type="text" name="question_text" placeholder="Введите вопрос" required>
                    </div>
                    
                    <div class="field">
                        <label>Варианты ответов</label>
                        <div class="options-container">
                            <div class="fields">
                                <div class="twelve wide field">
                                    <input type="text" name="options[]" placeholder="Вариант ответа 1" required>
                                </div>
                                <div class="four wide field">
                                    <div class="ui radio checkbox">
                                        <input type="radio" name="correct_option" value="0" required checked>
                                        <label>Правильный ответ</label>
                                    </div>
                                </div>
                            </div>
                            <div class="fields">
                                <div class="twelve wide field">
                                    <input type="text" name="options[]" placeholder="Вариант ответа 2" required>
                                </div>
                                <div class="four wide field">
                                    <div class="ui radio checkbox">
                                        <input type="radio" name="correct_option" value="1" required>
                                        <label>Правильный ответ</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="ui basic button" id="addOptionBtn">
                            <i class="plus icon"></i>
                            Добавить вариант
                        </button>
                    </div>
                    
                    <button type="submit" class="ui primary button">Добавить вопрос</button>
                </form>
            </div>

            <!-- Список вопросов -->
            <?php if (empty($questions)): ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="tasks icon"></i>
                        В тесте пока нет вопросов
                    </div>
                </div>
            <?php else: ?>
                <div class="ui segments">
                    <?php foreach ($questions as $question): ?>
                        <?php $options = get_question_options($pdo, $question['id_question']); ?>
                        <div class="ui segment">
                            <form class="ui form" method="post">
                                <input type="hidden" name="action" value="edit_question">
                                <input type="hidden" name="question_id" value="<?= $question['id_question'] ?>">
                                
                                <div class="field">
                                    <label>Текст вопроса</label>
                                    <div class="ui action input">
                                        <input type="text" name="question_text" value="<?= htmlspecialchars($question['text_question']) ?>" required>
                                        <button type="submit" class="ui primary button">Сохранить</button>
                                        <button type="submit" class="ui red button" 
                                                formaction="?test_id=<?= $test_id ?>" 
                                                onclick="return confirm('Вы уверены, что хотите удалить этот вопрос?');"
                                                name="action" value="delete_question">
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="field">
                                    <label>Варианты ответов</label>
                                    <?php foreach ($options as $index => $option): ?>
                                        <div class="fields">
                                            <div class="twelve wide field">
                                                <input type="text" name="options[]" value="<?= htmlspecialchars($option['text_option']) ?>" required>
                                            </div>
                                            <div class="four wide field">
                                                <div class="ui radio checkbox">
                                                    <input type="radio" name="correct_option" value="<?= $index ?>" <?= $option['is_correct'] ? 'checked' : '' ?> required>
                                                    <label>Правильный ответ</label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.checkbox').checkbox();
    
    let optionCount = 2;
    
    $('#addOptionBtn').click(function() {
        optionCount++;
        const newOption = `
            <div class="fields">
                <div class="twelve wide field">
                    <input type="text" name="options[]" placeholder="Вариант ответа ${optionCount}" required>
                </div>
                <div class="four wide field">
                    <div class="ui radio checkbox">
                        <input type="radio" name="correct_option" value="${optionCount - 1}" required>
                        <label>Правильный ответ</label>
                    </div>
                </div>
            </div>
        `;
        $('.options-container').append(newOption);
        $('.ui.checkbox').checkbox();
    });
    
    $('.ui.form').form({
        fields: {
            question_text: 'empty',
            'options[]': 'empty',
            correct_option: 'checked'
        }
    });
});
</script>

</body>
</html> 