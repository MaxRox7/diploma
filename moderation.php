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
                $subject = 'Ваша заявка одобрена';
                $body = '
                <h2 style="color:#2185d0;">Поздравляем!</h2>
                <p>Уважаемый(ая) ' . htmlspecialchars($user['fn_user']) . ',</p>
                <p>Мы рады сообщить, что Ваша заявка на регистрацию в CodeSphere одобрена.</p>
                <p>Теперь Вы можете войти в систему, используя свой email и пароль, указанные при регистрации.</p>
                <p>Добро пожаловать в наше образовательное сообщество!</p>';
                send_email_smtp($user['login_user'], $subject, $body);
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
                $subject = 'Ваша заявка отклонена';
                $body = '
                <h2 style="color:#db2828;">Заявка отклонена</h2>
                <p>Уважаемый(ая) ' . htmlspecialchars($user['fn_user']) . ',</p>
                <p>К сожалению, Ваша заявка на регистрацию в CodeSphere была отклонена.</p>';
                if ($moderation_comment) {
                    $body .= '<p><b>Причина отклонения:</b><br>' . nl2br(htmlspecialchars($moderation_comment)) . '</p>';
                }
                $body .= '<p>Если у Вас возникли вопросы, пожалуйста, свяжитесь с нашей службой поддержки.</p>';
                send_email_smtp($user['login_user'], $subject, $body);
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

    if (isset($_POST['action']) && in_array($_POST['action'], ['approve_course','correction_course','reject_course'])) {
        $course_id = (int)($_POST['course_id'] ?? 0);
        $moderation_comment = trim($_POST['moderation_comment'] ?? '');
        $status = $_POST['action'] === 'approve_course' ? 'approved' : ($_POST['action'] === 'correction_course' ? 'correction' : 'rejected');
        $stmt = $pdo->prepare("UPDATE course SET status_course=?, moderation_comment=? WHERE id_course=?");
        $stmt->execute([$status, $moderation_comment, $course_id]);
        // Получаем автора
        $stmt = $pdo->prepare("SELECT u.login_user, u.fn_user, c.name_course FROM users u JOIN create_passes cp ON u.id_user=cp.id_user JOIN course c ON cp.id_course=c.id_course WHERE cp.id_course=? AND cp.is_creator=true");
        $stmt->execute([$course_id]);
        $author = $stmt->fetch();
        if ($author) {
            $subject = '';
            $body = '';
            if ($status === 'approved') {
                $subject = 'Ваш курс одобрен!';
                $body = '
                <h2 style="color:#2185d0;">Поздравляем!</h2>
                <p>Уважаемый преподаватель,</p>
                <p>Мы рады сообщить, что Ваш курс <b>' . htmlspecialchars($author['name_course']) . '</b> успешно прошёл модерацию и теперь доступен студентам на платформе CodeSphere.</p>
                <p>Теперь студенты могут записаться на Ваш курс и начать обучение. Вы можете отслеживать прогресс студентов в личном кабинете.</p>
                <p>Благодарим Вас за вклад в развитие нашей образовательной платформы!</p>';
            } elseif ($status === 'correction') {
                $subject = 'Курс отправлен на доработку';
                $body = '
                <h2 style="color:#f2711c;">Курс требует доработки</h2>
                <p>Уважаемый преподаватель,</p>
                <p>Ваш курс <b>' . htmlspecialchars($author['name_course']) . '</b> был рассмотрен нашими модераторами, и мы обнаружили некоторые аспекты, требующие доработки.</p>';
                if ($moderation_comment) {
                    $body .= '<p><b>Комментарий модератора:</b><br>' . nl2br(htmlspecialchars($moderation_comment)) . '</p>';
                }
                $body .= '<p>Пожалуйста, внесите необходимые изменения и повторно отправьте курс на модерацию. После внесения всех необходимых корректировок, Ваш курс будет опубликован на платформе.</p>
                <p>Если у Вас возникнут вопросы, не стесняйтесь обращаться в службу поддержки.</p>';
            } else {
                $subject = 'Курс отклонён';
                $body = '
                <h2 style="color:#db2828;">Курс отклонён</h2>
                <p>Уважаемый преподаватель,</p>
                <p>К сожалению, Ваш курс <b>' . htmlspecialchars($author['name_course']) . '</b> не был одобрен для публикации на платформе CodeSphere.</p>';
                if ($moderation_comment) {
                    $body .= '<p><b>Причина отклонения:</b><br>' . nl2br(htmlspecialchars($moderation_comment)) . '</p>';
                }
                $body .= '<p>Вы можете создать новый курс с учетом указанных замечаний или обратиться в службу поддержки для получения дополнительной информации.</p>
                <p>Мы ценим Ваше стремление делиться знаниями и надеемся на дальнейшее сотрудничество.</p>';
            }
            send_email_smtp($author['login_user'], $subject, $body);
        }
        header('Location: moderation.php?tab=courses&success=1');
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
        <a href="?tab=students" class="item<?= !isset($_GET['tab']) || $_GET['tab'] === 'students' ? ' active' : '' ?>" data-tab="students">Студенты</a>
        <a href="?tab=teachers" class="item<?= isset($_GET['tab']) && $_GET['tab'] === 'teachers' ? ' active' : '' ?>" data-tab="teachers">Преподаватели</a>
        <a href="?tab=courses" class="item<?= isset($_GET['tab']) && $_GET['tab'] === 'courses' ? ' active' : '' ?>" data-tab="courses">Курсы</a>
    </div>
    <div class="ui bottom attached tab segment<?= !isset($_GET['tab']) || $_GET['tab'] === 'students' ? ' active' : '' ?>" data-tab="students">
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
    <div class="ui bottom attached tab segment<?= isset($_GET['tab']) && $_GET['tab'] === 'teachers' ? ' active' : '' ?>" data-tab="teachers">
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
    <div class="ui bottom attached tab segment<?= isset($_GET['tab']) && $_GET['tab'] === 'courses' ? ' active' : '' ?>" data-tab="courses">
        <h2 class="ui header">Заявки на курсы</h2>
        <?php
        // Получаем курсы на модерации
        $courses = $pdo->query("SELECT c.*, u.fn_user, u.login_user FROM course c JOIN create_passes cp ON c.id_course=cp.id_course AND cp.is_creator=true JOIN users u ON cp.id_user=u.id_user WHERE c.status_course IN ('pending','correction') ORDER BY c.id_course DESC")->fetchAll();
        if ($courses):
        ?>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Автор</th>
                    <th>Описание</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= $course['id_course'] ?></td>
                    <td><?= htmlspecialchars($course['name_course']) ?></td>
                    <td><?= htmlspecialchars($course['fn_user']) ?><br><small><?= htmlspecialchars($course['login_user']) ?></small></td>
                    <td><?= nl2br(htmlspecialchars(mb_substr($course['desc_course'],0,200))) ?></td>
                    <td>
                        <a href="?tab=courses&view=<?= $course['id_course'] ?>" class="ui button">Просмотреть</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="ui message">Нет курсов на модерации.</div>
        <?php endif; ?>

        <?php
        // Просмотр курса
        if (isset($_GET['view'])) {
            $cid = (int)$_GET['view'];
            $stmt = $pdo->prepare("SELECT c.*, u.fn_user, u.login_user, cp.id_user as author_id FROM course c JOIN create_passes cp ON c.id_course=cp.id_course AND cp.is_creator=true JOIN users u ON cp.id_user=u.id_user WHERE c.id_course=?");
            $stmt->execute([$cid]);
            $course = $stmt->fetch();
            if ($course):
                // Получаем уроки и шаги
                $lessons = $pdo->prepare("SELECT * FROM lessons WHERE id_course=? ORDER BY id_lesson");
                $lessons->execute([$cid]);
                $lessons = $lessons->fetchAll();
        ?>
        <div class="ui segment">
            <h3 class="ui header">Курс: <?= htmlspecialchars($course['name_course']) ?></h3>
            <p><b>Автор:</b> <?= htmlspecialchars($course['fn_user']) ?> (<?= htmlspecialchars($course['login_user']) ?>)</p>
            <p><b>Описание:</b> <?= nl2br(htmlspecialchars($course['desc_course'])) ?></p>
            <p><b>Требования:</b> <?= htmlspecialchars($course['requred_year'] ?? '') ?> курс, <?= htmlspecialchars($course['required_spec'] ?? '') ?>, <?= htmlspecialchars($course['required_uni'] ?? '') ?></p>
            <h4>Уроки и шаги:</h4>
            <ul>
                <?php foreach ($lessons as $lesson): ?>
                    <li><b><?= htmlspecialchars($lesson['name_lesson']) ?></b>
                        <?php
                        $steps = $pdo->prepare("SELECT * FROM Steps WHERE id_lesson=? ORDER BY id_step");
                        $steps->execute([$lesson['id_lesson']]);
                        $steps = $steps->fetchAll();
                        if ($steps): ?>
                            <ul>
                            <?php foreach ($steps as $step): ?>
                                <li><?= htmlspecialchars($step['number_steps'] ?? 'Без названия') ?> (<?= htmlspecialchars($step['type_step'] ?? 'unknown') ?>)
                                    <?php
                                    // Материалы
                                    $materials = $pdo->prepare("SELECT * FROM Material WHERE id_step=?");
                                    $materials->execute([$step['id_step']]);
                                    $materials = $materials->fetchAll();
                                    if ($materials): ?>
                                        <ul>
                                        <?php foreach ($materials as $mat): ?>
                                            <li>Материал: <a href="<?= htmlspecialchars(($mat['path_matial'] ?? '') ?: ($mat['link_material'] ?? '')) ?>" target="_blank" class="ui mini button">Открыть материал</a></li>
                                        <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <?php
                                    // Тесты
                                    $tests = $pdo->prepare("SELECT * FROM Tests WHERE id_step=?");
                                    $tests->execute([$step['id_step']]);
                                    $tests = $tests->fetchAll();
                                    if ($tests): ?>
                                        <ul>
                                        <?php foreach ($tests as $test): ?>
                                            <li>Тест: <?= htmlspecialchars($test['name_test']) ?> (<?= $test['id_test'] ?>)
                                                <a href="test.php?test_id=<?= $test['id_test'] ?>&admin_view=1" target="_blank" class="ui mini button">Пройти</a>
                                            </li>
                                        <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form method="post" class="ui form" style="margin-top:20px;">
                <input type="hidden" name="course_id" value="<?= $course['id_course'] ?>">
                <div class="field">
                    <label>Комментарий автору (если требуется)</label>
                    <textarea name="moderation_comment" rows="2"></textarea>
                </div>
                <button name="action" value="approve_course" class="ui positive button">Одобрить</button>
                <button name="action" value="correction_course" class="ui orange button">На доработку</button>
                <button name="action" value="reject_course" class="ui negative button">Отклонить</button>
                <a href="?tab=courses" class="ui button">Назад</a>
                <a href="edit_lessons.php?course_id=<?= $course['id_course'] ?>&admin_view=1" class="ui violet button" target="_blank">
                    <i class="list icon"></i> Управление уроками как преподаватель
                </a>
            </form>
        </div>
        <?php endif; } ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
<script>
$(function() {
    // Переключение вкладок по клику с изменением URL
    $('.menu .item').on('click', function(e) {
        e.preventDefault();
        const tab = $(this).data('tab');
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.location.href = url.toString();
    });
});
</script>
</body>
</html> 