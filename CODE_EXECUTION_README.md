# Code Execution System for Programming Tasks

This document describes how to set up and use the code execution system for programming tasks in the test system.

## Overview

The code execution system allows teachers to create programming tasks for students in PHP, Python, and C++. Students can write code in the browser and run it to see the output. The system checks if the output matches the expected output.

## Installation

### 1. Apply Database Changes

Run the `apply_code_execution.php` script to apply the necessary database changes:

```
http://your-site.com/apply_code_execution.php
```

This will add the following columns to the `code_tasks` table:
- `template_code`: The starting code template provided to students
- `language`: The programming language (php, python, cpp)
- `execution_timeout`: Maximum execution time in seconds

### 2. Set Up Required Languages

Make sure the following languages are installed on your server:

- **PHP**: Already installed if the system is running
- **Python**: Install Python 3
  ```
  sudo apt-get install python3
  ```
- **C++**: Install G++ compiler
  ```
  sudo apt-get install g++
  ```

### 3. Set Permissions

Make sure the `code_executor.php` file has the correct permissions:

```
chmod 755 code_executor.php
```

### 4. Configure Security

The code execution system uses temporary directories and process isolation to run code safely. However, you should still consider additional security measures:

- Run the code execution in a sandboxed environment
- Set up resource limits in your PHP configuration
- Consider using Docker for isolation

## Creating Programming Tasks

1. Go to the test editing page
2. Click "Add new question"
3. Select "Question with code" as the question type
4. Fill in the question text
5. Select the programming language (PHP, Python, or C++)
6. Provide a template code for students to start with
7. Specify the expected output for correct solutions
8. Save the question

> **Note about PHP code**: When creating PHP tasks, you can either:
> - Include PHP tags (`<?php` and `?>`) in your template if you want students to see them
> - Omit PHP tags and they will be automatically added during execution
> 
> The system will detect if PHP tags are present and handle the code appropriately.

## How It Works

1. Students see the question and the code template
2. They can modify the code and run it to see the output
3. When they submit their answer, the system:
   - Executes their code in a secure environment
   - Compares the output with the expected output
   - Marks the answer as correct if the outputs match

## Security Considerations

The code execution system includes several security measures:

- Code is executed in a temporary directory that is deleted after execution
- Dangerous PHP functions are disabled
- Execution time is limited
- Process isolation is used to prevent system access

## Troubleshooting

If you encounter issues with the code execution system:

1. Check if all required languages are installed
2. Verify that the `code_executor.php` file has the correct permissions
3. Check the server logs for errors
4. Make sure the temporary directory is writable

## Sample Tasks

Sample programming tasks are provided in the `sample_code_tasks.sql` file. You can import these tasks to test the system:

```sql
-- Import sample tasks
\i sample_code_tasks.sql
```

## Limitations

- The system currently supports PHP, Python, and C++
- Execution time is limited to prevent infinite loops
- Network access is not available to executed code
- File system access is limited to the temporary directory

## Future Improvements

- Add support for more programming languages
- Implement more sophisticated code checking (unit tests, etc.)
- Add syntax highlighting in the code editor
- Provide better feedback for common errors 