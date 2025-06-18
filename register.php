<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $fn_user = trim($_POST['fn_user']);
    $birth_user = trim($_POST['birth_user']);
    $login_user = trim($_POST['login_user']);
    $password_user = trim($_POST['password_user']);
    $role_user = $_POST['role_user'] ?? 'student';
    // Опциональные поля
    $uni_user = isset($_POST['uni_user']) ? trim($_POST['uni_user']) : null;
    $spec_user = isset($_POST['spec_user']) ? trim($_POST['spec_user']) : null;
    $year_user = isset($_POST['year_user']) ? (int)$_POST['year_user'] : null;

    // Валидация email
    if (!filter_var($login_user, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    }

    // Проверяем обязательные поля
    if (empty($fn_user) || empty($birth_user) || empty($login_user) || empty($password_user)) {
        $error = 'Заполните все обязательные поля';
    }

    // Проверка файлов только для преподавателей
    $passport_path = null;
    $diploma_path = null;
    $criminal_path = null;
    if ($role_user === 'teacher') {
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
                if ($role_user === 'teacher') {
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
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        fn_user, birth_user, uni_user, role_user, 
                        spec_user, year_user, login_user, password_user, status, student_card, passport_file, diploma_file, criminal_record_file
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)
                ");
                $status = ($role_user === 'student') ? 'approved' : 'pending';
                $stmt->execute([
                    $fn_user,
                    $birth_user,
                    $uni_user,
                    $role_user,
                    $spec_user,
                    $year_user,
                    $login_user,
                    $password_hash,
                    $status,
                    $passport_path,
                    $diploma_path,
                    $criminal_path
                ]);
                // После регистрации:
                $user_id = $pdo->lastInsertId('users_id_user_seq');
                
                // Копируем дефолтную аватарку для нового пользователя
                $default_avatar = 'avatars/default.jpg';
                $user_avatar = "avatars/{$user_id}.jpg";
                if (file_exists($default_avatar)) {
                    copy($default_avatar, $user_avatar);
                }
                
                $stmt = $pdo->prepare('SELECT * FROM users WHERE id_user = ?');
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                if ($role_user === 'student') {
                    // Сразу логиним студента
                    $_SESSION['user'] = $user;
                    $_SESSION['jwt'] = generate_jwt([
                        'id_user' => $user['id_user'],
                        'role_user' => $user['role_user'],
                        'exp' => time() + JWT_EXPIRATION
                    ]);
                    $subject = 'Добро пожаловать в CodeSphere!';
                    $message = '<p>Поздравляем с успешной регистрацией на платформе CodeSphere!</p><p>Теперь вы можете приступить к обучению и пользоваться всеми возможностями платформы.</p>';
                    send_email_smtp($login_user, $subject, $message);
                    header('Location: courses.php');
                    exit;
                } else {
                    // Для преподавателя — не логиним, а показываем сообщение
                    header('Location: register_success.php?role=teacher');
                    exit;
                }
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

        <!-- Кнопка для показа опциональных данных об обучении -->
        <div class="field">
            <button type="button" class="ui button" id="show_edu_fields_btn">Заполнить данные об обучении (опционально)</button>
        </div>
        <div id="edu_fields" style="display:none;">
            <div class="field">
                <label>Университет</label>
                <input type="text" name="uni_user" placeholder="Введите название университета">
            </div>
            <div class="field">
                <label>Специальность</label>
                <input type="text" name="spec_user" placeholder="Введите специальность">
            </div>
            <div class="field">
                <label>Год обучения</label>
                <select name="year_user" class="ui dropdown">
                    <option value="">Выберите год обучения</option>
                    <option value="1">1 курс</option>
                    <option value="2">2 курс</option>
                    <option value="3">3 курс</option>
                    <option value="4">4 курс</option>
                    <option value="5">5 курс</option>
                    <option value="6">6 курс</option>
                </select>
            </div>
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
        if (role === 'teacher') {
            $('#teacher_files').show();
        } else {
            $('#teacher_files').hide();
        }
    }
    $('#role_user_select').change(toggleRoleFields);
    toggleRoleFields();
    // Кнопка для показа/скрытия опциональных полей об обучении
    $('#show_edu_fields_btn').click(function() {
        $('#edu_fields').toggle();
    });
    $('.ui.form').form({
        fields: {
            fn_user: 'empty',
            birth_user: 'empty',
            login_user: 'empty',
            password_user: 'empty'
        }
    });
});
</script>

</body>
</html>
