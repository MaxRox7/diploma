<?php
require_once 'config.php';
redirect_unauthenticated();

$error = '';
$user_id = $_SESSION['user']['id_user'];

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
    
    $user = $_SESSION['user'] ?? null;
    $where = [];
    if (!is_admin()) {
        $where[] = "status_course='approved'";
    }
    $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    
    // Получаем рекомендуемые курсы на основе интересов пользователя
    $recommended_courses = [];
    $popular_courses = [];
    
    // Обновляем интересы пользователя при каждом посещении страницы
    try {
        update_user_interests($user_id);
    } catch (Exception $e) {
        // Игнорируем ошибку, если таблицы для интересов не существуют
        error_log('Ошибка при обновлении интересов пользователя: ' . $e->getMessage());
    }
    
    // Получаем теги, которые интересуют пользователя
    try {
        $stmt = $pdo->prepare("
            SELECT t.* 
            FROM tags t
            JOIN user_tag_interests uti ON t.id_tag = uti.id_tag
            WHERE uti.id_user = ?
            ORDER BY uti.interest_weight DESC
        ");
        $stmt->execute([$user_id]);
        $user_tags = $stmt->fetchAll();
    } catch (Exception $e) {
        // Если таблицы не существуют, продолжаем с пустым массивом тегов
        $user_tags = [];
    }
    
    // Если у пользователя есть интересы, получаем рекомендации
    if (!empty($user_tags)) {
        // Формируем список ID тегов
        $tag_ids = array_column($user_tags, 'id_tag');
        
        // Запрос для получения рекомендаций на основе интересов
        $sql = "
            SELECT c.*, 
                  COUNT(DISTINCT ct.id_tag) as matching_tags_count,
                  SUM(COALESCE(uti.interest_weight, 1)) as total_weight,
                  COALESCE(AVG(CAST(f.rate_feedback AS FLOAT)), 0) as average_rating,
                  COUNT(DISTINCT f.id_feedback) as feedback_count,
                  COUNT(DISTINCT cp2.id_user) as students_count,
                  COUNT(DISTINCT l.id_lesson) as lessons_count,
                  false as is_enrolled
            FROM course c
            JOIN course_tags ct ON c.id_course = ct.id_course
            LEFT JOIN user_tag_interests uti ON ct.id_tag = uti.id_tag AND uti.id_user = ?
            LEFT JOIN feedback f ON c.id_course = f.id_course
            LEFT JOIN create_passes cp2 ON c.id_course = cp2.id_course
            LEFT JOIN lessons l ON c.id_course = l.id_lesson
            WHERE ct.id_tag IN (" . implode(',', array_fill(0, count($tag_ids), '?')) . ")
            AND c.id_course NOT IN (
                SELECT cp.id_course FROM create_passes cp WHERE cp.id_user = ?
            )
            AND c.status_course = 'approved'
            GROUP BY c.id_course
            ORDER BY total_weight DESC, matching_tags_count DESC
            LIMIT 6
        ";
        
        // Подготавливаем параметры для запроса
        $params_rec = array_merge([$user_id], $tag_ids, [$user_id]);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params_rec);
        $recommended_courses = $stmt->fetchAll();
    }
    
    // Получаем популярные курсы
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COALESCE(cs.views_count, 0) as views_count, 
               COALESCE(cs.enrollment_count, 0) as enrollment_count,
               COALESCE(AVG(CAST(f.rate_feedback AS FLOAT)), 0) as average_rating,
               COUNT(DISTINCT f.id_feedback) as feedback_count,
               COUNT(DISTINCT cp2.id_user) as students_count,
               COUNT(DISTINCT l.id_lesson) as lessons_count,
               false as is_enrolled
        FROM course c
        LEFT JOIN course_statistics cs ON c.id_course = cs.id_course
        LEFT JOIN feedback f ON c.id_course = f.id_course
        LEFT JOIN create_passes cp2 ON c.id_course = cp2.id_course
        LEFT JOIN lessons l ON c.id_course = l.id_lesson
        WHERE c.status_course = 'approved'
        AND c.id_course NOT IN (
            SELECT cp.id_course FROM create_passes cp WHERE cp.id_user = ?
        )
        GROUP BY c.id_course, cs.views_count, cs.enrollment_count
        ORDER BY COALESCE(cs.views_count, 0) DESC, COALESCE(cs.enrollment_count, 0) DESC
        LIMIT 6
    ");
    $stmt->execute([$user_id]);
    $popular_courses = $stmt->fetchAll();
    
    // Получаем высокооцененные курсы
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COALESCE(AVG(CAST(f.rate_feedback AS FLOAT)), 0) as average_rating,
               COUNT(DISTINCT f.id_feedback) as feedback_count,
               COUNT(DISTINCT cp2.id_user) as students_count,
               COUNT(DISTINCT l.id_lesson) as lessons_count,
               false as is_enrolled
        FROM course c
        LEFT JOIN feedback f ON c.id_course = f.id_course
        LEFT JOIN create_passes cp2 ON c.id_course = cp2.id_course
        LEFT JOIN lessons l ON c.id_course = l.id_lesson
        WHERE c.status_course = 'approved'
        AND c.id_course NOT IN (
            SELECT cp.id_course FROM create_passes cp WHERE cp.id_user = ?
        )
        GROUP BY c.id_course
        HAVING COUNT(DISTINCT f.id_feedback) > 0
        ORDER BY COALESCE(AVG(CAST(f.rate_feedback AS FLOAT)), 0) DESC
        LIMIT 6
    ");
    $stmt->execute([$user_id]);
    $top_rated_courses = $stmt->fetchAll();
    
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
        LEFT JOIN lessons l ON c.id_course = l.id_lesson
        LEFT JOIN feedback f ON c.id_course = f.id_course
        $where_sql $search_condition
        GROUP BY c.id_course
        ORDER BY c.id_course DESC
    ");
    
    $execute_params = array_merge([$_SESSION['user']['id_user']], $params);
    $stmt->execute($execute_params);
    $courses = $stmt->fetchAll();
    
    $filtered_courses = [];
    foreach ($courses as $course) {
        $ok = true;
        if (!empty($course['required_uni']) && $course['required_uni'] !== $user['uni_user']) $ok = false;
        if (!empty($course['required_spec']) && $course['required_spec'] !== $user['spec_user']) $ok = false;
        if (!empty($course['requred_year']) && (string)$course['requred_year'] !== (string)$user['year_user']) $ok = false;
        if ($ok) $filtered_courses[] = $course;
    }
    $courses = $filtered_courses;
    
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

    <!-- Секция рекомендуемых курсов -->
    <?php if (!empty($recommended_courses)): ?>
        <div class="ui segment">
            <h2 class="ui header">
                <i class="lightbulb outline icon"></i>
                <div class="content">
                    Рекомендуемые курсы
                    <div class="sub header">На основе ваших интересов</div>
                </div>
            </h2>
            
            <div class="ui three stackable cards">
                <?php foreach ($recommended_courses as $course): ?>
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
                                <?php 
                                $tags = array_map('trim', explode(',', $course['tags_course']));
                                $tags = array_slice($tags, 0, 3); // Ограничиваем количество тегов
                                foreach ($tags as $tag): 
                                ?>
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
                                Подробнее
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Секция популярных курсов -->
    <?php if (!empty($popular_courses)): ?>
        <div class="ui segment">
            <h2 class="ui header">
                <i class="fire icon"></i>
                <div class="content">
                    Популярные курсы
                    <div class="sub header">Курсы, которые выбирают многие</div>
                </div>
            </h2>
            
            <div class="ui three stackable cards">
                <?php foreach ($popular_courses as $course): ?>
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
                            <div class="ui mini statistics">
                                <div class="statistic">
                                    <div class="value">
                                        <i class="eye icon"></i> <?= $course['views_count'] ?>
                                    </div>
                                </div>
                                <div class="statistic">
                                    <div class="value">
                                        <i class="users icon"></i> <?= $course['enrollment_count'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="extra content">
                            <a href="course.php?id=<?= $course['id_course'] ?>" class="ui fluid primary button">
                                Подробнее
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Секция высокооцененных курсов -->
    <?php if (!empty($top_rated_courses)): ?>
        <div class="ui segment">
            <h2 class="ui header">
                <i class="star icon"></i>
                <div class="content">
                    Высокооцененные курсы
                    <div class="sub header">Курсы с лучшими отзывами</div>
                </div>
            </h2>
            
            <div class="ui three stackable cards">
                <?php foreach ($top_rated_courses as $course): ?>
                    <div class="ui card">
                        <div class="content">
                            <div class="header"><?= htmlspecialchars($course['name_course']) ?></div>
                            <div class="meta">
                                <div class="ui star rating" data-rating="<?= round($course['average_rating']) ?>" data-max-rating="5"></div>
                                <strong><?= number_format($course['average_rating'], 1) ?> / 5.0</strong> (<?= $course['feedback_count'] ?> отзывов)
                            </div>
                            <div class="description">
                                <?= nl2br(htmlspecialchars(mb_substr($course['desc_course'], 0, 150) . (mb_strlen($course['desc_course']) > 150 ? '...' : ''))) ?>
                            </div>
                        </div>
                        <div class="extra content">
                            <div class="ui labels">
                                <?php 
                                $tags = array_map('trim', explode(',', $course['tags_course']));
                                $tags = array_slice($tags, 0, 3); // Ограничиваем количество тегов
                                foreach ($tags as $tag): 
                                ?>
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
                                Подробнее
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
        
        <div class="ui segment">
            <h2 class="ui header">
                <i class="list icon"></i>
                <div class="content">
                    Все курсы
                    <?php if (!empty($search)): ?>
                        <div class="sub header">Результаты поиска по запросу: "<?= htmlspecialchars($search) ?>"</div>
                    <?php endif; ?>
                </div>
            </h2>
            
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
