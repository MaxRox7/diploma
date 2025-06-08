<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем, является ли пользователь администратором
if (!is_admin() && !is_teacher()) {
    header('Location: courses.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $pdo = get_db_connection();
            
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Добавляем курс
            $stmt = $pdo->prepare("
                INSERT INTO course (
                    name_course, desc_course, with_certificate, hourse_course,
                    requred_year, required_spec, required_uni, level_course, tags_course, status_course
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING id_course
            ");
            
            // Проверяем и преобразуем значения перед отправкой в БД
            $hourse_course = is_numeric($hourse_course) ? $hourse_course : null;
            $requred_year = !empty($requred_year) ? (int)$requred_year : null;
            $required_spec = !empty($required_spec) ? $required_spec : null;
            $required_uni = !empty($required_uni) ? $required_uni : null;
            $level_course = !empty($level_course) ? $level_course : null;
            
            $status = is_teacher() ? 'draft' : 'approved';
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
                $status
            ]);
            
            $course_id = $stmt->fetchColumn();
            
            // Добавляем создателя курса в таблицу create_passes
            $stmt = $pdo->prepare("
                INSERT INTO create_passes (id_course, id_user, is_creator, date_complete)
                VALUES (?, ?, true, NULL)
            ");
            
            $stmt->execute([$course_id, $_SESSION['user']['id_user']]);
            
            // Обрабатываем теги курса
            if (!empty($tags_course)) {
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
            
            // Завершаем транзакцию
            $pdo->commit();
            
            $success = 'Курс успешно создан! Теперь вы можете добавить уроки.';
            header("Location: edit_lessons.php?course_id=" . $course_id);
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка при создании курса: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание курса - CodeSphere</title>
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
            <h1 class="ui header">Создание нового курса</h1>
            
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
                    <input type="text" name="name_course" placeholder="Введите название курса" required maxlength="70">
                </div>

                <div class="required field">
                    <label>Описание курса</label>
                    <textarea name="desc_course" placeholder="Подробно опишите курс" required rows="4"></textarea>
                </div>

                <div class="required field">
                    <label>Продолжительность (в часах)</label>
                    <input type="text" name="hourse_course" placeholder="Например: 40" required maxlength="5">
                </div>

                <div class="required field">
                    <label>Теги курса</label>
                    <input type="text" name="tags_course" placeholder="Введите теги через запятую" required maxlength="255">
                    <small class="description">Перечислите теги через запятую. Например: "PHP, MySQL, Программирование"</small>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="with_certificate">
                        <label>Выдавать сертификат по окончании</label>
                    </div>
                </div>

                <h4 class="ui dividing header">Требования к студентам (необязательно)</h4>

                <div class="field">
                    <label>Курс обучения</label>
                    <select class="ui dropdown" name="requred_year">
                        <option value="">Не указано</option>
                        <option value="1">1 курс</option>
                        <option value="2">2 курс</option>
                        <option value="3">3 курс</option>
                        <option value="4">4 курс</option>
                        <option value="5">5 курс</option>
                        <option value="6">6 курс</option>
                    </select>
                </div>

                <div class="field">
                    <label>Специальность</label>
                    <input type="text" name="required_spec" placeholder="Например: Информатика" maxlength="50">
                </div>

                <div class="field">
                    <label>Университет</label>
                    <input type="text" name="required_uni" placeholder="Название университета" maxlength="70">
                </div>

                <div class="field">
                    <label>Уровень сложности</label>
                    <select class="ui dropdown" name="level_course">
                        <option value="">Выберите уровень</option>
                        <option value="beginner">Начальный</option>
                        <option value="intermediate">Средний</option>
                        <option value="advanced">Продвинутый</option>
                    </select>
                </div>

                <button type="submit" class="ui primary button">Создать курс</button>
                <a href="courses.php" class="ui button">Отмена</a>
            </form>
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
