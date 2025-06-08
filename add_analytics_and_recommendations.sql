-- Таблицы для персонализированных рекомендаций по тегам
CREATE TABLE IF NOT EXISTS public.tags (
    id_tag SERIAL PRIMARY KEY,
    name_tag VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS public.course_tags (
    id_course INTEGER REFERENCES public.course(id_course) ON DELETE CASCADE,
    id_tag INTEGER REFERENCES public.tags(id_tag) ON DELETE CASCADE,
    PRIMARY KEY (id_course, id_tag)
);

CREATE TABLE IF NOT EXISTS public.user_tag_interests (
    id_user INTEGER REFERENCES public.users(id_user) ON DELETE CASCADE,
    id_tag INTEGER REFERENCES public.tags(id_tag) ON DELETE CASCADE,
    interest_weight FLOAT DEFAULT 1.0, -- вес интереса к тегу
    PRIMARY KEY (id_user, id_tag)
);

-- Таблицы для отслеживания популярности курсов
CREATE TABLE IF NOT EXISTS public.course_statistics (
    id_course INTEGER PRIMARY KEY REFERENCES public.course(id_course) ON DELETE CASCADE,
    views_count INTEGER DEFAULT 0,
    enrollment_count INTEGER DEFAULT 0,
    completion_count INTEGER DEFAULT 0,
    average_rating FLOAT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.course_views (
    id_view SERIAL PRIMARY KEY,
    id_course INTEGER REFERENCES public.course(id_course) ON DELETE CASCADE,
    id_user INTEGER REFERENCES public.users(id_user) ON DELETE CASCADE,
    view_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица для базовой аналитики обучения студента
CREATE TABLE IF NOT EXISTS public.student_analytics (
    id_user INTEGER REFERENCES public.users(id_user) ON DELETE CASCADE,
    id_course INTEGER REFERENCES public.course(id_course) ON DELETE CASCADE,
    lessons_completed INTEGER DEFAULT 0,
    total_lessons INTEGER DEFAULT 0,
    tests_completed INTEGER DEFAULT 0,
    total_tests INTEGER DEFAULT 0,
    average_test_score FLOAT DEFAULT 0,
    last_activity TIMESTAMP,
    estimated_completion_date DATE,
    PRIMARY KEY (id_user, id_course)
);

-- Заполняем таблицу course_statistics для существующих курсов
INSERT INTO public.course_statistics (id_course, views_count, enrollment_count, completion_count)
SELECT 
    c.id_course, 
    0 as views_count,
    COUNT(cp.id_user) as enrollment_count,
    COUNT(CASE WHEN c.status_course = 'completed' THEN 1 END) as completion_count
FROM 
    public.course c
LEFT JOIN 
    public.create_passes cp ON c.id_course = cp.id_course
GROUP BY 
    c.id_course
ON CONFLICT (id_course) DO NOTHING;

-- Заполняем student_analytics для существующих записей
INSERT INTO public.student_analytics (id_user, id_course, lessons_completed, total_lessons, tests_completed, total_tests, last_activity)
SELECT 
    cp.id_user,
    cp.id_course,
    COALESCE((
        SELECT COUNT(DISTINCT ump.id_step)
        FROM user_material_progress ump
        JOIN steps s ON ump.id_step = s.id_step
        JOIN lessons l ON s.id_lesson = l.id_lesson
        WHERE ump.id_user = cp.id_user AND l.id_course = cp.id_course AND s.type_step = 'material'
    ), 0) as lessons_completed,
    COALESCE((
        SELECT COUNT(*)
        FROM steps s
        JOIN lessons l ON s.id_lesson = l.id_lesson
        WHERE l.id_course = cp.id_course AND s.type_step = 'material'
    ), 0) as total_lessons,
    COALESCE((
        SELECT COUNT(DISTINCT ta.id_test)
        FROM test_attempts ta
        JOIN tests t ON ta.id_test = t.id_test
        JOIN steps s ON t.id_step = s.id_step
        JOIN lessons l ON s.id_lesson = l.id_lesson
        WHERE ta.id_user = cp.id_user AND l.id_course = cp.id_course AND ta.status = 'completed'
    ), 0) as tests_completed,
    COALESCE((
        SELECT COUNT(*)
        FROM steps s
        JOIN lessons l ON s.id_lesson = l.id_lesson
        WHERE l.id_course = cp.id_course AND s.type_step = 'test'
    ), 0) as total_tests,
    NOW() as last_activity
FROM 
    create_passes cp
ON CONFLICT (id_user, id_course) DO NOTHING;

-- Добавляем несколько основных тегов для демонстрации
INSERT INTO public.tags (name_tag) VALUES 
('Программирование'), 
('Web-разработка'), 
('Базы данных'), 
('Python'), 
('PHP'), 
('JavaScript'), 
('Дизайн'), 
('Алгоритмы'), 
('Математика'), 
('Тестирование')
ON CONFLICT (name_tag) DO NOTHING; 