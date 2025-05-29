<?php
require_once 'config.php';
redirect_unauthenticated();

$error = '';

try {
    $pdo = get_db_connection();
    
    // Добавляем поиск
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $search_condition = '';
    $params = [];
    
    if (!empty($search)) {
        $search_condition = "AND (
            c.name_course ILIKE ? OR 
            c.desc_course ILIKE ? OR 
            c.tags_course ILIKE ?
        )";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param];
    }
    
    // Получаем курсы с учетом поиска
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(DISTINCT cp.id_user) as students_count,
               COUNT(DISTINCT l.id_lesson) as lessons_count,
               AVG(CAST(f.rate_feedback AS FLOAT)) as average_rating,
               COUNT(DISTINCT f.id_feedback) as feedback_count,
               EXISTS(
                   SELECT 1 
                   FROM create_passes cp2 
                   WHERE cp2.id_course = c.id_course 
                   AND cp2.id_user = ?
               ) as is_enrolled
        FROM course c
        LEFT JOIN create_passes cp ON c.id_course = cp.id_course
        LEFT JOIN lessons l ON c.id_course = l.id_course
        LEFT JOIN feedback f ON c.id_course = f.id_course
        WHERE 1=1 " . $search_condition . "
        GROUP BY c.id_course
        ORDER BY c.id_course DESC
    ");
    
    $execute_params = array_merge([$_SESSION['user']['id_user']], $params);
    $stmt->execute($execute_params);
    $courses = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Ошибка базы данных: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Курсы - CodeSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="ui container" style="margin-top: 50px;">
    <?php if ($error): ?>
        <div class="ui error message">
            <div class="header">Ошибка</div>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <?php if (empty($courses)): ?>
        <div class="ui placeholder segment">
            <div class="ui icon header">
                <i class="search icon"></i>
                <?php if (!empty($search)): ?>
                    По вашему запросу ничего не найдено
                <?php else: ?>
                    Пока нет доступных курсов
                <?php endif; ?>
            </div>
            <?php if (is_admin() || is_teacher()): ?>
                <a href="add_course.php" class="ui primary button">
                    <i class="plus icon"></i>
                    Добавить курс
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php if (is_admin() || is_teacher()): ?>
            <div class="ui right aligned container" style="margin-bottom: 20px;">
                <a href="add_course.php" class="ui primary button">
                    <i class="plus icon"></i>
                    Добавить курс
                </a>
            </div>
        <?php endif; ?>
        
        <div class="ui three stackable cards">
            <?php foreach ($courses as $course): ?>
                <div class="ui card">
                    <div class="content">
                        <div class="header"><?= htmlspecialchars($course['name_course']) ?></div>
                        <div class="meta">
                            <?php if ($course['average_rating']): ?>
                                <div class="ui star rating" data-rating="<?= round($course['average_rating']) ?>" data-max-rating="5"></div>
                                (<?= number_format($course['average_rating'], 1) ?> / 5.0 - <?= $course['feedback_count'] ?> отзывов)
                            <?php else: ?>
                                Нет оценок
                            <?php endif; ?>
                        </div>
                        <div class="description">
                            <?= nl2br(htmlspecialchars(mb_substr($course['desc_course'], 0, 150) . (mb_strlen($course['desc_course']) > 150 ? '...' : ''))) ?>
                        </div>
                    </div>
                    <div class="extra content">
                        <div class="ui labels">
                            <?php foreach (explode(',', $course['tags_course']) as $tag): ?>
                                <?php if (trim($tag)): ?>
                                    <a href="?search=<?= urlencode(trim($tag)) ?>" class="ui label">
                                        <?= htmlspecialchars(trim($tag)) ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="extra content">
                        <span class="right floated">
                            <?= $course['students_count'] ?> студентов
                        </span>
                        <span>
                            <i class="book icon"></i>
                            <?= $course['lessons_count'] ?> уроков
                        </span>
                    </div>
                    <div class="extra content">
                        <a href="course.php?id=<?= $course['id_course'] ?>" class="ui fluid primary button">
                            <?php if ($course['is_enrolled']): ?>
                                Перейти к курсу
                            <?php else: ?>
                                Подробнее
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('.ui.rating').rating('disable');
});
</script>

</body>
</html>
