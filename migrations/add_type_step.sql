-- Добавляем поле type_step в таблицу Steps
ALTER TABLE Steps ADD COLUMN type_step VARCHAR(50) NOT NULL DEFAULT 'material';

-- Обновляем существующие записи для тестов
UPDATE Steps s
SET type_step = 'test'
WHERE EXISTS (
    SELECT 1 
    FROM Tests t 
    WHERE t.id_step = s.id_step
); 