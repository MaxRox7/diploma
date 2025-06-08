-- Добавление полей для настройки прохождения тестов
ALTER TABLE Tests ADD COLUMN passing_percentage INTEGER DEFAULT 70 CHECK (passing_percentage BETWEEN 0 AND 100);
ALTER TABLE Tests ADD COLUMN max_attempts INTEGER DEFAULT 3 CHECK (max_attempts > 0);
ALTER TABLE Tests ADD COLUMN time_between_attempts INTEGER DEFAULT 0; -- в минутах
ALTER TABLE Tests ADD COLUMN show_results_after_completion BOOLEAN DEFAULT TRUE;
ALTER TABLE Tests ADD COLUMN practice_mode BOOLEAN DEFAULT FALSE;

-- Добавление таблицы для хранения уровней оценок
CREATE TABLE IF NOT EXISTS test_grade_levels (
    id_level SERIAL PRIMARY KEY,
    id_test INTEGER REFERENCES Tests(id_test) ON DELETE CASCADE,
    min_percentage INTEGER NOT NULL CHECK (min_percentage BETWEEN 0 AND 100),
    max_percentage INTEGER NOT NULL CHECK (max_percentage BETWEEN 0 AND 100),
    grade_name VARCHAR(50) NOT NULL,
    grade_color VARCHAR(20) DEFAULT '#000000',
    CHECK (min_percentage < max_percentage)
);

-- Добавление таблицы для хранения индивидуальных настроек попыток для студентов
CREATE TABLE IF NOT EXISTS student_test_settings (
    id_user INTEGER REFERENCES users(id_user) ON DELETE CASCADE,
    id_test INTEGER REFERENCES Tests(id_test) ON DELETE CASCADE,
    additional_attempts INTEGER DEFAULT 0,
    PRIMARY KEY (id_user, id_test)
);

-- Добавление индексов для оптимизации
CREATE INDEX idx_test_attempts_complete_time ON test_attempts(id_test, id_user, end_time);
CREATE INDEX idx_test_grade_levels ON test_grade_levels(id_test); 