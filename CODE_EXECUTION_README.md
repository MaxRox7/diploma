# Code Execution System for Programming Tasks

This document describes how to set up and use the code execution system for programming tasks in the test system.

## Overview

The code execution system allows teachers to create programming tasks for students in PHP, Python, and C++. Students can write code in the browser and run it to see the output. The system checks if the output matches the expected output.

## How It Works

The system uses the Piston API (https://github.com/engineer-man/piston) to execute code in a secure environment. Piston is a high-performance general purpose code execution engine that excels at running untrusted and potentially malicious code without fear of harmful effects.

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

### 2. API Configuration

The system uses the public Piston API instance at `https://emkc.org/api/v2/piston/execute`. This requires internet access from your server.

If you prefer to host your own Piston instance, you can modify the `$api_url` variable in the `code_executor.php` file.

### 3. Supported Languages

The following languages are supported:
- PHP (version 8.2.3)
- Python (version 3.10.0)
- C++ (version 10.2.0)

To add support for more languages, modify the `$language_map` array in the `code_executor.php` file.

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

## Security Considerations

The Piston API includes several security measures:

- Code is executed in isolated environments
- Execution time is limited
- Memory usage is limited
- Network access is disabled
- File system access is restricted

## Troubleshooting

If you encounter issues with the code execution system:

1. Check if your server has internet access to reach the Piston API
2. Verify that the API response is properly formatted
3. Check the server logs for errors
4. Try executing code with shorter timeouts if requests are timing out

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
- The public Piston API has a rate limit of 5 requests per second

## Future Improvements

- Add support for more programming languages
- Implement more sophisticated code checking (unit tests, etc.)
- Add syntax highlighting in the code editor
- Provide better feedback for common errors 