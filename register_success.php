<?php
// Страница успешной регистрации
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
    <div class="ui positive message">
        <div class="header">Регистрация прошла успешно!</div>
        <p>Ваша заявка отправлена на модерацию. После проверки вы получите письмо на почту, и сможете войти в систему.</p>
    </div>
    <a href="index.php" class="ui button">На главную</a>
    <a href="login.php" class="ui primary button">Войти</a>
</div>
</body>
</html> 