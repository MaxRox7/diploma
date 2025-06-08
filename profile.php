<?php
require_once 'config.php';

// Проверка на авторизацию
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$message = '';
$error = '';

// Обработка загрузки аватарки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($file['type'], $allowed_types)) {
            $error = 'Разрешены только изображения в форматах JPG, PNG и GIF';
        } else {
            $avatar_dir = 'avatars';
            if (!file_exists($avatar_dir)) {
                mkdir($avatar_dir, 0777, true);
            }
            
            // Конвертируем все изображения в JPG для единообразия
            $image = null;
            switch($file['type']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file['tmp_name']);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($file['tmp_name']);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($file['tmp_name']);
                    break;
            }
            
            if ($image) {
                // Создаем квадратное изображение
                $width = imagesx($image);
                $height = imagesy($image);
                $size = min($width, $height);
                
                $square = imagecreatetruecolor(300, 300);
                imagecopyresampled(
                    $square, 
                    $image, 
                    0, 0, 
                    (int)(($width - $size) / 2), 
                    (int)(($height - $size) / 2), 
                    300, 300, 
                    $size, $size
                );
                
                $avatar_path = "{$avatar_dir}/{$user['id_user']}.jpg";
                imagejpeg($square, $avatar_path, 90);
                imagedestroy($square);
                imagedestroy($image);
                
                $message = 'Аватар успешно обновлен';
            } else {
                $error = 'Ошибка при обработке изображения';
            }
        }
    } elseif (isset($_POST['delete_avatar'])) {
        $avatar_path = "avatars/{$user['id_user']}.jpg";
        if (file_exists($avatar_path)) {
            unlink($avatar_path);
            $message = 'Аватар удален';
        }
    }
}

// Получаем путь к аватарке
$avatar_path = "avatars/{$user['id_user']}.jpg";
$has_avatar = file_exists($avatar_path);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    <style>
        .profile-header {
            margin-bottom: 2em !important;
        }
        .profile-info .item {
            padding: 1em 0;
            border-bottom: 1px solid rgba(34,36,38,.1);
        }
        .profile-info .item:last-child {
            border-bottom: none;
        }
        .profile-info .header {
            font-weight: bold;
            margin-bottom: 0.5em;
            color: #666;
        }
        .profile-info .content {
            font-size: 1.1em;
        }
        .avatar-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 2em;
            cursor: pointer;
        }
        .avatar-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .avatar-container:hover .avatar-overlay {
            opacity: 1;
        }
        .avatar-overlay .buttons {
            text-align: center;
            color: white;
        }
        #avatar-input {
            display: none;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <?php if ($message): ?>
        <div class="ui success message">
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="ui error message">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <div class="ui clearing segment profile-header">
        <h2 class="ui left floated header">
            <i class="user circle icon"></i>
            <div class="content">
                Профиль пользователя
                <?php if (is_admin()): ?>
                    <div class="sub header">Администратор</div>
                <?php elseif (is_teacher()): ?>
                    <div class="sub header">Преподаватель</div>
                <?php endif; ?>
            </div>
        </h2>
    </div>

    <div class="ui segment">
        <form id="avatar-form" method="post" enctype="multipart/form-data" class="ui form">
            <div class="avatar-container">
                <img src="<?= $has_avatar ? $avatar_path : 'avatars/default.jpg' ?>" alt="Аватар">
                <div class="avatar-overlay">
                    <div class="buttons">
                        <?php if ($has_avatar): ?>
                            <button type="button" class="ui inverted button change-avatar">Изменить</button>
                            <button type="submit" name="delete_avatar" class="ui inverted red button">Удалить</button>
                        <?php else: ?>
                            <button type="button" class="ui inverted button change-avatar">Загрузить аватар</button>
                        <?php endif; ?>
                    </div>
                </div>
                <input type="file" id="avatar-input" name="avatar" accept="image/*">
            </div>
        </form>

        <div class="ui relaxed divided list profile-info">
            <div class="item">
                <div class="header">Логин</div>
                <div class="content"><?= htmlspecialchars($user['login_user']) ?></div>
            </div>
            <div class="item">
                <div class="header">ФИО</div>
                <div class="content"><?= htmlspecialchars($user['fn_user']) ?></div>
            </div>
        </div>
    </div>

    <div class="ui segment">
        <h3 class="ui header">
            <i class="cog icon"></i>
            <div class="content">Действия</div>
        </h3>
        <a href="edit_profile.php" class="ui primary button">
            <i class="edit icon"></i>
            Редактировать профиль
        </a>
        <a href="learning_analytics.php" class="ui teal button">
            <i class="chart bar icon"></i>
            Аналитика обучения
        </a>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.change-avatar').click(function() {
        $('#avatar-input').click();
    });

    $('#avatar-input').change(function() {
        if (this.files && this.files[0]) {
            $('#avatar-form').submit();
        }
    });
});
</script>

</body>
</html>
