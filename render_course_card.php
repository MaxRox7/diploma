<?php
// Вспомогательная функция для рендера карточки курса в едином стиле
function render_course_card($course, $show_stats = false, $show_enrolled = false) {
    ?>
    <div class="ui card">
        <div class="content">
            <div class="header"><?= htmlspecialchars($course['name_course']) ?></div>
            <div class="meta">
                <?php if (isset($course['average_rating']) && $course['average_rating']): ?>
                    <div class="ui star rating" data-rating="<?= round($course['average_rating']) ?>" data-max-rating="5"></div>
                    (<?= number_format($course['average_rating'], 1) ?> / 5.0 - <?= $course['feedback_count'] ?? 0 ?> отзывов)
                <?php else: ?>
                    Нет оценок
                <?php endif; ?>
            </div>
            <div class="description">
                <?= nl2br(htmlspecialchars(mb_substr($course['desc_course'], 0, 150) . (mb_strlen($course['desc_course']) > 150 ? '...' : ''))) ?>
            </div>
        </div>
        <?php if ($show_stats && (isset($course['views_count']) || isset($course['enrollment_count']))): ?>
        <div class="extra content">
            <div class="ui mini statistics">
                <?php if (isset($course['views_count'])): ?>
                <div class="statistic">
                    <div class="value">
                        <i class="eye icon"></i> <?= $course['views_count'] ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (isset($course['students_count'])): ?>
                <div class="statistic">
                    <div class="value">
                        <i class="users icon"></i> <?= $course['students_count'] ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="extra content">
            <div class="ui labels">
                <?php 
                $tags = array_map('trim', explode(',', $course['tags_course'] ?? ''));
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
            <span>
                <i class="book icon"></i>
                <?= $course['lessons_count'] ?? 0 ?> уроков
            </span>
        </div>
        <div class="extra content">
            <a href="course.php?id=<?= $course['id_course'] ?>" class="ui fluid primary button">
                <?php if ($show_enrolled && !empty($course['is_enrolled'])): ?>
                    Перейти к курсу
                <?php else: ?>
                    Подробнее
                <?php endif; ?>
            </a>
        </div>
    </div>
    <?php
} 