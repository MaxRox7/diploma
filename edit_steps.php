<?php
require_once 'config.php';
redirect_unauthenticated();

// Проверяем наличие ID урока
if (!isset($_GET['lesson_id'])) {
    header('Location: courses.php');
    exit;
}

$lesson_id = (int)$_GET['lesson_id'];
$error = '';
$success = '';

try {
    $pdo = get_db_connection();
    
    // Получаем информацию об уроке и курсе
    $stmt = $pdo->prepare("
        SELECT l.*, c.id_course, c.name_course
        FROM lessons l
        JOIN course c ON l.id_course = c.id_course
        WHERE l.id_lesson = ?
    ");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch();
    
    // Проверяем, является ли пользователь создателем курса или администратором в режиме просмотра
    $is_admin_view = is_admin() && isset($_GET['admin_view']) && $_GET['admin_view'] == 1;
    
    // Для преподавателей проверяем, что они действительно создатели курса
    if (!$is_admin_view) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM create_passes 
            WHERE id_course = ? AND id_user = ? AND is_creator = true
        ");
        $stmt->execute([$lesson['id_course'], $_SESSION['user']['id_user']]);
        $is_actual_creator = $stmt->fetchColumn() > 0;
        
        if (!$lesson || (!$is_actual_creator && !is_admin())) {
            header('Location: courses.php');
            exit;
        }
    } else if (!$lesson) {
        header('Location: courses.php');
        exit;
    }
    
    // Если администратор просматривает курс, получаем информацию о создателе
    if ($is_admin_view) {
        $stmt = $pdo->prepare("
            SELECT u.fn_user, u.login_user
            FROM create_passes cp
            JOIN users u ON cp.id_user = u.id_user
            WHERE cp.id_course = ? AND cp.is_creator = true
        ");
        $stmt->execute([$lesson['id_course']]);
        $creator = $stmt->fetch();
    }
    
    // Получаем шаги урока
    $stmt = $pdo->prepare("
        SELECT s.*, 
               m.path_matial as material_file,
               m.link_material,
               t.id_test as test_id
        FROM Steps s
        LEFT JOIN Material m ON s.id_step = m.id_step
        LEFT JOIN Tests t ON s.id_step = t.id_step
        WHERE s.id_lesson = ?
        ORDER BY s.id_step
    ");
    $stmt->execute([$lesson_id]);
    $steps = $stmt->fetchAll();
    
    // Обработка добавления нового шага
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add_step') {
                $name_step = trim($_POST['name_step']);
                $type_step = trim($_POST['type_step']);
                
                if (empty($name_step)) {
                    $error = 'Введите название шага';
                } else {
                    // Начинаем транзакцию
                    $pdo->beginTransaction();
                    
                    try {
                        // Создаем шаг
                        $stmt = $pdo->prepare("
                            INSERT INTO Steps (id_lesson, number_steps, type_step)
                            VALUES (?, ?, ?)
                            RETURNING id_step
                        ");
                        $stmt->execute([$lesson_id, $name_step, $type_step]);
                        $step_id = $stmt->fetchColumn();
                        
                        // Если это материал, загружаем файл или добавляем ссылку
                        if ($type_step === 'material') {
                            // Проверяем, добавляется ли ссылка
                            if (isset($_POST['material_link']) && !empty($_POST['material_link'])) {
                                $material_link = trim($_POST['material_link']);
                                
                                // Проверяем, является ли ссылка действительным URL
                                if (filter_var($material_link, FILTER_VALIDATE_URL)) {
                                    // Сохраняем ссылку в базу данных
                                    $stmt = $pdo->prepare("
                                        INSERT INTO Material (id_material, id_step, link_material)
                                        VALUES (?, ?, ?)
                                    ");
                                    $stmt->execute([uniqid(), $step_id, $material_link]);
                                } else {
                                    throw new Exception('Указан недействительный URL');
                                }
                            }
                            // Если не ссылка, то должен быть файл
                            else if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
                                $upload_dir = 'materials/' . $_SESSION['user']['login_user'] . '/' . $lesson['name_lesson'] . '/';
                                
                                // Создаем директорию, если не существует
                                if (!file_exists($upload_dir)) {
                                    mkdir($upload_dir, 0777, true);
                                }
                                
                                $file_name = $_FILES['material_file']['name'];
                                $file_name = $name_step . '_' . $step_id . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
                                $file_path = $upload_dir . $file_name;
                                
                                if (move_uploaded_file($_FILES['material_file']['tmp_name'], $file_path)) {
                                    // Сохраняем путь к файлу в базу данных
                                    $stmt = $pdo->prepare("
                                        INSERT INTO Material (id_material, id_step, path_matial)
                                        VALUES (?, ?, ?)
                                    ");
                                    $stmt->execute([uniqid(), $step_id, $file_path]);
                                } else {
                                    throw new Exception('Ошибка при загрузке файла');
                                }
                            } else {
                                throw new Exception('Необходимо загрузить файл или указать ссылку на материал');
                            }
                        }
                        
                        $pdo->commit();
                        $success = 'Шаг успешно добавлен';
                        
                        // Перезагружаем список шагов
                        $stmt = $pdo->prepare("
                            SELECT s.*, 
                                   m.path_matial as material_file,
                                   m.link_material,
                                   t.id_test as test_id
                            FROM Steps s
                            LEFT JOIN Material m ON s.id_step = m.id_step
                            LEFT JOIN Tests t ON s.id_step = t.id_step
                            WHERE s.id_lesson = ?
                            ORDER BY s.id_step
                        ");
                        $stmt->execute([$lesson_id]);
                        $steps = $stmt->fetchAll();
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = $e->getMessage();
                    }
                }
            }
            elseif ($_POST['action'] === 'delete_step') {
                $step_id = (int)$_POST['step_id'];
                
                // Начинаем транзакцию
                $pdo->beginTransaction();
                
                try {
                    // Получаем информацию о шаге
                    $stmt = $pdo->prepare("
                        SELECT s.*, m.path_matial as file_path
                        FROM Steps s
                        LEFT JOIN Material m ON s.id_step = m.id_step
                        WHERE s.id_step = ?
                    ");
                    $stmt->execute([$step_id]);
                    $step = $stmt->fetch();
                    
                    if ($step) {
                        // Если это материал, удаляем файл
                        if ($step['type_step'] === 'material' && $step['file_path']) {
                            if (file_exists($step['file_path'])) {
                                unlink($step['file_path']);
                            }
                            $stmt = $pdo->prepare("DELETE FROM Material WHERE id_step = ?");
                            $stmt->execute([$step_id]);
                        }
                        // Если это тест, удаляем тест и все связанные данные
                        elseif ($step['type_step'] === 'test') {
                            // Удаляем записи из test_answers
                            $stmt = $pdo->prepare("
                                DELETE FROM test_answers 
                                WHERE id_attempt IN (
                                    SELECT id_attempt FROM test_attempts WHERE id_test IN (
                                        SELECT id_test FROM Tests WHERE id_step = ?
                                    )
                                )
                                OR id_question IN (
                                    SELECT id_question FROM Questions WHERE id_test IN (
                                        SELECT id_test FROM Tests WHERE id_step = ?
                                    )
                                )
                            ");
                            $stmt->execute([$step_id, $step_id]);
                            
                            // Удаляем записи из test_attempts
                            $stmt = $pdo->prepare("
                                DELETE FROM test_attempts 
                                WHERE id_test IN (
                                    SELECT id_test FROM Tests WHERE id_step = ?
                                )
                            ");
                            $stmt->execute([$step_id]);
                            
                            $stmt = $pdo->prepare("
                                DELETE FROM Answer_options WHERE id_question IN (
                                    SELECT id_question FROM Questions WHERE id_test IN (
                                        SELECT id_test FROM Tests WHERE id_step = ?
                                    )
                                )
                            ");
                            $stmt->execute([$step_id]);
                            
                            $stmt = $pdo->prepare("
                                DELETE FROM Questions WHERE id_test IN (
                                    SELECT id_test FROM Tests WHERE id_step = ?
                                )
                            ");
                            $stmt->execute([$step_id]);
                            
                            $stmt = $pdo->prepare("DELETE FROM Tests WHERE id_step = ?");
                            $stmt->execute([$step_id]);
                        }
                        
                        // Удаляем записи о прогрессе пользователей по этому шагу
                        $stmt = $pdo->prepare("DELETE FROM user_material_progress WHERE id_step = ?");
                        $stmt->execute([$step_id]);
                        
                        // Удаляем сам шаг
                        $stmt = $pdo->prepare("DELETE FROM Steps WHERE id_step = ?");
                        $stmt->execute([$step_id]);
                        
                        $pdo->commit();
                        $success = 'Шаг успешно удален';
                        
                        // Перезагружаем список шагов
                        $stmt = $pdo->prepare("
                            SELECT s.*, 
                                   m.path_matial as material_file,
                                   t.id_test as test_id
                            FROM Steps s
                            LEFT JOIN Material m ON s.id_step = m.id_step
                            LEFT JOIN Tests t ON s.id_step = t.id_step
                            WHERE s.id_lesson = ?
                            ORDER BY s.id_step
                        ");
                        $stmt->execute([$lesson_id]);
                        $steps = $stmt->fetchAll();
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = $e->getMessage();
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
    <title>Редактирование шагов урока - CodeSphere</title>
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
                    <?= htmlspecialchars($lesson['name_lesson']) ?>
                    <div class="sub header">
                        Курс: <?= htmlspecialchars($lesson['name_course']) ?>
                    </div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="edit_lessons.php?course_id=<?= $lesson['id_course'] ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui button">Назад к урокам</a>
                    <a href="course.php?id=<?= $lesson['id_course'] ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui primary button">Просмотр курса</a>
                </div>
            </div>

            <?php if ($is_admin_view): ?>
                <div class="ui info message">
                    <i class="eye icon"></i>
                    <strong>Режим администратора:</strong> Вы редактируете шаги урока как преподаватель <?= htmlspecialchars($creator['fn_user']) ?> (<?= htmlspecialchars($creator['login_user']) ?>)
                    <a href="edit_steps.php?lesson_id=<?= $lesson_id ?>" class="ui small right floated button">Выйти из режима редактирования</a>
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

            <!-- Форма добавления шага -->
            <form class="ui form" method="post" enctype="multipart/form-data" id="addMaterialForm">
                <input type="hidden" name="action" value="add_step">
                <input type="hidden" name="type_step" value="material">
                <div class="ui segment">
                    <div class="fields">
                        <div class="eight wide field">
                            <label>Название шага</label>
                            <input type="text" name="name_step" placeholder="Название шага" required>
                        </div>
                    </div>
                    
                    <div class="ui two item menu">
                        <a class="item active" data-tab="file">Загрузить файл</a>
                        <a class="item" data-tab="link">Добавить ссылку</a>
                    </div>
                    
                    <div class="ui active tab segment" data-tab="file">
                        <div class="field">
                            <label>Файл материала (PDF)</label>
                            <input type="file" name="material_file" accept=".pdf" id="materialFileInput">
                        </div>
                    </div>
                    
                    <div class="ui tab segment" data-tab="link">
                        <div class="field">
                            <label>Ссылка на материал</label>
                            <input type="url" name="material_link" placeholder="https://example.com/document.pdf" id="materialLinkInput">
                        </div>
                    </div>
                    
                    <button type="submit" class="ui primary button">
                        <i class="plus icon"></i>
                        Добавить материал
                    </button>
                </div>
            </form>

            <div class="ui hidden divider"></div>

            <form class="ui form" method="post" action="manage_tests.php">
                <input type="hidden" name="action" value="create_test_step">
                <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
                <?php if ($is_admin_view): ?>
                <input type="hidden" name="admin_view" value="1">
                <?php endif; ?>
                <div class="fields">
                    <div class="twelve wide field">
                        <input type="text" name="name_step" placeholder="Название шага" required>
                    </div>
                    <div class="four wide field">
                        <button type="submit" class="ui teal fluid button">
                            <i class="tasks icon"></i>
                            Создать тест
                        </button>
                    </div>
                </div>
            </form>

            <!-- Список шагов -->
            <?php if (empty($steps)): ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="tasks icon"></i>
                        В уроке пока нет шагов
                    </div>
                </div>
            <?php else: ?>
                <div class="ui relaxed divided list">
                    <?php foreach ($steps as $step): ?>
                        <div class="item">
                            <div class="right floated content">
                                <?php if ($step['type_step'] === 'material'): ?>
                                    <?php if ($step['material_file']): ?>
                                    <a href="<?= htmlspecialchars($step['material_file']) ?>" class="ui blue button" target="_blank">
                                        <i class="file pdf icon"></i>
                                        Просмотр материала
                                    </a>
                                    <?php elseif ($step['link_material']): ?>
                                    <a href="<?= htmlspecialchars($step['link_material']) ?>" class="ui blue button" target="_blank">
                                        <i class="linkify icon"></i>
                                        Перейти по ссылке
                                    </a>
                                    <?php endif; ?>
                                <?php elseif ($step['type_step'] === 'test' && $step['test_id']): ?>
                                    <a href="edit_test.php?test_id=<?= $step['test_id'] ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui blue button">
                                        <i class="edit icon"></i>
                                        Редактировать тест
                                    </a>
                                    <a href="test.php?test_id=<?= $step['test_id'] ?><?= $is_admin_view ? '&admin_view=1' : '' ?>" class="ui green button" target="_blank">
                                        <i class="play icon"></i>
                                        Пройти тест
                                    </a>
                                <?php endif; ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_step">
                                    <input type="hidden" name="step_id" value="<?= $step['id_step'] ?>">
                                    <button type="submit" class="ui red button" onclick="return confirm('Вы уверены, что хотите удалить этот шаг?');">
                                        <i class="trash icon"></i>
                                        Удалить
                                    </button>
                                </form>
                            </div>
                            <i class="large <?= $step['type_step'] === 'material' ? 'file pdf' : 'tasks' ?> middle aligned icon"></i>
                            <div class="content">
                                <div class="header"><?= htmlspecialchars($step['number_steps']) ?></div>
                                <div class="description">
                                    Тип: <?= $step['type_step'] === 'material' ? 'Материал' : 'Тест' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    
    // Активация табов для выбора типа материала
    $('.menu .item').tab();
    
    // Обработка переключения табов
    $('.menu .item[data-tab="file"]').on('click', function() {
        $('#materialLinkInput').removeAttr('required');
        $('#materialFileInput').attr('required', 'required');
        $('#materialLinkInput').closest('.tab').removeClass('active');
        $('#materialFileInput').closest('.tab').addClass('active');
    });
    
    $('.menu .item[data-tab="link"]').on('click', function() {
        $('#materialFileInput').removeAttr('required');
        $('#materialLinkInput').attr('required', 'required');
        $('#materialFileInput').closest('.tab').removeClass('active');
        $('#materialLinkInput').closest('.tab').addClass('active');
    });
    
    // По умолчанию файл обязателен, ссылка нет
    $('#materialFileInput').attr('required', 'required');
    $('#materialLinkInput').removeAttr('required');
    
    $('.ui.form').form({
        fields: {
            name_step: 'empty'
        }
    });
});
</script>

</body>
</html> 