<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем наличие ID теста
if (!isset($_GET['test_id'])) {
    header('Location: courses.php');
    exit;
}

$test_id = (int)$_GET['test_id'];
$is_admin_view = is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1;
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Инициализируем переменную questions
    $questions = [];
    
    // Получаем информацию о тесте и проверяем права доступа
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
            SELECT t.*, s.id_step, s.number_steps, l.id_lesson, l.name_lesson, c.id_course, c.name_course, cp.id_user
            FROM Tests t
            JOIN Steps s ON t.id_step = s.id_step
            JOIN lessons l ON s.id_lesson = l.id_lesson
            JOIN course c ON l.id_course = c.id_course
            JOIN create_passes cp ON c.id_course = cp.id_course
            WHERE t.id_test = ? AND cp.id_user = ? AND cp.is_creator = true
            AND EXISTS (
                SELECT 1 FROM users u 
                WHERE u.id_user = cp.id_user AND u.role_user = ?
            )
        ");
        $stmt->execute([$test_id, $_SESSION['user']['id_user'], ROLE_TEACHER]);
    }
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
                $type_question = $_POST['type_question'] ?? 'single';
                if (empty($question_text)) {
                    $error = 'Введите текст вопроса';
                } else {
                    try {
                        $pdo->beginTransaction();
                        // Добавляем вопрос
                        $stmt = $pdo->prepare("
                            INSERT INTO Questions (id_test, text_question, answer_question, type_question)
                            VALUES (?, ?, '', ?)
                            RETURNING id_question
                        ");
                        $stmt->execute([$test_id, $question_text, $type_question]);
                        $question_id = $stmt->fetchColumn();
                        // SINGLE
                        if ($type_question === 'single') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_option = (int)$_POST['correct_option'];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([strval($correct_option), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MULTI
                        elseif ($type_question === 'multi') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_options = $_POST['correct_options'] ?? [];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([implode(',', $correct_options), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MATCH
                        elseif ($type_question === 'match') {
                            $left = $_POST['match_left'] ?? [];
                            $right = $_POST['match_right'] ?? [];
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            for ($i = 0; $i < count($left); $i++) {
                                $pair = trim($left[$i]) . '||' . trim($right[$i]);
                                $stmt->execute([
                                    $question_id,
                                    $pair
                                ]);
                            }
                        }
                        // CODE
                        elseif ($type_question === 'code') {
                            // Заглушка: только вопрос, без вариантов
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
                $type_question = $_POST['type_question'] ?? 'single';
                if (empty($question_text)) {
                    $error = 'Введите текст вопроса';
                } else {
                    try {
                        $pdo->beginTransaction();
                        // Обновляем вопрос
                        $stmt = $pdo->prepare("
                            UPDATE Questions 
                            SET text_question = ?, answer_question = '', type_question = ?
                            WHERE id_question = ? AND id_test = ?
                        ");
                        $stmt->execute([$question_text, $type_question, $question_id, $test_id]);
                        // Удаляем старые варианты ответов
                        $stmt = $pdo->prepare("DELETE FROM Answer_options WHERE id_question = ?");
                        $stmt->execute([$question_id]);
                        // SINGLE
                        if ($type_question === 'single') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_option = (int)$_POST['correct_option'];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([strval($correct_option), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MULTI
                        elseif ($type_question === 'multi') {
                            $options = array_filter($_POST['options'], 'strlen');
                            $correct_options = $_POST['correct_options'] ?? [];
                            $stmt = $pdo->prepare("
                                UPDATE Questions SET answer_question = ? WHERE id_question = ?
                            ");
                            $stmt->execute([implode(',', $correct_options), $question_id]);
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            foreach ($options as $option) {
                                $stmt->execute([
                                    $question_id,
                                    $option
                                ]);
                            }
                        }
                        // MATCH
                        elseif ($type_question === 'match') {
                            $left = $_POST['match_left'] ?? [];
                            $right = $_POST['match_right'] ?? [];
                            $stmt = $pdo->prepare("
                                INSERT INTO Answer_options (id_question, text_option)
                                VALUES (?, ?)
                            ");
                            for ($i = 0; $i < count($left); $i++) {
                                $pair = trim($left[$i]) . '||' . trim($right[$i]);
                                $stmt->execute([
                                    $question_id,
                                    $pair
                                ]);
                            }
                        }
                        // CODE
                        elseif ($type_question === 'code') {
                            // Только текст вопроса, без вариантов
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
                        Курс: <?= htmlspecialchars($test['name_course'] ?? '') ?><br>
                        Урок: <?= htmlspecialchars($test['name_lesson'] ?? '') ?><br>
                        Шаг: <?= htmlspecialchars($test['number_steps'] ?? '') ?>
                    </div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="test_results.php?test_id=<?= $test_id ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui button">
                        Результаты теста
                    </a>
                    <a href="edit_steps.php?lesson_id=<?= $test['id_lesson'] ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui button">
                        Назад к шагам
                    </a>
                </div>
            </div>

            <?php if ($is_admin_view): ?>
                <div class="ui info message">
                    <i class="eye icon"></i>
                    <strong>Режим администратора:</strong> Вы редактируете тест как преподаватель
                    <a href="edit_test.php?test_id=<?= $test_id ?>" class="ui small right floated button">Выйти из режима редактирования</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="ui success message">
                    <div class="header">Успех!</div>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <!-- Форма добавления вопроса -->
            <div class="ui segment">
                <h3>Добавить новый вопрос</h3>
                <form method="post" class="ui form" id="addQuestionForm">
                    <input type="hidden" name="action" value="add_question">
                    <div class="field">
                        <label>Тип вопроса</label>
                        <select name="type_question" id="type_question" class="ui dropdown" required>
                            <option value="single">Один правильный ответ</option>
                            <option value="multi">Несколько правильных ответов</option>
                            <option value="match">Сопоставление</option>
                            <option value="code">Вопрос с кодом</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Текст вопроса</label>
                        <textarea name="question_text" rows="2" required></textarea>
                    </div>
                    <div id="options-block">
                        <!-- Здесь будут варианты в зависимости от типа -->
                    </div>
                    <button type="submit" class="ui primary button">Добавить вопрос</button>
                </form>
            </div>

            <!-- Список существующих вопросов -->
            <div class="ui segment">
                <h3>Вопросы теста</h3>
                <div class="ui styled fluid accordion">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="title">
                            <i class="dropdown icon"></i>
                            Вопрос <?= $index + 1 ?> (<?= htmlspecialchars($question['type_question']) ?>): <?= htmlspecialchars(substr($question['text_question'], 0, 50)) ?>...
                        </div>
                        <div class="content">
                            <form method="post" class="ui form">
                                <input type="hidden" name="action" value="edit_question">
                                <input type="hidden" name="question_id" value="<?= $question['id_question'] ?>">
                                <input type="hidden" name="type_question" value="<?= htmlspecialchars($question['type_question']) ?>">
                                <div class="field">
                                    <label>Текст вопроса</label>
                                    <textarea name="question_text" rows="2" required><?= htmlspecialchars($question['text_question']) ?></textarea>
                                </div>
                                <?php 
                                $options = get_question_options($pdo, $question['id_question']);
                                if ($question['type_question'] === 'single'): ?>
                                    <div class="fields">
                                        <div class="twelve wide field">
                                            <label>Варианты ответов</label>
                                            <div class="options-container">
                                                <?php foreach ($options as $opt_index => $option): ?>
                                                    <div class="field">
                                                        <input type="text" name="options[]" value="<?= htmlspecialchars($option['text_option']) ?>" required>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="ui basic button" onclick="addOptionToContainer(this)"><i class="plus icon"></i> Добавить вариант</button>
                                        </div>
                                        <div class="four wide field">
                                            <label>Правильный ответ</label>
                                            <select name="correct_option" class="ui dropdown" required>
                                                <?php foreach ($options as $opt_index => $option): ?>
                                                    <option value="<?= $opt_index ?>" <?= ((string)$opt_index === ($question['answer_question'] ?? '')) ? 'selected' : '' ?>>Вариант <?= $opt_index + 1 ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php elseif ($question['type_question'] === 'multi'): ?>
                                    <div class="field">
                                        <label>Варианты ответов (отметьте правильные)</label>
                                        <div class="options-container">
                                            <?php foreach ($options as $opt_index => $option): ?>
                                                <div class="field">
                                                    <input type="text" name="options[]" value="<?= htmlspecialchars($option['text_option']) ?>" required>
                                                    <input type="checkbox" name="correct_options[]" value="<?= $opt_index ?>" <?= in_array((string)$opt_index, explode(',', $question['answer_question'] ?? '')) ? 'checked' : '' ?>> Правильный
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="ui basic button" onclick="addOptionMultiToContainer(this)"><i class="plus icon"></i> Добавить вариант</button>
                                    </div>
                                <?php elseif ($question['type_question'] === 'match'): ?>
                                    <div class="field">
                                        <label>Пары для сопоставления (левая и правая части)</label>
                                        <div class="match-container">
                                            <?php foreach ($options as $opt_index => $option): 
                                                $pair = explode('||', $option['text_option']);
                                                $left = $pair[0] ?? '';
                                                $right = $pair[1] ?? '';
                                            ?>
                                                <div class="fields">
                                                    <div class="eight wide field">
                                                        <input type="text" name="match_left[]" value="<?= htmlspecialchars($left) ?>" required>
                                                    </div>
                                                    <div class="eight wide field">
                                                        <input type="text" name="match_right[]" value="<?= htmlspecialchars($right) ?>" required>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="ui basic button" onclick="addMatchPairToContainer(this)"><i class="plus icon"></i> Добавить пару</button>
                                    </div>
                                <?php elseif ($question['type_question'] === 'code'): ?>
                                    <div class="ui message">Редактирование вариантов для задания с кодом не требуется.</div>
                                <?php endif; ?>
                                <div class="ui buttons">
                                    <button type="submit" class="ui primary button">Сохранить изменения</button>
                                    <button type="submit" class="ui negative button" onclick="if(!confirm('Вы уверены, что хотите удалить этот вопрос?')) return false; this.form.action.value='delete_question';">Удалить вопрос</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.accordion').accordion();
    $('.ui.dropdown').dropdown();
});

// JS для динамического отображения вариантов
function renderOptions(type) {
    const block = document.getElementById('options-block');
    if (type === 'single') {
        block.innerHTML = `
            <div class="fields">
                <div class="twelve wide field">
                    <label>Варианты ответов</label>
                    <div id="options-container">
                        <div class="field">
                            <input type="text" name="options[]" placeholder="Вариант ответа 1" required>
                        </div>
                        <div class="field">
                            <input type="text" name="options[]" placeholder="Вариант ответа 2" required>
                        </div>
                    </div>
                    <button type="button" class="ui basic button" onclick="addOption()">
                        <i class="plus icon"></i> Добавить вариант
                    </button>
                </div>
                <div class="four wide field">
                    <label>Правильный ответ</label>
                    <select name="correct_option" class="ui dropdown" required>
                        <option value="0">Вариант 1</option>
                        <option value="1">Вариант 2</option>
                    </select>
                </div>
            </div>
        `;
    } else if (type === 'multi') {
        block.innerHTML = `
            <div class="field">
                <label>Варианты ответов (отметьте правильные)</label>
                <div id="options-container">
                    <div class="field">
                        <input type="text" name="options[]" placeholder="Вариант ответа 1" required>
                        <input type="checkbox" name="correct_options[]" value="0"> Правильный
                    </div>
                    <div class="field">
                        <input type="text" name="options[]" placeholder="Вариант ответа 2" required>
                        <input type="checkbox" name="correct_options[]" value="1"> Правильный
                    </div>
                </div>
                <button type="button" class="ui basic button" onclick="addOptionMulti()">
                    <i class="plus icon"></i> Добавить вариант
                </button>
            </div>
        `;
    } else if (type === 'match') {
        block.innerHTML = `
            <div class="field">
                <label>Пары для сопоставления (левая и правая части)</label>
                <div id="match-container">
                    <div class="fields">
                        <div class="eight wide field">
                            <input type="text" name="match_left[]" placeholder="Левая часть 1" required>
                        </div>
                        <div class="eight wide field">
                            <input type="text" name="match_right[]" placeholder="Правая часть 1" required>
                        </div>
                    </div>
                    <div class="fields">
                        <div class="eight wide field">
                            <input type="text" name="match_left[]" placeholder="Левая часть 2" required>
                        </div>
                        <div class="eight wide field">
                            <input type="text" name="match_right[]" placeholder="Правая часть 2" required>
                        </div>
                    </div>
                </div>
                <button type="button" class="ui basic button" onclick="addMatchPair()">
                    <i class="plus icon"></i> Добавить пару
                </button>
            </div>
        `;
    } else if (type === 'code') {
        block.innerHTML = `<div class="ui message">Поля для кода появятся позже. Сейчас только текст вопроса.</div>`;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    renderOptions('single');
    document.getElementById('type_question').addEventListener('change', function() {
        renderOptions(this.value);
    });
});
function addOption() {
    const container = document.getElementById('options-container');
    const optionCount = container.children.length;
    const newField = document.createElement('div');
    newField.className = 'field';
    newField.innerHTML = `<input type="text" name="options[]" placeholder="Вариант ответа ${optionCount + 1}" required>`;
    container.appendChild(newField);
    updateCorrectOptions();
}
function addOptionMulti() {
    const container = document.getElementById('options-container');
    const optionCount = container.children.length;
    const newField = document.createElement('div');
    newField.className = 'field';
    newField.innerHTML = `<input type="text" name="options[]" placeholder="Вариант ответа ${optionCount + 1}" required> <input type="checkbox" name="correct_options[]" value="${optionCount}"> Правильный`;
    container.appendChild(newField);
}
function addMatchPair() {
    const container = document.getElementById('match-container');
    const pairCount = container.children.length / 2 + 1;
    const newFields = document.createElement('div');
    newFields.className = 'fields';
    newFields.innerHTML = `<div class="eight wide field"><input type="text" name="match_left[]" placeholder="Левая часть ${pairCount}" required></div><div class="eight wide field"><input type="text" name="match_right[]" placeholder="Правая часть ${pairCount}" required></div>`;
    container.appendChild(newFields);
}
function updateCorrectOptions() {
    const container = document.getElementById('options-container');
    const select = document.querySelector('select[name="correct_option"]');
    const optionCount = container.children.length;
    select.innerHTML = '';
    for (let i = 0; i < optionCount; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `Вариант ${i + 1}`;
        select.appendChild(option);
    }
}
</script>

</body>
</html> 