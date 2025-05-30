-- Добавляем каскадное удаление для всех зависимых таблиц

-- Связи с таблицей course
ALTER TABLE lessons
    DROP CONSTRAINT IF EXISTS lessons_course_fk,
    ADD CONSTRAINT lessons_course_fk
    FOREIGN KEY (id_course)
    REFERENCES course(id_course)
    ON DELETE CASCADE;

ALTER TABLE create_passes
    DROP CONSTRAINT IF EXISTS create_passes_course_fk,
    ADD CONSTRAINT create_passes_course_fk
    FOREIGN KEY (id_course)
    REFERENCES course(id_course)
    ON DELETE CASCADE;

ALTER TABLE feedback
    DROP CONSTRAINT IF EXISTS feedback_course_fk,
    ADD CONSTRAINT feedback_course_fk
    FOREIGN KEY (id_course)
    REFERENCES course(id_course)
    ON DELETE CASCADE;

-- Связи с таблицей lessons
ALTER TABLE Steps
    DROP CONSTRAINT IF EXISTS steps_lesson_fk,
    ADD CONSTRAINT steps_lesson_fk
    FOREIGN KEY (id_lesson)
    REFERENCES lessons(id_lesson)
    ON DELETE CASCADE;

-- Связи с таблицей Steps
ALTER TABLE Tests
    DROP CONSTRAINT IF EXISTS tests_step_fk,
    ADD CONSTRAINT tests_step_fk
    FOREIGN KEY (id_step)
    REFERENCES Steps(id_step)
    ON DELETE CASCADE;

ALTER TABLE Material
    DROP CONSTRAINT IF EXISTS material_step_fk,
    ADD CONSTRAINT material_step_fk
    FOREIGN KEY (id_step)
    REFERENCES Steps(id_step)
    ON DELETE CASCADE;

-- Связи с таблицей Tests
ALTER TABLE Questions
    DROP CONSTRAINT IF EXISTS questions_test_fk,
    ADD CONSTRAINT questions_test_fk
    FOREIGN KEY (id_test)
    REFERENCES Tests(id_test)
    ON DELETE CASCADE;

-- Связи с таблицей Questions
ALTER TABLE Answer_options
    DROP CONSTRAINT IF EXISTS answer_options_question_fk,
    ADD CONSTRAINT answer_options_question_fk
    FOREIGN KEY (id_question)
    REFERENCES Questions(id_question)
    ON DELETE CASCADE;

ALTER TABLE Answers
    DROP CONSTRAINT IF EXISTS answers_question_fk,
    ADD CONSTRAINT answers_question_fk
    FOREIGN KEY (id_question)
    REFERENCES Questions(id_question)
    ON DELETE CASCADE;

ALTER TABLE code_tasks
    DROP CONSTRAINT IF EXISTS code_tasks_question_fk,
    ADD CONSTRAINT code_tasks_question_fk
    FOREIGN KEY (id_question)
    REFERENCES Questions(id_question)
    ON DELETE CASCADE;

-- Связи с таблицей Answers
ALTER TABLE Results
    DROP CONSTRAINT IF EXISTS results_answer_fk,
    ADD CONSTRAINT results_answer_fk
    FOREIGN KEY (id_answer)
    REFERENCES Answers(id_answer)
    ON DELETE CASCADE; 