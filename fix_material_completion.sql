-- Создаем таблицу для отслеживания прогресса пользователей по материалам
CREATE TABLE IF NOT EXISTS user_material_progress (
    id_user INTEGER NOT NULL,
    id_step INTEGER NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user, id_step),
    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_step) REFERENCES steps(id_step)
);

-- Добавляем индекс для быстрого поиска
CREATE INDEX IF NOT EXISTS idx_user_material_progress ON user_material_progress(id_user, id_step);

-- Сбрасываем date_complete для всех пользователей, так как старая логика была неверной
UPDATE create_passes 
SET date_complete = NULL 
WHERE date_complete IS NOT NULL; 