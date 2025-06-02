<?php
require_once 'config.php';
redirect_unauthenticated();

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$q_param = $_POST['q'] ?? $_GET['q'] ?? 0;
$is_finish = ($q_param === 'finish');
$question_index = $is_finish ? 0 : (int)$q_param;
$error = '';
$success = '';

if (!$test_id) {
    header('Location: courses.php');
    exit;
}

$pdo = get_db_connection();

// Получаем все вопросы теста
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE id_test = ? ORDER BY id_question");
$stmt->execute([$test_id]);
$questions = $stmt->fetchAll();
$total_questions = count($questions);

if ($total_questions === 0) {
    echo '<div class="ui error message">В этом тесте нет вопросов.</div>';
    exit;
}

// Сохраняем ответы в сессии
if (!isset($_SESSION['test_answers'])) {
    $_SESSION['test_answers'] = [];
}
if (!isset($_SESSION['test_answers'][$test_id])) {
    $_SESSION['test_answers'][$test_id] = array_fill(0, $total_questions, null);
}

// Обработка ответа на вопрос
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = $_POST['answer'] ?? null;
    $multi_answer = $_POST['multi_answer'] ?? null;
    $match_answer = $_POST['match_answer'] ?? null;
    $code_answer = $_POST['code_answer'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($action === 'answer') {
        $type = $questions[$question_index]['type_question'];
        if ($type === 'single') {
            $_SESSION['test_answers'][$test_id][$question_index] = $answer;
        } elseif ($type === 'multi') {
            $_SESSION['test_answers'][$test_id][$question_index] = $multi_answer ?? [];
        } elseif ($type === 'match') {
            $_SESSION['test_answers'][$test_id][$question_index] = $match_answer ?? [];
        } elseif ($type === 'code') {
            $_SESSION['test_answers'][$test_id][$question_index] = $code_answer ?? '';
        }
        // Переход к следующему вопросу, но не на финальный экран
        if ($question_index + 1 < $total_questions) {
            header('Location: test_pass.php?test_id=' . $test_id . '&q=' . ($question_index + 1));
            exit;
        } // иначе остаёмся на последнем вопросе, ждём нажатия 'Сдать работу'
    } elseif ($action === 'skip') {
        // Просто переход к следующему вопросу, но не на финальный экран
        if ($question_index + 1 < $total_questions) {
            header('Location: test_pass.php?test_id=' . $test_id . '&q=' . ($question_index + 1));
            exit;
        } // иначе остаёмся на последнем вопросе
    } elseif ($action === 'prev') {
        // Назад
        if ($question_index > 0) {
            header('Location: test_pass.php?test_id=' . $test_id . '&q=' . ($question_index - 1));
            exit;
        }
    } elseif ($action === 'finish') {
        header('Location: test_pass.php?test_id=' . $test_id . '&q=finish');
        exit;
    }
}

// Завершение теста
if ($is_finish || $question_index === -1) {
    // Проверяем, не проходил ли пользователь этот тест ранее
    $user_id = $_SESSION['user']['id_user'];
    $stmt = $pdo->prepare("SELECT * FROM test_attempts WHERE id_test = ? AND id_user = ? AND status = 'completed'");
    $stmt->execute([$test_id, $user_id]);
    $existing_attempt = $stmt->fetch();
    if ($existing_attempt) {
        echo '<div class="ui warning message">Вы уже проходили этот тест. Ваш результат: ' . $existing_attempt['score'] . ' из ' . $existing_attempt['max_score'] . '</div>';
        exit;
    }
    // Считаем результат
    $correct = 0;
    $details = [];
    foreach ($questions as $i => $q) {
        $type = $q['type_question'];
        $user_answer = $_SESSION['test_answers'][$test_id][$i];
        $is_right = false;
        if ($user_answer === null || $user_answer === '' || $user_answer === []) {
            $is_right = false;
        } else {
            if ($type === 'single') {
                $right = $q['answer_question'];
                $is_right = ($user_answer !== null && (string)$user_answer === (string)$right);
            } elseif ($type === 'multi') {
                $right = array_map('strval', explode(',', $q['answer_question']));
                $is_right = (is_array($user_answer) && count($right) && count(array_diff($right, $user_answer)) === 0 && count(array_diff($user_answer, $right)) === 0);
            } elseif ($type === 'match') {
                $right = [];
                $stmt = $pdo->prepare("SELECT * FROM Answer_options WHERE id_question = ? ORDER BY id_option");
                $stmt->execute([$q['id_question']]);
                $options = $stmt->fetchAll();
                foreach ($options as $opt_index => $option) {
                    $pair = explode('||', $option['text_option']);
                    $right[$opt_index] = $opt_index;
                }
                $is_right = (is_array($user_answer) && $user_answer == $right);
            } elseif ($type === 'code') {
                $is_right = true;
            }
        }
        $details[] = [
            'question' => $q['text_question'],
            'type' => $type,
            'user_answer' => $user_answer,
            'is_right' => $is_right,
            'right' => $q['answer_question'],
        ];
        if ($is_right) $correct++;
    }
    $score = $correct;
    $max_score = $total_questions;
    // Создаём новую попытку
    $stmt = $pdo->prepare("INSERT INTO test_attempts (id_test, id_user, score, max_score, status, end_time) VALUES (?, ?, ?, ?, 'completed', NOW()) RETURNING id_attempt");
    $stmt->execute([$test_id, $user_id, $score, $max_score]);
    $id_attempt = $stmt->fetchColumn();
    // Сохраняем ответы
    foreach ($questions as $i => $q) {
        $type = $q['type_question'];
        $user_answer = $_SESSION['test_answers'][$test_id][$i];
        $is_right = $details[$i]['is_right'];
        $is_right_bool = $is_right ? true : false;
        $stmt = $pdo->prepare("INSERT INTO test_answers (id_attempt, id_question, id_selected_option, is_correct) VALUES (?, ?, ?, ?)");
        // Получаем варианты ответа для текущего вопроса
        $options = [];
        if (in_array($type, ['single', 'multi', 'match'])) {
            $stmt_opts = $pdo->prepare("SELECT * FROM Answer_options WHERE id_question = ? ORDER BY id_option");
            $stmt_opts->execute([$q['id_question']]);
            $options = $stmt_opts->fetchAll();
        }
        // Для single/multi/match сохраняем id_option выбранного варианта, для code — null
        $selected = null;
        if ($type === 'single') {
            $selected = is_numeric($user_answer) && isset($options[$user_answer]) ? $options[$user_answer]['id_option'] : null;
        } elseif ($type === 'multi') {
            $selected = is_array($user_answer) && count($user_answer) && isset($options[$user_answer[0]]) ? $options[$user_answer[0]]['id_option'] : null;
        } elseif ($type === 'match') {
            $selected = null; // Можно доработать для хранения всех пар
        } elseif ($type === 'code') {
            $selected = null;
        }
        $stmt->bindValue(1, $id_attempt, PDO::PARAM_INT);
        $stmt->bindValue(2, $q['id_question'], PDO::PARAM_INT);
        if ($selected === null) {
            $stmt->bindValue(3, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(3, $selected, PDO::PARAM_INT);
        }
        $stmt->bindValue(4, $is_right_bool, PDO::PARAM_BOOL);
        $stmt->execute();
    }
    // Очищаем сессию
    unset($_SESSION['test_answers'][$test_id]);
    // Показываем красивую страницу результата
    ?><!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Результат теста</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    </head>
    <body>
    <div class="ui container" style="margin-top: 50px; max-width: 700px;">
        <div class="ui raised very padded segment">
            <h2 class="ui center aligned header">
                <i class="check circle outline green icon"></i>
                Тест завершён!
            </h2>
            <div class="ui center aligned huge header" style="margin-top: 20px;">
                Ваш результат: <span class="ui green text"><b><?= $score ?> / <?= $max_score ?></b></span>
            </div>
            <div class="ui divider"></div>
            <h4 class="ui header">Разбор вопросов:</h4>
            <div class="ui styled fluid accordion">
                <?php foreach ($details as $i => $d): ?>
                    <div class="title<?= $i === 0 ? ' active' : '' ?>">
                        <i class="dropdown icon"></i>
                        Вопрос <?= $i+1 ?>: <?= htmlspecialchars(mb_strimwidth($d['question'], 0, 60, '...')) ?>
                        <span class="ui <?= $d['is_right'] ? 'green' : 'red' ?> text" style="margin-left: 10px;">
                            <?= $d['is_right'] ? 'Верно' : 'Неверно' ?>
                        </span>
                    </div>
                    <div class="content<?= $i === 0 ? ' active' : '' ?>">
                        <p><b>Вопрос:</b> <?= htmlspecialchars($d['question']) ?></p>
                        <?php if ($d['type'] === 'single' || $d['type'] === 'multi'): ?>
                            <p><b>Ваш ответ:</b> <?= htmlspecialchars(is_array($d['user_answer']) ? implode(", ", $d['user_answer']) : $d['user_answer']) ?></p>
                            <p><b>Правильный ответ:</b> <?= htmlspecialchars($d['right']) ?></p>
                        <?php elseif ($d['type'] === 'match'): ?>
                            <p><b>Ваш ответ:</b> <?= htmlspecialchars(json_encode($d['user_answer'], JSON_UNESCAPED_UNICODE)) ?></p>
                        <?php elseif ($d['type'] === 'code'): ?>
                            <p>Ваш код отправлен на проверку.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="ui divider"></div>
            <a href="lesson.php?id=<?= htmlspecialchars($_GET['lesson_id'] ?? '') ?>" class="ui big button"><i class="arrow left icon"></i>Назад к уроку</a>
        </div>
    </div>
    <script>
    $(function(){ $('.ui.accordion').accordion(); });
    </script>
    </body>
    </html>
    <?php exit; }

// Получаем текущий вопрос
$question = $questions[$question_index];
$type = $question['type_question'];
$answer_value = $_SESSION['test_answers'][$test_id][$question_index];

// Получаем варианты ответа
$options = [];
if (in_array($type, ['single', 'multi', 'match'])) {
    $stmt = $pdo->prepare("SELECT * FROM Answer_options WHERE id_question = ? ORDER BY id_option");
    $stmt->execute([$question['id_question']]);
    $options = $stmt->fetchAll();
}

// Счетчик
$answered_count = 0;
foreach ($_SESSION['test_answers'][$test_id] as $ans) {
    if ($ans !== null && $ans !== '' && $ans !== []) $answered_count++;
}
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Прохождение теста</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<div class="ui container" style="margin-top: 50px; max-width: 700px;">
    <div class="ui segment">
        <div class="ui grid">
            <div class="ten wide column">
                <h3>Вопрос <?= $question_index + 1 ?> из <?= $total_questions ?></h3>
            </div>
            <div class="six wide column right aligned">
                <div class="ui mini horizontal list">
                    <?php for ($i = 0; $i < $total_questions; $i++): ?>
                        <?php $answered = $_SESSION['test_answers'][$test_id][$i] !== null && $_SESSION['test_answers'][$test_id][$i] !== '' && $_SESSION['test_answers'][$test_id][$i] !== []; ?>
                        <div class="item">
                            <a href="test_pass.php?test_id=<?= $test_id ?>&q=<?= $i ?>" class="ui circular label <?= $i === $question_index ? 'blue' : '' ?> <?= $answered ? 'green' : 'grey' ?>">
                                <?= $i + 1 ?>
                            </a>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <form class="ui form" method="post">
            <input type="hidden" name="q" value="<?= $question_index ?>">
            <div class="field">
                <label><?= htmlspecialchars($question['text_question']) ?></label>
                <?php if ($type === 'single'): ?>
                    <?php foreach ($options as $opt_index => $option): ?>
                        <div class="field">
                            <div class="ui radio checkbox">
                                <input type="radio" name="answer" value="<?= $opt_index ?>" <?= ($answer_value == $opt_index) ? 'checked' : '' ?> required>
                                <label><?= htmlspecialchars($option['text_option']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($type === 'multi'): ?>
                    <?php foreach ($options as $opt_index => $option): ?>
                        <div class="field">
                            <div class="ui checkbox">
                                <input type="checkbox" name="multi_answer[]" value="<?= $opt_index ?>" <?= (is_array($answer_value) && in_array((string)$opt_index, $answer_value)) ? 'checked' : '' ?>>
                                <label><?= htmlspecialchars($option['text_option']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($type === 'match'): ?>
                    <div class="ui two column grid">
                        <?php 
                        $left = [];
                        $right = [];
                        foreach ($options as $opt_index => $option) {
                            $pair = explode('||', $option['text_option']);
                            $left[] = $pair[0] ?? '';
                            $right[] = $pair[1] ?? '';
                        }
                        foreach ($left as $i => $lval): ?>
                            <div class="column">
                                <div class="field">
                                    <label><?= htmlspecialchars($lval) ?></label>
                                    <select name="match_answer[<?= $i ?>]" class="ui dropdown" required>
                                        <option value="">Выберите...</option>
                                        <?php foreach ($right as $j => $rval): ?>
                                            <option value="<?= $j ?>" <?= (isset($answer_value[$i]) && $answer_value[$i] == $j) ? 'selected' : '' ?>><?= htmlspecialchars($rval) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($type === 'code'): ?>
                    <textarea name="code_answer" rows="8" placeholder="Введите ваш код..."><?= htmlspecialchars($answer_value ?? '') ?></textarea>
                <?php endif; ?>
            </div>
            <div class="ui divider"></div>
            <div class="ui buttons">
                <?php if ($question_index > 0): ?>
                    <button type="submit" name="action" value="prev" class="ui button">Назад</button>
                <?php endif; ?>
                <button type="submit" name="action" value="skip" class="ui button">Пропустить</button>
                <button type="submit" name="action" value="answer" class="ui primary button">Ответить</button>
                <?php if ($answered_count === $total_questions): ?>
                    <button type="submit" name="action" value="finish" class="ui positive button" style="margin-left: 10px;">Сдать работу</button>
                <?php endif; ?>
            </div>
        </form>
        <div class="ui divider"></div>
        <div>Отвечено: <?= $answered_count ?> / <?= $total_questions ?></div>
    </div>
</div>
<script>
$(function(){ $('.ui.radio.checkbox').checkbox(); $('.ui.checkbox').checkbox(); $('.ui.dropdown').dropdown(); });
</script>
</body>
</html> 