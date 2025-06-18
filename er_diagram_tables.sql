-- Только определения таблиц и внешних ключей для построения ER-диаграммы

CREATE TABLE public.users (
    id_user SERIAL PRIMARY KEY,
    login_user VARCHAR(255) NOT NULL,
    fn_user VARCHAR(255),
    role_user VARCHAR(50) NOT NULL
);

CREATE TABLE public.course (
    id_course SERIAL PRIMARY KEY,
    name_course VARCHAR(255) NOT NULL
);

CREATE TABLE public.lessons (
    id_lesson SERIAL PRIMARY KEY,
    id_course INTEGER NOT NULL REFERENCES course(id_course),
    name_lesson VARCHAR(255) NOT NULL
);

CREATE TABLE public.steps (
    id_step SERIAL PRIMARY KEY,
    id_lesson INTEGER NOT NULL REFERENCES lessons(id_lesson),
    number_steps VARCHAR(255),
    type_step VARCHAR(50)
);

CREATE TABLE public.material (
    id_material SERIAL PRIMARY KEY,
    id_step INTEGER NOT NULL REFERENCES steps(id_step),
    path_matial VARCHAR(255),
    link_material VARCHAR(255)
);

CREATE TABLE public.tests (
    id_test SERIAL PRIMARY KEY,
    id_step INTEGER NOT NULL REFERENCES steps(id_step),
    passing_percentage INTEGER,
    max_attempts INTEGER
);

CREATE TABLE public.questions (
    id_question SERIAL PRIMARY KEY,
    id_test INTEGER NOT NULL REFERENCES tests(id_test),
    text_question TEXT,
    type_question VARCHAR(50)
);

CREATE TABLE public.answer_options (
    id_option SERIAL PRIMARY KEY,
    id_question INTEGER NOT NULL REFERENCES questions(id_question),
    text_option TEXT,
    is_correct BOOLEAN
);

CREATE TABLE public.create_passes (
    id_course INTEGER NOT NULL REFERENCES course(id_course),
    id_user INTEGER NOT NULL REFERENCES users(id_user),
    is_creator BOOLEAN,
    date_complete TIMESTAMP,
    PRIMARY KEY (id_course, id_user)
);

CREATE TABLE public.test_attempts (
    id_attempt SERIAL PRIMARY KEY,
    id_test INTEGER NOT NULL REFERENCES tests(id_test),
    id_user INTEGER NOT NULL REFERENCES users(id_user),
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    score INTEGER,
    max_score INTEGER,
    status VARCHAR(50)
);

CREATE TABLE public.test_answers (
    id_answer SERIAL PRIMARY KEY,
    id_attempt INTEGER NOT NULL REFERENCES test_attempts(id_attempt),
    id_question INTEGER NOT NULL REFERENCES questions(id_question),
    id_selected_option INTEGER,
    answer_text TEXT,
    is_correct BOOLEAN
);

CREATE TABLE public.tags (
    id_tag SERIAL PRIMARY KEY,
    name_tag VARCHAR(255) NOT NULL
);

CREATE TABLE public.course_tags (
    id_course INTEGER NOT NULL REFERENCES course(id_course),
    id_tag INTEGER NOT NULL REFERENCES tags(id_tag),
    PRIMARY KEY (id_course, id_tag)
);

CREATE TABLE public.user_tag_interests (
    id_user INTEGER NOT NULL REFERENCES users(id_user),
    id_tag INTEGER NOT NULL REFERENCES tags(id_tag),
    interest_weight FLOAT,
    PRIMARY KEY (id_user, id_tag)
);

CREATE TABLE public.user_material_progress (
    id_user INTEGER NOT NULL REFERENCES users(id_user),
    id_step INTEGER NOT NULL REFERENCES steps(id_step),
    completed_at TIMESTAMP,
    PRIMARY KEY (id_user, id_step)
);

CREATE TABLE public.student_analytics (
    id_analytics SERIAL PRIMARY KEY,
    id_user INTEGER NOT NULL REFERENCES users(id_user),
    id_course INTEGER REFERENCES course(id_course),
    activity_date TIMESTAMP,
    activity_type VARCHAR(50)
); 