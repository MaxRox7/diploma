<?php
require_once 'config.php';
redirect_unauthenticated();

// Only teachers can access this page
if (!is_teacher()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user']['id_user'];
$pdo = get_db_connection();
$error = '';
$success = '';

// Handle sending course to moderation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_to_moderation') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    
    // Check if the user is the creator of this course
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM create_passes WHERE id_course = ? AND id_user = ? AND is_creator = true");
    $stmt->execute([$course_id, $user_id]);
    
    if ($stmt->fetchColumn() > 0) {
        try {
            // Update course status to pending
            $stmt = $pdo->prepare("UPDATE course SET status_course='pending', moderation_comment=NULL WHERE id_course=?");
            $stmt->execute([$course_id]);
            $success = 'Курс отправлен на модерацию';
        } catch (PDOException $e) {
            $error = 'Ошибка при отправке курса на модерацию: ' . $e->getMessage();
        }
    } else {
        $error = 'У вас нет прав для этого действия';
    }
}

// Get all courses created by this teacher
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cp.date_complete 
        FROM course c 
        JOIN create_passes cp ON c.id_course = cp.id_course 
        WHERE cp.id_user = ? AND cp.is_creator = true 
        ORDER BY c.id_course DESC
    ");
    $stmt->execute([$user_id]);
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Ошибка при получении списка курсов: ' . $e->getMessage();
    $courses = [];
}

// Get status labels and colors
function get_status_label($status) {
    switch ($status) {
        case 'draft':
            return ['Черновик', 'grey'];
        case 'pending':
            return ['На модерации', 'yellow'];
        case 'correction':
            return ['Требует доработки', 'orange'];
        case 'approved':
            return ['Одобрен', 'green'];
        case 'rejected':
            return ['Отклонен', 'red'];
        default:
            return ['Неизвестно', 'black'];
    }
}

<?php include_once 'courses.php'; ?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои курсы - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <div class="ui grid">
        <div class="sixteen wide column">
            <div class="ui clearing segment">
                <h2 class="ui left floated header">
                    Мои курсы
                    <div class="sub header">Управление созданными вами курсами</div>
                </h2>
                <div class="ui right floated buttons">
                    <a href="add_course.php" class="ui primary button">
                        <i class="plus icon"></i> Создать новый курс
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="ui error message">
                    <div class="header">Ошибка</div>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="ui success message">
                    <div class="header">Успех</div>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($courses)): ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="book icon"></i>
                        У вас пока нет созданных курсов
                    </div>
                    <a href="add_course.php" class="ui primary button">Создать курс</a>
                </div>
            <?php else: ?>
                <div class="ui three stackable cards">
                    <?php foreach ($courses as $course): ?>
                        <?php render_course_card($course); ?>
                        <div class="ui segment" style="border-top: none; border-radius: 0 0 8px 8px;">
                            <?php 
                            $status_info = get_status_label($course['status_course']);
                            $status_label = $status_info[0];
                            $status_color = $status_info[1];
                            ?>
                            <div class="ui <?= $status_color ?> label"><?= htmlspecialchars($status_label) ?></div>
                            <a href="edit_course.php?id=<?= $course['id_course'] ?>" class="ui blue button"><i class="edit icon"></i> Редактировать</a>
                            <a href="course.php?id=<?= $course['id_course'] ?>" class="ui button"><i class="eye icon"></i> Просмотр</a>
                            <?php if ($course['status_course'] === 'draft' || $course['status_course'] === 'correction'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="send_to_moderation">
                                    <input type="hidden" name="course_id" value="<?= $course['id_course'] ?>">
                                    <button type="submit" class="ui orange button"><i class="paper plane icon"></i> Отправить на модерацию</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($course['status_course'] === 'correction' && !empty($course['moderation_comment'])): ?>
                                <div class="ui warning message" style="margin-top: 10px;">
                                    <div class="header">Комментарий модератора:</div>
                                    <p><?= nl2br(htmlspecialchars($course['moderation_comment'])) ?></p>
                                </div>
                            <?php elseif ($course['status_course'] === 'rejected' && !empty($course['moderation_comment'])): ?>
                                <div class="ui negative message" style="margin-top: 10px;">
                                    <div class="header">Причина отклонения:</div>
                                    <p><?= nl2br(htmlspecialchars($course['moderation_comment'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html> 