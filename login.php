<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_user = trim($_POST['login_user']);
    $password_user = trim($_POST['password_user']);

    if (empty($login_user) || empty($password_user)) {
        $error = 'Заполните все поля';
    } else {
        try {
            $pdo = get_db_connection();
            
            // Получаем пользователя по логину
            $stmt = $pdo->prepare("SELECT * FROM users WHERE login_user = ?");
            $stmt->execute([$login_user]);
            $user = $stmt->fetch();

            if ($user && password_verify($password_user, $user['password_user'])) {
                if (empty($user['status'])) {
                    // Если статус не установлен, считаем студента одобренным, преподавателя — на модерации
                    if ($user['role_user'] === 'teacher') {
                        $user['status'] = 'pending';
                    } else {
                        $user['status'] = 'approved';
                    }
                }
                if ($user['status'] === 'banned') {
                    $error = 'Ваш аккаунт заблокирован администратором.';
                } elseif ($user['status'] !== 'approved') {
                    $error = 'Ваша заявка ещё не одобрена администратором.';
                } else {
                    // Создаем JWT токен
                    $payload = [
                        'user_id' => $user['id_user'],
                        'login' => $user['login_user'],
                        'role' => $user['role_user'],
                        'exp' => time() + JWT_EXPIRATION
                    ];
                    
                    $jwt = generate_jwt($payload);
                    
                    // Сохраняем токен и данные пользователя в сессии
                    $_SESSION['jwt'] = $jwt;
                    $_SESSION['user'] = $user;
                    
                    header('Location: courses.php');
                    exit;
                }
            } else {
                $error = 'Неверный логин или пароль';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при входе: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <h1 class="ui header">Вход в систему</h1>

    <?php if ($error): ?>
        <div class="ui error message">
            <div class="header">Ошибка</div>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" class="ui form">
        <div class="required field">
            <label>Логин</label>
            <input type="text" name="login_user" placeholder="Введите логин" required>
        </div>

        <div class="required field">
            <label>Пароль</label>
            <input type="password" name="password_user" placeholder="Введите пароль" required>
        </div>

        <button type="submit" class="ui primary button">Войти</button>
    </form>

    <div class="ui message">
        Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.form').form({
        fields: {
            login_user: 'empty',
            password_user: 'empty'
        }
    });
});
</script>

</body>
</html>
