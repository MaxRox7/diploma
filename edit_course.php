<?php
require_once 'config.php';
redirect_unauthenticated();

if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0);
$user_id = $_SESSION['user']['id_user'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Получаем информацию о курсе
    $stmt = $pdo->prepare("
        SELECT c.*, cp.id_user, cp.is_creator 
        FROM course c
        LEFT JOIN create_passes cp ON c.id_course = cp.id_course AND cp.id_user = ?
        WHERE c.id_course = ?
    ");
    $stmt->execute([$user_id, $course_id]);
    $course = $stmt->fetch();
    
    // Получаем теги курса для формирования строки tags_course
    $stmt = $pdo->prepare("
        SELECT t.name_tag 
        FROM tags t
        JOIN course_tags ct ON t.id_tag = ct.id_tag
        WHERE ct.id_course = ?
        ORDER BY t.name_tag
    ");
    $stmt->execute([$course_id]);
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $course['tags_course'] = implode(', ', $tags);
    
    // Если администратор просматривает курс с параметром admin_view=1
    if (is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1) {
        if (!$course) {
            // Если курс не найден для текущего пользователя, получаем его напрямую
            $stmt = $pdo->prepare("
                SELECT c.*, cp.id_user
                FROM course c
                JOIN create_passes cp ON c.id_course = cp.id_course
                WHERE c.id_course = ? AND cp.is_creator = true
                LIMIT 1
            ");
            $stmt->execute([$course_id]);
            $course = $stmt->fetch();
            
            if (!$course) {
                header('Location: courses.php');
                exit;
            }
            
            // Получаем информацию о создателе курса для отображения
            $stmt = $pdo->prepare("
                SELECT u.fn_user, u.login_user
                FROM users u
                WHERE u.id_user = ?
            ");
            $stmt->execute([$course['id_user']]);
            $creator = $stmt->fetch();
            
            $admin_view = true;
        }
    } else if (!$course || !$course['is_creator']) {
        header('Location: courses.php');
        exit;
    }
    
    // Обработка формы редактирования
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'send_to_moderation') {
            $stmt = $pdo->prepare("UPDATE course SET status_course='pending', moderation_comment=NULL WHERE id_course=?");
            $stmt->execute([$course_id]);
            header('Location: edit_course.php?id=' . $course_id);
            exit;
        }
        $name_course = trim($_POST['name_course']);
        $desc_course = trim($_POST['desc_course']);
        $with_certificate = isset($_POST['with_certificate']) ? 'true' : 'false';
        $hourse_course = trim($_POST['hourse_course']);
        $requred_year = trim($_POST['requred_year']);
        $required_spec = trim($_POST['required_spec']);
        $required_uni = trim($_POST['required_uni']);
        $level_course = trim($_POST['level_course']);
        $tags_course = trim($_POST['tags_course']);
        
        if (empty($name_course) || empty($desc_course) || empty($hourse_course) || empty($tags_course)) {
            $error = 'Заполните все обязательные поля';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE course 
                    SET name_course = ?, 
                        desc_course = ?, 
                        with_certificate = ?,
                        hourse_course = ?,
                        requred_year = ?,
                        required_spec = ?,
                        required_uni = ?,
                        level_course = ?,
                        tags_course = ?
                    WHERE id_course = ?
                ");
                
                // Проверяем и преобразуем значения перед отправкой в БД
                $hourse_course = is_numeric($hourse_course) ? $hourse_course : null;
                $requred_year = !empty($requred_year) ? (int)$requred_year : null;
                $required_spec = !empty($required_spec) ? $required_spec : null;
                $required_uni = !empty($required_uni) ? $required_uni : null;
                $level_course = !empty($level_course) ? $level_course : null;
                
                $stmt->execute([
                    $name_course,
                    $desc_course,
                    $with_certificate,
                    $hourse_course,
                    $requred_year,
                    $required_spec,
                    $required_uni,
                    $level_course,
                    $tags_course,
                    $course_id
                ]);
                
                // Обрабатываем теги курса
                if (!empty($tags_course)) {
                    // Удаляем существующие связи тегов с курсом
                    $stmt = $pdo->prepare("DELETE FROM course_tags WHERE id_course = ?");
                    $stmt->execute([$course_id]);
                    
                    // Добавляем новые теги
                    $tag_names = explode(',', $tags_course);
                    foreach ($tag_names as $tag_name) {
                        $tag_name = trim($tag_name);
                        if (empty($tag_name)) continue;
                        
                        // Проверяем, существует ли тег
                        $stmt = $pdo->prepare("
                            SELECT id_tag FROM tags 
                            WHERE LOWER(name_tag) = LOWER(?) 
                            LIMIT 1
                        ");
                        $stmt->execute([$tag_name]);
                        $tag_id = $stmt->fetchColumn();
                        
                        // Если тег не существует, создаем его
                        if (!$tag_id) {
                            $stmt = $pdo->prepare("
                                INSERT INTO tags (name_tag) 
                                VALUES (?) 
                                RETURNING id_tag
                            ");
                            $stmt->execute([$tag_name]);
                            $tag_id = $stmt->fetchColumn();
                        }
                        
                        // Связываем тег с курсом
                        $stmt = $pdo->prepare("
                            INSERT INTO course_tags (id_course, id_tag)
                            VALUES (?, ?)
                            ON CONFLICT (id_course, id_tag) DO NOTHING
                        ");
                        $stmt->execute([$course_id, $tag_id]);
                    }
                }
                
                $success = 'Курс успешно обновлен';
                
                // Обновляем данные курса
                $stmt = $pdo->prepare("SELECT * FROM course WHERE id_course = ?");
                $stmt->execute([$course_id]);
                $course = $stmt->fetch();
                
            } catch (PDOException $e) {
                $error = 'Ошибка при обновлении курса: ' . $e->getMessage();
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
    <title>Редактирование курса - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <div class="ui clearing segment">
                <h2 class="ui left floated header">
                    Редактирование курса
                    <div class="sub header"><?= htmlspecialchars($course['name_course']) ?></div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="manage_tags.php?course_id=<?= $course_id ?><?= isset($admin_view) && $admin_view ? '&admin_view=1' : '' ?>" class="ui teal button">
                        <i class="tags icon"></i> Управление тегами
                    </a>
                    <a href="course.php?id=<?= $course_id ?>" class="ui button">Назад к курсу</a>
                </div>
            </div>

            <?php if (isset($admin_view) && $admin_view): ?>
                <div class="ui info message">
                    <i class="eye icon"></i>
                    <strong>Режим администратора:</strong> Вы редактируете курс как преподаватель <?= htmlspecialchars($creator['fn_user']) ?> (<?= htmlspecialchars($creator['login_user']) ?>)
                    <a href="edit_course.php?id=<?= $course_id ?>" class="ui small right floated button">Выйти из режима редактирования</a>
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
                    <div class="header">Успех</div>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <form class="ui form" method="post">
                <div class="required field">
                    <label>Название курса</label>
                    <input type="text" name="name_course" value="<?= htmlspecialchars($course['name_course']) ?>" required maxlength="70">
                </div>

                <div class="required field">
                    <label>Описание курса</label>
                    <textarea name="desc_course" required rows="4"><?= htmlspecialchars($course['desc_course']) ?></textarea>
                </div>

                <div class="required field">
                    <label>Продолжительность (в часах)</label>
                    <input type="text" name="hourse_course" value="<?= htmlspecialchars($course['hourse_course']) ?>" required maxlength="5">
                </div>

                <div class="required field">
                    <label>Теги курса</label>
                    <input type="text" name="tags_course" value="<?= htmlspecialchars($course['tags_course']) ?>" required maxlength="255">
                    <small class="description">Перечислите теги через запятую. Например: "PHP, MySQL, Программирование"</small>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="with_certificate" <?= $course['with_certificate'] == 'true' ? 'checked' : '' ?>>
                        <label>Выдавать сертификат по окончании</label>
                    </div>
                </div>

                <h4 class="ui dividing header">Требования к студентам (необязательно)</h4>

                <div class="field">
                    <label>Курс обучения</label>
                    <select class="ui dropdown" name="requred_year">
                        <option value="">Не указано</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= $course['requred_year'] == $i ? 'selected' : '' ?>><?= $i ?> курс</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Специальность</label>
                    <input type="text" name="required_spec" value="<?= htmlspecialchars($course['required_spec'] ?? '') ?>" maxlength="50">
                </div>

                <div class="field">
                    <label>Университет</label>
                    <input type="text" name="required_uni" value="<?= htmlspecialchars($course['required_uni'] ?? '') ?>" maxlength="70">
                </div>

                <div class="field">
                    <label>Уровень сложности</label>
                    <select class="ui dropdown" name="level_course">
                        <option value="">Выберите уровень</option>
                        <option value="beginner" <?= $course['level_course'] == 'beginner' ? 'selected' : '' ?>>Начальный</option>
                        <option value="intermediate" <?= $course['level_course'] == 'intermediate' ? 'selected' : '' ?>>Средний</option>
                        <option value="advanced" <?= $course['level_course'] == 'advanced' ? 'selected' : '' ?>>Продвинутый</option>
                    </select>
                </div>

                <button type="submit" class="ui primary button">Сохранить изменения</button>
                <a href="course.php?id=<?= $course_id ?>" class="ui button">Отмена</a>
            </form>

            <?php if (($course['status_course'] === 'draft' || $course['status_course'] === 'correction') && $course['id_user'] == $user_id): ?>
                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="action" value="send_to_moderation">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    <button type="submit" class="ui orange button">
                        <i class="paper plane icon"></i> Отправить на модерацию
                    </button>
                    <?php if ($course['status_course'] === 'correction' && !empty($course['moderation_comment'])): ?>
                        <div class="ui warning message" style="margin-top:10px;">
                            <b>Комментарий модератора:</b><br>
                            <?= nl2br(htmlspecialchars($course['moderation_comment'])) ?>
                        </div>
                    <?php endif; ?>
                </form>
            <?php elseif ($course['status_course'] === 'pending'): ?>
                <div class="ui info message" style="margin-top:20px;">
                    Курс отправлен на модерацию. Ожидайте решения администратора.
                </div>
            <?php elseif ($course['status_course'] === 'approved'): ?>
                <div class="ui positive message" style="margin-top:20px;">
                    Курс одобрен и доступен студентам.
                </div>
            <?php elseif ($course['status_course'] === 'rejected'): ?>
                <div class="ui negative message" style="margin-top:20px;">
                    Курс отклонён модератором.
                    <?php if (!empty($course['moderation_comment'])): ?>
                        <br><b>Комментарий:</b> <?= nl2br(htmlspecialchars($course['moderation_comment'])) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    $('.ui.checkbox').checkbox();
    
    $('.ui.form').form({
        fields: {
            name_course: 'empty',
            desc_course: 'empty',
            hourse_course: ['empty', 'number'],
            tags_course: 'empty'
        }
    });
    
    // Получаем все существующие теги для автозаполнения
    $.getJSON('get_tags.php', function(data) {
        $('input[name="tags_course"]').autocomplete({
            source: function(request, response) {
                var terms = request.term.split(/,\s*/);
                var currentTerm = terms.pop();
                var filteredData = $.grep(data, function(item) {
                    return item.toLowerCase().indexOf(currentTerm.toLowerCase()) === 0;
                });
                response(filteredData);
            },
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                var terms = this.value.split(/,\s*/);
                terms.pop();
                terms.push(ui.item.value);
                terms.push("");
                this.value = terms.join(", ");
                return false;
            },
            minLength: 1
        });
    });
});
</script>

</body>
</html> 