<?php
if (!isset($user) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

// Получаем путь к аватарке
function get_avatar_path($user_id) {
    $avatar_path = "avatars/{$user_id}.jpg";
    return file_exists($avatar_path) ? $avatar_path : "avatars/default.jpg";
}
?>
<div class="ui menu">
    <div class="ui container">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="courses.php" class="header item">CodeSphere</a>
        <?php else: ?>
            <a href="index.php" class="header item">CodeSphere</a>
        <?php endif; ?>
        
        <div class="right menu">
            <?php if (isset($_SESSION['user'])): ?>
                <div class="search item">
                    <form class="ui search" action="courses.php" method="GET">
                        <div class="ui icon input">
                            <input type="text" name="search" placeholder="Поиск курсов..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                            <i class="search icon"></i>
                        </div>
                    </form>
                </div>
                
                <a href="courses.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : '' ?>">
                    <i class="book icon"></i>
                    Курсы
                </a>
                <?php if (is_teacher()): ?>
                    <a href="my_courses.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'my_courses.php' ? 'active' : '' ?>">
                        <i class="folder open icon"></i>
                        Мои курсы
                    </a>
                <?php endif; ?>
                <?php if (is_admin() || is_teacher()): ?>
                    <?php if (basename($_SERVER['PHP_SELF']) === 'courses.php'): ?>
                        <a href="add_course.php" class="item">
                            <i class="plus icon"></i>
                            Добавить курс
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (is_admin()): ?>
                    <a href="moderation.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'moderation.php' ? 'active' : '' ?>">
                        <i class="users icon"></i>
                        Заявки
                    </a>
                <?php endif; ?>
                
                <div class="ui dropdown item">
                    <img class="ui avatar image" src="<?= get_avatar_path($_SESSION['user']['id_user']) ?>">
                    <span style="margin-left: 5px;"><?= htmlspecialchars($_SESSION['user']['fn_user']) ?></span>
                    <i class="dropdown icon"></i>
                    <div class="menu">
                        <a href="profile.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                            <i class="user icon"></i>
                            Профиль
                        </a>
                        <?php if (is_student()): ?>
                        <a href="learning_analytics.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'learning_analytics.php' ? 'active' : '' ?>">
                            <i class="chart bar icon"></i>
                            Аналитика обучения
                        </a>
                        <?php elseif (is_teacher()): ?>
                        <a href="teacher_analytics.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'teacher_analytics.php' ? 'active' : '' ?>">
                            <i class="chart bar icon"></i>
                            Аналитика преподавателя
                        </a>
                        <?php elseif (is_admin()): ?>
                        <a href="admin_analytics.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'admin_analytics.php' ? 'active' : '' ?>">
                            <i class="chart line icon"></i>
                            Аналитика платформы
                        </a>
                        <?php endif; ?>
                        <a href="logout.php" class="item">
                            <i class="sign out icon"></i>
                            Выход
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : '' ?>">
                    <i class="sign in icon"></i>
                    Войти
                </a>
                <a href="register.php" class="item <?= basename($_SERVER['PHP_SELF']) === 'register.php' ? 'active' : '' ?>">
                    <i class="user plus icon"></i>
                    Регистрация
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.ui.menu {
    margin-bottom: 0;
    border-radius: 0;
    background-color: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.ui.menu .header.item {
    font-size: 1.2em;
    font-weight: bold;
    color: #2185d0;
}
.ui.menu .right.menu {
    margin-left: auto !important;
}
.ui.menu .item {
    padding: 1em 1.2em;
}
.search.item {
    padding-right: 0.5em !important;
}
.ui.menu .ui.icon.input {
    width: 250px;
}
.ui.menu .ui.dropdown.item img.avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 5px;
}
@media only screen and (max-width: 768px) {
    .ui.menu .ui.icon.input {
        width: 150px;
    }
    .ui.menu .item {
        padding: 0.8em 0.5em;
    }
}
</style>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
});
</script> 