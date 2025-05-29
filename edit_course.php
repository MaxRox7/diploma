<?php
require_once 'config.php';
redirect_unauthenticated();

if (!isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = (int)$_GET['id'];
$user_id = $_SESSION['user']['id_user'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Проверяем права доступа (должен быть создателем курса)
    $stmt = $pdo->prepare("
        SELECT c.*, cp.id_user
        FROM course c
        JOIN create_passes cp ON c.id_course = cp.id_course
        WHERE c.id_course = ? AND cp.id_user = ?
    ");
    $stmt->execute([$course_id, $user_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: courses.php');
        exit;
    }
    
    // Обработка формы редактирования
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name_course = trim($_POST['name_course']);
        $desc_course = trim($_POST['desc_course']);
        $with_certificate = isset($_POST['with_certificate']) ? true : false;
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
                
                $stmt->execute([
                    $name_course,
                    $desc_course,
                    $with_certificate,
                    $hourse_course,
                    $requred_year ?: null,
                    $required_spec ?: null,
                    $required_uni ?: null,
                    $level_course ?: null,
                    $tags_course,
                    $course_id
                ]);
                
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
                    Редактирование курса
                    <div class="sub header"><?= htmlspecialchars($course['name_course']) ?></div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="course.php?id=<?= $course_id ?>" class="ui button">Назад к курсу</a>
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
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="with_certificate" <?= $course['with_certificate'] ? 'checked' : '' ?>>
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
});
</script>

</body>
</html> 