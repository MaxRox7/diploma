-- Add columns to code_tasks table to support code execution
ALTER TABLE code_tasks ADD COLUMN IF NOT EXISTS template_code TEXT;
ALTER TABLE code_tasks ADD COLUMN IF NOT EXISTS language VARCHAR(20) CHECK (language IN ('php', 'python', 'cpp')) DEFAULT 'php';
ALTER TABLE code_tasks ADD COLUMN IF NOT EXISTS execution_timeout INT DEFAULT 5;

-- Add comment column to explain the expected functionality
COMMENT ON TABLE code_tasks IS 'Stores code tasks for programming questions with input template and expected output';
COMMENT ON COLUMN code_tasks.template_code IS 'Starting template code provided to the student';
COMMENT ON COLUMN code_tasks.input_ct IS 'Input data or description for the code task';
COMMENT ON COLUMN code_tasks.output_ct IS 'Expected output that the code should produce';
COMMENT ON COLUMN code_tasks.language IS 'Programming language for the task (php, python, cpp)';
COMMENT ON COLUMN code_tasks.execution_timeout IS 'Maximum execution time in seconds';

-- Create index on language for faster filtering
CREATE INDEX IF NOT EXISTS idx_code_tasks_language ON code_tasks(language); 