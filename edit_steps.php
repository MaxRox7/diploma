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
    
    if (!$lesson || !is_course_creator($pdo, $lesson['id_course'], $_SESSION['user']['id_user'])) {
        header('Location: courses.php');
        exit;
    }
    
    // Получаем шаги урока
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
    
    // Обработка добавления нового шага
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add_step') {
                $name_step = trim($_POST['name_step']);
                $type_step = $_POST['type_step'];
                
                if (empty($name_step)) {
                    $error = 'Введите название шага';
                } else {
                    // Начинаем транзакцию
                    $pdo->beginTransaction();
                    
                    try {
                        // Добавляем шаг
                        $stmt = $pdo->prepare("
                            INSERT INTO Steps (id_lesson, number_steps, status_step, type_step)
                            VALUES (?, ?, 'not_started', ?)
                            RETURNING id_step
                        ");
                        $stmt->execute([$lesson_id, $name_step, $type_step]);
                        $step_id = $stmt->fetchColumn();
                        
                        // Если это материал, обрабатываем загрузку файла
                        if ($type_step === 'material' && isset($_FILES['material_file'])) {
                            $file = $_FILES['material_file'];
                            if ($file['error'] === UPLOAD_ERR_OK) {
                                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                if ($file_extension === 'pdf') {
                                    // Получаем безопасные имена для папок (заменяем пробелы и спецсимволы на подчеркивание)
                                    $safe_course_name = preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '_', $lesson['name_course']);
                                    $safe_lesson_name = preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '_', $lesson['name_lesson']);
                                    $safe_step_name = preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '_', $name_step);
                                    
                                    // Создаем структуру папок: materials/course_name/lesson_name/step_name_N/
                                    $base_dir = 'materials/';
                                    $course_dir = $base_dir . $safe_course_name . '/';
                                    $lesson_dir = $course_dir . $safe_lesson_name . '/';
                                    $step_dir = $lesson_dir . $safe_step_name . '_' . $step_id . '/';
                                    
                                    // Создаем все необходимые директории
                                    foreach ([$base_dir, $course_dir, $lesson_dir, $step_dir] as $dir) {
                                        if (!file_exists($dir)) {
                                            if (!mkdir($dir, 0777, true)) {
                                                throw new Exception('Не удалось создать директорию: ' . $dir);
                                            }
                                        }
                                    }
                                    
                                    // Генерируем имя файла, сохраняя оригинальное имя
                                    $safe_filename = preg_replace('/[^a-zA-Zа-яА-Я0-9_.-]+/u', '_', $file['name']);
                                    $file_path = $step_dir . $safe_filename;
                                    
                                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                                        // Генерируем уникальный id_material
                                        do {
                                            $material_id = 'MAT' . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
                                            
                                            // Проверяем, не существует ли уже такой ID
                                            $check = $pdo->prepare("SELECT 1 FROM Material WHERE id_material = ?");
                                            $check->execute([$material_id]);
                                        } while ($check->fetch());
                                        
                                        $stmt = $pdo->prepare("
                                            INSERT INTO Material (id_material, id_step, path_matial)
                                            VALUES (?, ?, ?)
                                        ");
                                        $stmt->execute([$material_id, $step_id, $file_path]);
                                    } else {
                                        throw new Exception('Ошибка при загрузке файла');
                                    }
                                } else {
                                    throw new Exception('Разрешены только PDF файлы');
                                }
                            }
                        }
                        // Если это тест, создаем пустой тест
                        elseif ($type_step === 'test') {
                            // Создаем тест с временным названием, которое можно будет изменить в manage_tests.php
                            $stmt = $pdo->prepare("
                                INSERT INTO Tests (id_step, name_test, desc_test)
                                VALUES (?, 'Новый тест', '')
                                RETURNING id_test
                            ");
                            $stmt->execute([$step_id]);
                            $test_id = $stmt->fetchColumn();
                            
                            $pdo->commit();
                            header("Location: manage_tests.php?step_id=" . $step_id);
                            exit;
                        }
                        
                        $pdo->commit();
                        $success = 'Шаг успешно добавлен';
                        
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
                    <a href="edit_lessons.php?course_id=<?= $lesson['id_course'] ?>" class="ui button">
                        Назад к урокам
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

            <!-- Форма добавления шага -->
            <form class="ui form" method="post" enctype="multipart/form-data" id="addStepForm">
                <input type="hidden" name="action" value="add_step">
                <div class="fields">
                    <div class="eight wide field">
                        <input type="text" name="name_step" placeholder="Название шага" required>
                    </div>
                    <div class="four wide field">
                        <select class="ui dropdown" name="type_step" id="stepType" required>
                            <option value="">Тип шага</option>
                            <option value="material">Материал</option>
                            <option value="test">Тест</option>
                        </select>
                    </div>
                    <div class="four wide field" id="materialFileField" style="display: none;">
                        <input type="file" name="material_file" accept=".pdf">
                    </div>
                    <div class="four wide field">
                        <button type="submit" class="ui primary fluid button">Добавить шаг</button>
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
                                <?php if ($step['type_step'] === 'material' && $step['material_file']): ?>
                                    <a href="<?= htmlspecialchars($step['material_file']) ?>" class="ui blue button" target="_blank">
                                        <i class="file pdf icon"></i>
                                        Просмотр материала
                                    </a>
                                <?php elseif ($step['type_step'] === 'test' && $step['test_id']): ?>
                                    <a href="edit_test.php?test_id=<?= $step['test_id'] ?>" class="ui blue button">
                                        <i class="edit icon"></i>
                                        Редактировать тест
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
    
    $('#stepType').change(function() {
        if ($(this).val() === 'material') {
            $('#materialFileField').show();
            $('input[name="material_file"]').prop('required', true);
        } else {
            $('#materialFileField').hide();
            $('input[name="material_file"]').prop('required', false);
        }
    });
    
    $('.ui.form').form({
        fields: {
            name_step: 'empty',
            type_step: 'empty'
        }
    });
});
</script>

</body>
</html> 