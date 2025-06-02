<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $fn_user = trim($_POST['fn_user']);
    $birth_user = trim($_POST['birth_user']);
    $uni_user = trim($_POST['uni_user']);
    $spec_user = trim($_POST['spec_user']);
    $year_user = (int)$_POST['year_user'];
    $login_user = trim($_POST['login_user']);
    $password_user = trim($_POST['password_user']);
    $role_user = $_POST['role_user'] ?? 'student';

    // Валидация email
    if (!filter_var($login_user, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    }

    // Проверяем обязательные поля
    if (empty($fn_user) || empty($birth_user) || empty($uni_user) || 
        empty($spec_user) || empty($year_user) || empty($login_user) || 
        empty($password_user)) {
        $error = 'Заполните все обязательные поля';
    }

    // Проверка файлов
    $student_card_path = null;
    $passport_path = null;
    $diploma_path = null;
    $criminal_path = null;
    if ($role_user === 'student') {
        if (!isset($_FILES['student_card']) || $_FILES['student_card']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Загрузите фото студенческого билета';
        }
    } elseif ($role_user === 'teacher') {
        if (!isset($_FILES['passport_file']) || $_FILES['passport_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Загрузите паспорт';
        }
        if (!isset($_FILES['diploma_file']) || $_FILES['diploma_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Загрузите диплом';
        }
        if (!isset($_FILES['criminal_record_file']) || $_FILES['criminal_record_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Загрузите справку о несудимости';
        }
    }

    if (!$error) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login_user = ?");
            $stmt->execute([$login_user]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Пользователь с такой почтой уже существует';
            } else {
                $password_hash = password_hash($password_user, PASSWORD_DEFAULT);
                // Сохраняем файлы
                if ($role_user === 'student') {
                    $student_dir = 'uploads/students';
                    if (!file_exists($student_dir)) mkdir($student_dir, 0777, true);
                    $student_card_path = $student_dir . '/' . uniqid('student_card_') . '_' . basename($_FILES['student_card']['name']);
                    move_uploaded_file($_FILES['student_card']['tmp_name'], $student_card_path);
                } elseif ($role_user === 'teacher') {
                    $passport_dir = 'uploads/teachers/passport';
                    $diploma_dir = 'uploads/teachers/diploma';
                    $criminal_dir = 'uploads/teachers/criminal';
                    if (!file_exists($passport_dir)) mkdir($passport_dir, 0777, true);
                    if (!file_exists($diploma_dir)) mkdir($diploma_dir, 0777, true);
                    if (!file_exists($criminal_dir)) mkdir($criminal_dir, 0777, true);
                    $passport_path = $passport_dir . '/' . uniqid('passport_') . '_' . basename($_FILES['passport_file']['name']);
                    $diploma_path = $diploma_dir . '/' . uniqid('diploma_') . '_' . basename($_FILES['diploma_file']['name']);
                    $criminal_path = $criminal_dir . '/' . uniqid('criminal_') . '_' . basename($_FILES['criminal_record_file']['name']);
                    move_uploaded_file($_FILES['passport_file']['tmp_name'], $passport_path);
                    move_uploaded_file($_FILES['diploma_file']['tmp_name'], $diploma_path);
                    move_uploaded_file($_FILES['criminal_record_file']['tmp_name'], $criminal_path);
                }
                // Добавляем пользователя
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        fn_user, birth_user, uni_user, role_user, 
                        spec_user, year_user, login_user, password_user, status, student_card, passport_file, diploma_file, criminal_record_file
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $fn_user,
                    $birth_user,
                    $uni_user,
                    $role_user,
                    $spec_user,
                    $year_user,
                    $login_user,
                    $password_hash,
                    $student_card_path,
                    $passport_path,
                    $diploma_path,
                    $criminal_path
                ]);
                // Сообщение об успешной регистрации
                header('Location: register_success.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при регистрации: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <h1 class="ui header">Регистрация</h1>

    <?php if ($error): ?>
        <div class="ui error message">
            <div class="header">Ошибка</div>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" class="ui form" enctype="multipart/form-data">
        <div class="required field">
            <label>ФИО</label>
            <input type="text" name="fn_user" placeholder="Введите ФИО" required>
        </div>

        <div class="required field">
            <label>Дата рождения</label>
            <input type="date" name="birth_user" required>
        </div>

        <div class="required field">
            <label>Университет</label>
            <input type="text" name="uni_user" placeholder="Введите название университета" required>
        </div>

        <div class="required field">
            <label>Специальность</label>
            <input type="text" name="spec_user" placeholder="Введите специальность" required>
        </div>

        <div class="required field">
            <label>Год обучения</label>
            <select name="year_user" class="ui dropdown" required>
                <option value="">Выберите год обучения</option>
                <option value="1">1 курс</option>
                <option value="2">2 курс</option>
                <option value="3">3 курс</option>
                <option value="4">4 курс</option>
                <option value="5">5 курс</option>
                <option value="6">6 курс</option>
            </select>
        </div>

        <div class="required field">
            <label>Почта</label>
            <input type="text" name="login_user" placeholder="Введите почту" required>
        </div>

        <div class="required field">
            <label>Пароль</label>
            <input type="password" name="password_user" placeholder="Введите пароль" required>
        </div>

        <div class="required field">
            <label>Роль</label>
            <select name="role_user" class="ui dropdown" id="role_user_select" required>
                <option value="student">Студент</option>
                <option value="teacher">Преподаватель</option>
            </select>
        </div>

        <div class="required field" id="student_card_field">
            <label>Фото студенческого билета</label>
            <input type="file" name="student_card">
        </div>

        <div id="teacher_files" style="display:none;">
            <div class="required field">
                <label>Паспорт</label>
                <input type="file" name="passport_file">
            </div>
            <div class="required field">
                <label>Диплом</label>
                <input type="file" name="diploma_file">
            </div>
            <div class="required field">
                <label>Справка о несудимости</label>
                <input type="file" name="criminal_record_file">
            </div>
        </div>

        <button type="submit" class="ui primary button">Зарегистрироваться</button>
    </form>

    <div class="ui message">
        Уже есть аккаунт? <a href="login.php">Войти</a>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    function toggleRoleFields() {
        var role = $('#role_user_select').val();
        if (role === 'student') {
            $('#student_card_field').show();
            $('#teacher_files').hide();
        } else {
            $('#student_card_field').hide();
            $('#teacher_files').show();
        }
    }
    $('#role_user_select').change(toggleRoleFields);
    toggleRoleFields();
    
    $('.ui.form').form({
        fields: {
            fn_user: 'empty',
            birth_user: 'empty',
            uni_user: 'empty',
            spec_user: 'empty',
            year_user: 'empty',
            login_user: 'empty',
            password_user: 'empty'
        }
    });
});
</script>

</body>
</html>
