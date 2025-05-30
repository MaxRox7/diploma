-- Сначала обновим все записи, где date_complete установлен автоматически при записи на курс
UPDATE create_passes 
SET date_complete = NULL 
WHERE date_complete IS NOT NULL 
AND id_user IN (
    SELECT id_user 
    FROM users 
    WHERE role_user = 'student'
)
AND NOT EXISTS (
    -- Проверяем, действительно ли студент завершил все шаги
    SELECT 1 
    FROM lessons l
    JOIN Steps s ON l.id_lesson = s.id_lesson
    LEFT JOIN (
        SELECT s2.id_step 
        FROM Steps s2
        WHERE s2.status_step = 'completed'
        OR EXISTS (
            SELECT 1 
            FROM Tests t 
            JOIN Results r ON t.id_test = r.id_test
            JOIN Answers a ON r.id_answer = a.id_answer
            WHERE t.id_step = s2.id_step
            AND a.id_user = create_passes.id_user
            AND CAST(r.score_result AS INTEGER) >= 60
        )
    ) completed_steps ON s.id_step = completed_steps.id_step
    WHERE l.id_course = create_passes.id_course
    GROUP BY l.id_course
    HAVING COUNT(DISTINCT s.id_step) > COUNT(DISTINCT completed_steps.id_step)
);

-- Добавим ограничение, чтобы date_complete не мог быть установлен по умолчанию
ALTER TABLE create_passes 
ALTER COLUMN date_complete DROP DEFAULT; 