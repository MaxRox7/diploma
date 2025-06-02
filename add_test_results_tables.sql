-- Add tables for test results tracking
CREATE TABLE IF NOT EXISTS test_attempts (
    id_attempt SERIAL PRIMARY KEY,
    id_test INTEGER REFERENCES Tests(id_test),
    id_user INTEGER REFERENCES users(id_user),
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP,
    score INTEGER,
    max_score INTEGER,
    status VARCHAR(20) CHECK (status IN ('in_progress', 'completed', 'abandoned')),
    UNIQUE (id_test, id_user, start_time)
);

CREATE TABLE IF NOT EXISTS test_answers (
    id_answer SERIAL PRIMARY KEY,
    id_attempt INTEGER REFERENCES test_attempts(id_attempt),
    id_question INTEGER REFERENCES Questions(id_question),
    id_selected_option INTEGER REFERENCES Answer_options(id_option),
    is_correct BOOLEAN,
    answer_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    answer_text text
);

-- Add indexes for better performance
CREATE INDEX idx_test_attempts_user ON test_attempts(id_user);
CREATE INDEX idx_test_attempts_test ON test_attempts(id_test);
CREATE INDEX idx_test_answers_attempt ON test_answers(id_attempt); 