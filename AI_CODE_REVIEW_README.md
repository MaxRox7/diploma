# AI Code Review System

This document describes the AI code review system implemented for programming tasks in the test system.

## Overview

The AI code review system uses YandexGPT API to analyze student code submissions and provide detailed feedback on code quality, correctness, and suggestions for improvement. The system is integrated with the existing code execution system and provides feedback to both students and teachers.

## Components

The system consists of the following components:

1. **ai_code_reviewer.php** - The main API endpoint that communicates with YandexGPT API to analyze code
2. **ai_code_review_integration.php** - Helper functions to integrate AI code review with the test system
3. **test_pass.php** - Updated to use AI code review for code tasks
4. **test_results.php** - Updated to display AI feedback for code tasks

## How It Works

1. When a student submits a code solution, the system first checks if the output matches the expected output
2. If the output matches, the system sends the code to the AI code reviewer
3. The AI analyzes the code and provides feedback on:
   - Correctness: Does the code solve the problem correctly?
   - Efficiency: How efficient is the algorithm?
   - Style: Does the code follow language conventions?
   - Potential problems: Are there any bugs or issues?
4. The AI feedback is stored in the database and displayed to the student after test completion
5. Teachers can also see the AI feedback when reviewing student submissions

## Configuration

The system uses the YandexGPT API key from the `.env` file. Make sure the API key is properly configured:

```
API_KEY=your_yandex_gpt_api_key
```

## Database

The system adds an `ai_feedback` column to the `test_answers` table to store AI feedback. This column is created automatically when needed.

## Limitations

- The AI may not always provide perfect feedback, especially for complex code
- The system is limited by the YandexGPT API rate limits and token limits
- The system currently supports PHP, Python, and C++ code analysis

## Future Improvements

- Add support for more programming languages
- Implement more sophisticated code analysis
- Add the ability for teachers to provide additional feedback alongside AI feedback
- Implement a feedback loop to improve AI code analysis over time

## Troubleshooting

If you encounter issues with the AI code review system:

1. Check if the YandexGPT API key is correctly configured in the `.env` file
2. Check the logs for errors (look for entries with "AI" in them)
3. Verify that the `ai_feedback` column exists in the `test_answers` table
4. Try increasing the timeout for AI API calls if responses are taking too long 