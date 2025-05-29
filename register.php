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
    
    // Проверяем обязательные поля
    if (empty($fn_user) || empty($birth_user) || empty($uni_user) || 
        empty($spec_user) || empty($year_user) || empty($login_user) || 
        empty($password_user)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            // Подключаемся к базе данных
            $pdo = get_db_connection();
            
            // Проверяем, существует ли пользователь с таким логином
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login_user = ?");
            $stmt->execute([$login_user]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Пользователь с таким логином уже существует';
            } else {
                // Хешируем пароль
                $password_hash = password_hash($password_user, PASSWORD_DEFAULT);
                
                // Добавляем нового пользователя
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        fn_user, birth_user, uni_user, role_user, 
                        spec_user, year_user, login_user, password_user
                    ) VALUES (?, ?, ?, 'student', ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $fn_user,
                    $birth_user,
                    $uni_user,
                    $spec_user,
                    $year_user,
                    $login_user,
                    $password_hash
                ]);
                
                // Получаем данные созданного пользователя
                $stmt = $pdo->prepare("
                    SELECT * FROM users 
                    WHERE login_user = ?
                ");
                $stmt->execute([$login_user]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Создаем JWT токен
                $payload = [
                    'user_id' => $user['id_user'],
                    'login' => $user['login_user'],
                    'role' => 'student',
                    'exp' => time() + JWT_EXPIRATION
                ];
                
                $jwt = generate_jwt($payload);
                
                // Сохраняем токен в сессии
                $_SESSION['jwt'] = $jwt;
                $_SESSION['user'] = $user;
                
                header('Location: courses.php');
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
<div class="ui menu">
    <div class="ui container">
        <a href="index.php" class="header item">CodeSphere</a>
        <div class="right menu">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="courses.php" class="item">Курсы</a>
                <a href="profile.php" class="item">Профиль</a>
                <a href="logout.php" class="item">Выход</a>
            <?php else: ?>
                <a href="login.php" class="item">Войти</a>
                <a href="register.php" class="active item">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="ui container" style="margin-top: 50px;">
    <h1 class="ui header">Регистрация</h1>

    <?php if ($error): ?>
        <div class="ui error message">
            <div class="header">Ошибка</div>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" class="ui form">
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
            <label>Логин</label>
            <input type="text" name="login_user" placeholder="Введите логин" required>
        </div>

        <div class="required field">
            <label>Пароль</label>
            <input type="password" name="password_user" placeholder="Введите пароль" required>
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
