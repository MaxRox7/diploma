<?php
// Страница успешной регистрации
$role = $_GET['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация успешна - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="ui container" style="margin-top: 50px;">
    <?php if ($role === 'teacher'): ?>
        <div class="ui info message">
            <div class="header">Ваша заявка отправлена на модерацию</div>
            <p>Спасибо за регистрацию! Ваша заявка будет рассмотрена администратором. После одобрения вы получите уведомление на почту и сможете войти в систему.</p>
        </div>
    <?php else: ?>
        <div class="ui positive message">
            <div class="header">Регистрация прошла успешно!</div>
            <p>Поздравляем! Теперь вы можете войти в систему и начать пользоваться платформой.</p>
        </div>
    <?php endif; ?>
    <a href="index.php" class="ui button">На главную</a>
    <a href="login.php" class="ui primary button">Войти</a>
</div>
</body>
</html> 