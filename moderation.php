<?php
require_once 'config.php';
redirect_unauthenticated();
if (!is_admin()) {
    header('Location: index.php');
    exit;
}

$pdo = get_db_connection();
$error = '';
$success = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'approve') $success = 'Заявка одобрена.';
    elseif ($_GET['success'] === 'reject') $success = 'Заявка отклонена.';
    elseif ($_GET['success'] === 'edit') $success = 'Данные пользователя обновлены.';
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// Обработка действий админа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $moderation_comment = trim($_POST['moderation_comment'] ?? '');
    $edit_fields = [
        'fn_user' => trim($_POST['fn_user'] ?? ''),
        'birth_user' => trim($_POST['birth_user'] ?? ''),
        'uni_user' => trim($_POST['uni_user'] ?? ''),
        'spec_user' => trim($_POST['spec_user'] ?? ''),
        'year_user' => (int)($_POST['year_user'] ?? 0),
        'login_user' => trim($_POST['login_user'] ?? ''),
    ];
    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE users SET status='approved', moderation_comment=? WHERE id_user=?");
            $stmt->execute([$moderation_comment, $user_id]);
            // Отправка письма
            $stmt = $pdo->prepare("SELECT login_user, fn_user FROM users WHERE id_user=?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            if ($user) {
                $html = '<div style="font-family:Arial,sans-serif;font-size:16px;color:#222;max-width:600px;margin:auto;padding:24px;border:1px solid #e0e0e0;border-radius:8px;">
                    <h2 style="color:#2185d0;">Ваша заявка на регистрацию одобрена!</h2>
                    <p>Здравствуйте, <b>' . htmlspecialchars($user['fn_user']) . '</b>!</p>
                    <p>Ваша заявка на регистрацию в системе <b>CodeSphere</b> была успешно одобрена администратором.</p>
                    <p>Теперь вы можете войти в систему, используя свою почту и пароль.</p>
                    <p style="margin-top:32px;color:#888;font-size:14px;">С уважением,<br>Команда CodeSphere</p>
                </div>';
                send_email_smtp($user['login_user'], 'Ваша заявка одобрена — CodeSphere', $html);
            }
            header('Location: moderation.php?success=approve');
            exit;
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE users SET status='rejected', moderation_comment=? WHERE id_user=?");
            $stmt->execute([$moderation_comment, $user_id]);
            // Отправка письма
            $stmt = $pdo->prepare("SELECT login_user, fn_user FROM users WHERE id_user=?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            if ($user) {
                $html = '<div style="font-family:Arial,sans-serif;font-size:16px;color:#222;max-width:600px;margin:auto;padding:24px;border:1px solid #e0e0e0;border-radius:8px;">
                    <h2 style="color:#d01919;">Ваша заявка на регистрацию отклонена</h2>
                    <p>Здравствуйте, <b>' . htmlspecialchars($user['fn_user']) . '</b>!</p>
                    <p>К сожалению, ваша заявка на регистрацию в системе <b>CodeSphere</b> была отклонена администратором.</p>';
                if ($moderation_comment) {
                    $html .= '<p><b>Причина:</b> ' . htmlspecialchars($moderation_comment) . '</p>';
                }
                $html .= '<p style="margin-top:32px;color:#888;font-size:14px;">С уважением,<br>Команда CodeSphere</p></div>';
                send_email_smtp($user['login_user'], 'Ваша заявка отклонена — CodeSphere', $html);
            }
            header('Location: moderation.php?success=reject');
            exit;
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE users SET fn_user=?, birth_user=?, uni_user=?, spec_user=?, year_user=?, login_user=? WHERE id_user=?");
            $stmt->execute([
                $edit_fields['fn_user'],
                $edit_fields['birth_user'],
                $edit_fields['uni_user'],
                $edit_fields['spec_user'],
                $edit_fields['year_user'],
                $edit_fields['login_user'],
                $user_id
            ]);
            header('Location: moderation.php?success=edit');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: moderation.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

// Получаем заявки с учётом поиска
$search = trim($_GET['search'] ?? '');
$where = "WHERE status='pending'";
$params = [];
if ($search !== '') {
    $where .= " AND (fn_user ILIKE ? OR login_user ILIKE ? OR uni_user ILIKE ? OR spec_user ILIKE ? OR CAST(year_user AS TEXT) ILIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 5, $search_param);
}
$students = $pdo->prepare("SELECT * FROM users $where AND role_user='student' ORDER BY id_user");
$students->execute($params);
$students = $students->fetchAll();
$teachers = $pdo->prepare("SELECT * FROM users $where AND role_user='teacher' ORDER BY id_user");
$teachers->execute($params);
$teachers = $teachers->fetchAll();

function file_link($path) {
    return $path && file_exists($path) ? '<a href="' . htmlspecialchars($path) . '" target="_blank">Смотреть</a>' : '<span style="color:#888">Нет</span>';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Модерация заявок - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="ui container" style="margin-top: 40px;">
    <h2 class="ui header">Модерация заявок</h2>
    <?php if ($error): ?>
        <div class="ui error message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="ui success message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <div class="ui top attached tabular menu">
        <a class="item active" data-tab="students">Студенты</a>
        <a class="item" data-tab="teachers">Преподаватели</a>
    </div>
    <div class="ui bottom attached tab segment active" data-tab="students">
        <?php if (empty($students)): ?>
            <div class="ui message">Нет заявок студентов.</div>
        <?php else: ?>
            <form method="get" class="ui form" style="margin-bottom:20px;">
                <div class="ui action input" style="width:350px;">
                    <input type="text" name="search" placeholder="Поиск по имени, почте, вузу, спецу, году..." value="<?= htmlspecialchars($search) ?>">
                    <button class="ui button" type="submit">Поиск</button>
                    <a href="moderation.php" class="ui button">Сбросить</a>
                </div>
            </form>
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>ID</th><th>ФИО</th><th>Почта</th><th>Университет</th><th>Спец.</th><th>Год</th><th>Студ. билет</th><th>Комментарий</th><th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $u): ?>
                    <tr>
                        <form method="post">
                        <td><?= $u['id_user'] ?><input type="hidden" name="user_id" value="<?= $u['id_user'] ?>"></td>
                        <td><input type="text" name="fn_user" value="<?= htmlspecialchars($u['fn_user']) ?>" style="width:120px"></td>
                        <td><input type="email" name="login_user" value="<?= htmlspecialchars($u['login_user']) ?>" style="width:150px"></td>
                        <td><input type="text" name="uni_user" value="<?= htmlspecialchars($u['uni_user']) ?>" style="width:100px"></td>
                        <td><input type="text" name="spec_user" value="<?= htmlspecialchars($u['spec_user']) ?>" style="width:100px"></td>
                        <td><input type="number" name="year_user" value="<?= htmlspecialchars($u['year_user']) ?>" style="width:60px"></td>
                        <td><?= file_link($u['student_card']) ?></td>
                        <td><input type="text" name="moderation_comment" value="<?= htmlspecialchars($u['moderation_comment'] ?? '') ?>" style="width:120px"></td>
                        <td>
                            <button class="ui green mini button" name="action" value="approve">Одобрить</button>
                            <button class="ui red mini button" name="action" value="reject">Отклонить</button>
                            <button class="ui blue mini button" name="action" value="edit">Сохранить</button>
                        </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <div class="ui bottom attached tab segment" data-tab="teachers">
        <?php if (empty($teachers)): ?>
            <div class="ui message">Нет заявок преподавателей.</div>
        <?php else: ?>
            <form method="get" class="ui form" style="margin-bottom:20px;">
                <div class="ui action input" style="width:350px;">
                    <input type="text" name="search" placeholder="Поиск по имени, почте, вузу, спецу, году..." value="<?= htmlspecialchars($search) ?>">
                    <button class="ui button" type="submit">Поиск</button>
                    <a href="moderation.php" class="ui button">Сбросить</a>
                </div>
            </form>
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>ID</th><th>ФИО</th><th>Почта</th><th>Университет</th><th>Спец.</th><th>Год</th><th>Паспорт</th><th>Диплом</th><th>Справка</th><th>Комментарий</th><th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($teachers as $u): ?>
                    <tr>
                        <form method="post">
                        <td><?= $u['id_user'] ?><input type="hidden" name="user_id" value="<?= $u['id_user'] ?>"></td>
                        <td><input type="text" name="fn_user" value="<?= htmlspecialchars($u['fn_user']) ?>" style="width:120px"></td>
                        <td><input type="email" name="login_user" value="<?= htmlspecialchars($u['login_user']) ?>" style="width:150px"></td>
                        <td><input type="text" name="uni_user" value="<?= htmlspecialchars($u['uni_user']) ?>" style="width:100px"></td>
                        <td><input type="text" name="spec_user" value="<?= htmlspecialchars($u['spec_user']) ?>" style="width:100px"></td>
                        <td><input type="number" name="year_user" value="<?= htmlspecialchars($u['year_user']) ?>" style="width:60px"></td>
                        <td><?= file_link($u['passport_file']) ?></td>
                        <td><?= file_link($u['diploma_file']) ?></td>
                        <td><?= file_link($u['criminal_record_file']) ?></td>
                        <td><input type="text" name="moderation_comment" value="<?= htmlspecialchars($u['moderation_comment'] ?? '') ?>" style="width:120px"></td>
                        <td>
                            <button class="ui green mini button" name="action" value="approve">Одобрить</button>
                            <button class="ui red mini button" name="action" value="reject">Отклонить</button>
                            <button class="ui blue mini button" name="action" value="edit">Сохранить</button>
                        </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
<script>
$(function() {
    $('.menu .item').tab();
});
</script>
</body>
</html> 