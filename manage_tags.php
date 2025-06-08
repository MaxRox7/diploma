<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем наличие ID курса
if (!isset($_GET['course_id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = (int)$_GET['course_id'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Получаем информацию о курсе
    $stmt = $pdo->prepare("SELECT * FROM course WHERE id_course = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: courses.php');
        exit;
    }
    
    // Проверяем, является ли пользователь создателем курса или администратором в режиме просмотра
    $is_admin_view = is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1;
    
    // Для преподавателей проверяем, что они действительно создатели курса
    if (!$is_admin_view) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM create_passes 
            WHERE id_course = ? AND id_user = ? AND is_creator = true
        ");
        $stmt->execute([$course_id, $_SESSION['user']['id_user']]);
        $is_creator = $stmt->fetchColumn() > 0;
        
        if (!$is_creator && !is_admin()) {
            header('Location: courses.php');
            exit;
        }
    }
    
    // Получаем все доступные теги
    $stmt = $pdo->prepare("SELECT * FROM tags ORDER BY name_tag");
    $stmt->execute();
    $all_tags = $stmt->fetchAll();
    
    // Получаем теги курса
    $stmt = $pdo->prepare("
        SELECT t.* 
        FROM tags t
        JOIN course_tags ct ON t.id_tag = ct.id_tag
        WHERE ct.id_course = ?
        ORDER BY t.name_tag
    ");
    $stmt->execute([$course_id]);
    $course_tags = $stmt->fetchAll();
    
    // Обработка добавления тега
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add_tag') {
                $tag_id = (int)$_POST['tag_id'];
                
                // Проверяем, что тег существует
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tags WHERE id_tag = ?");
                $stmt->execute([$tag_id]);
                $tag_exists = $stmt->fetchColumn() > 0;
                
                if ($tag_exists) {
                    // Добавляем тег к курсу
                    $stmt = $pdo->prepare("
                        INSERT INTO course_tags (id_course, id_tag)
                        VALUES (?, ?)
                        ON CONFLICT (id_course, id_tag) DO NOTHING
                    ");
                    $stmt->execute([$course_id, $tag_id]);
                    $success = 'Тег успешно добавлен к курсу';
                    
                    // Обновляем список тегов курса
                    $stmt = $pdo->prepare("
                        SELECT t.* 
                        FROM tags t
                        JOIN course_tags ct ON t.id_tag = ct.id_tag
                        WHERE ct.id_course = ?
                        ORDER BY t.name_tag
                    ");
                    $stmt->execute([$course_id]);
                    $course_tags = $stmt->fetchAll();
                } else {
                    $error = 'Выбранный тег не существует';
                }
            } elseif ($_POST['action'] === 'remove_tag') {
                $tag_id = (int)$_POST['tag_id'];
                
                // Удаляем тег из курса
                $stmt = $pdo->prepare("
                    DELETE FROM course_tags
                    WHERE id_course = ? AND id_tag = ?
                ");
                $stmt->execute([$course_id, $tag_id]);
                $success = 'Тег успешно удален из курса';
                
                // Обновляем список тегов курса
                $stmt = $pdo->prepare("
                    SELECT t.* 
                    FROM tags t
                    JOIN course_tags ct ON t.id_tag = ct.id_tag
                    WHERE ct.id_course = ?
                    ORDER BY t.name_tag
                ");
                $stmt->execute([$course_id]);
                $course_tags = $stmt->fetchAll();
            } elseif ($_POST['action'] === 'create_tag') {
                $tag_name = trim($_POST['tag_name']);
                
                if (empty($tag_name)) {
                    $error = 'Введите название тега';
                } else {
                    // Создаем новый тег
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO tags (name_tag)
                            VALUES (?)
                            RETURNING id_tag
                        ");
                        $stmt->execute([$tag_name]);
                        $new_tag_id = $stmt->fetchColumn();
                        
                        // Добавляем тег к курсу
                        $stmt = $pdo->prepare("
                            INSERT INTO course_tags (id_course, id_tag)
                            VALUES (?, ?)
                            ON CONFLICT (id_course, id_tag) DO NOTHING
                        ");
                        $stmt->execute([$course_id, $new_tag_id]);
                        $success = 'Новый тег успешно создан и добавлен к курсу';
                        
                        // Обновляем список всех тегов
                        $stmt = $pdo->prepare("SELECT * FROM tags ORDER BY name_tag");
                        $stmt->execute();
                        $all_tags = $stmt->fetchAll();
                        
                        // Обновляем список тегов курса
                        $stmt = $pdo->prepare("
                            SELECT t.* 
                            FROM tags t
                            JOIN course_tags ct ON t.id_tag = ct.id_tag
                            WHERE ct.id_course = ?
                            ORDER BY t.name_tag
                        ");
                        $stmt->execute([$course_id]);
                        $course_tags = $stmt->fetchAll();
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23505) { // Код ошибки для нарушения уникального ограничения
                            $error = 'Тег с таким названием уже существует';
                        } else {
                            throw $e;
                        }
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
    <title>Управление тегами курса - CodeSphere</title>
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
                    Управление тегами курса
                    <div class="sub header">
                        <?= htmlspecialchars($course['name_course']) ?>
                    </div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="edit_course.php?id=<?= $course_id ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui button">
                        Назад к редактированию курса
                    </a>
                    <a href="course.php?id=<?= $course_id ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui primary button">
                        Просмотр курса
                    </a>
                </div>
            </div>

            <?php if ($is_admin_view): ?>
                <div class="ui info message">
                    <i class="eye icon"></i>
                    <strong>Режим администратора:</strong> Вы редактируете теги курса как администратор
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

            <div class="ui two column grid">
                <!-- Текущие теги курса -->
                <div class="column">
                    <div class="ui segment">
                        <h3 class="ui header">Теги курса</h3>
                        
                        <?php if (empty($course_tags)): ?>
                            <div class="ui warning message">
                                <i class="info circle icon"></i>
                                У курса пока нет тегов
                            </div>
                        <?php else: ?>
                            <div class="ui relaxed divided list">
                                <?php foreach ($course_tags as $tag): ?>
                                    <div class="item">
                                        <div class="right floated content">
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="remove_tag">
                                                <input type="hidden" name="tag_id" value="<?= $tag['id_tag'] ?>">
                                                <button type="submit" class="ui red mini button" onclick="return confirm('Вы уверены, что хотите удалить этот тег?');">
                                                    <i class="trash icon"></i>
                                                    Удалить
                                                </button>
                                            </form>
                                        </div>
                                        <i class="large tag middle aligned icon"></i>
                                        <div class="content">
                                            <div class="header"><?= htmlspecialchars($tag['name_tag']) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Добавление тегов -->
                <div class="column">
                    <div class="ui segment">
                        <h3 class="ui header">Добавить существующий тег</h3>
                        
                        <form class="ui form" method="post">
                            <input type="hidden" name="action" value="add_tag">
                            <div class="field">
                                <label>Выберите тег</label>
                                <select name="tag_id" class="ui dropdown" required>
                                    <option value="">Выберите тег</option>
                                    <?php foreach ($all_tags as $tag): ?>
                                        <?php 
                                        // Проверяем, есть ли уже этот тег у курса
                                        $tag_exists = false;
                                        foreach ($course_tags as $ct) {
                                            if ($ct['id_tag'] == $tag['id_tag']) {
                                                $tag_exists = true;
                                                break;
                                            }
                                        }
                                        if (!$tag_exists):
                                        ?>
                                            <option value="<?= $tag['id_tag'] ?>"><?= htmlspecialchars($tag['name_tag']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="ui primary button">
                                <i class="plus icon"></i>
                                Добавить тег
                            </button>
                        </form>
                        
                        <div class="ui divider"></div>
                        
                        <h3 class="ui header">Создать новый тег</h3>
                        
                        <form class="ui form" method="post">
                            <input type="hidden" name="action" value="create_tag">
                            <div class="field">
                                <label>Название тега</label>
                                <input type="text" name="tag_name" placeholder="Введите название тега" required>
                            </div>
                            <button type="submit" class="ui teal button">
                                <i class="plus icon"></i>
                                Создать и добавить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    
    $('.ui.form').form({
        fields: {
            tag_id: 'empty',
            tag_name: 'empty'
        }
    });
});
</script>

</body>
</html> 