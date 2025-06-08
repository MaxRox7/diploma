-- Создание таблицы для отслеживания активности студентов
CREATE TABLE IF NOT EXISTS student_analytics (
    id_activity SERIAL PRIMARY KEY,
    id_user INTEGER NOT NULL REFERENCES users(id_user) ON DELETE CASCADE,
    activity_type VARCHAR(50) NOT NULL, -- 'material_view', 'test_attempt', 'course_enrollment', etc.
    id_course INTEGER REFERENCES course(id_course) ON DELETE CASCADE,
    id_lesson INTEGER REFERENCES lessons(id_lesson) ON DELETE CASCADE,
    id_step INTEGER REFERENCES Steps(id_step) ON DELETE CASCADE,
    id_test INTEGER REFERENCES Tests(id_test) ON DELETE CASCADE,
    activity_date DATE NOT NULL DEFAULT CURRENT_DATE,
    activity_time TIME NOT NULL DEFAULT CURRENT_TIME,
    session_duration INTEGER, -- в секундах
    details JSONB -- дополнительные детали активности
);

-- Индексы для более быстрого поиска
CREATE INDEX IF NOT EXISTS idx_student_analytics_user ON student_analytics(id_user);
CREATE INDEX IF NOT EXISTS idx_student_analytics_course ON student_analytics(id_course);
CREATE INDEX IF NOT EXISTS idx_student_analytics_date ON student_analytics(activity_date);
CREATE INDEX IF NOT EXISTS idx_student_analytics_type ON student_analytics(activity_type);

-- Функция для добавления записи активности
CREATE OR REPLACE FUNCTION log_student_activity(
    p_id_user INTEGER,
    p_activity_type VARCHAR(50),
    p_id_course INTEGER DEFAULT NULL,
    p_id_lesson INTEGER DEFAULT NULL,
    p_id_step INTEGER DEFAULT NULL,
    p_id_test INTEGER DEFAULT NULL,
    p_session_duration INTEGER DEFAULT NULL,
    p_details JSONB DEFAULT NULL
) RETURNS VOID AS $$
BEGIN
    INSERT INTO student_analytics (
        id_user, 
        activity_type, 
        id_course, 
        id_lesson, 
        id_step, 
        id_test, 
        activity_date, 
        activity_time, 
        session_duration, 
        details
    ) VALUES (
        p_id_user,
        p_activity_type,
        p_id_course,
        p_id_lesson,
        p_id_step,
        p_id_test,
        CURRENT_DATE,
        CURRENT_TIME,
        p_session_duration,
        p_details
    );
END;
$$ LANGUAGE plpgsql; 