-- Sample PHP code task
INSERT INTO Questions (id_test, text_question, answer_question, type_question)
VALUES (
    1, -- Replace with an actual test ID
    'Напишите функцию sum_array, которая принимает массив чисел и возвращает их сумму.',
    '', -- No answer needed for code questions
    'code'
);

INSERT INTO code_tasks (id_question, template_code, input_ct, output_ct, language, execution_timeout)
VALUES (
    LASTVAL(), -- Get the ID of the last inserted question
    '/**
 * Функция для суммирования элементов массива
 * 
 * @param array $arr Массив чисел
 * @return int Сумма элементов массива
 */
function sum_array($arr) {
    // Ваш код здесь
}

// Тестирование функции
$test_array = [1, 2, 3, 4, 5];
echo "Сумма массива: " . sum_array($test_array);',
    '',
    'Сумма массива: 15',
    'php',
    5
);

-- Sample Python code task
INSERT INTO Questions (id_test, text_question, answer_question, type_question)
VALUES (
    1, -- Replace with an actual test ID
    'Напишите функцию is_prime, которая проверяет, является ли число простым.',
    '', 
    'code'
);

INSERT INTO code_tasks (id_question, template_code, input_ct, output_ct, language, execution_timeout)
VALUES (
    LASTVAL(),
    '# Функция для проверки, является ли число простым
def is_prime(n):
    # Ваш код здесь
    pass

# Тестирование функции
test_numbers = [2, 3, 4, 5, 6, 7, 11]
for num in test_numbers:
    print(f"{num} is prime: {is_prime(num)}")',
    '',
    '2 is prime: True
3 is prime: True
4 is prime: False
5 is prime: True
6 is prime: False
7 is prime: True
11 is prime: True',
    'python',
    5
);

-- Sample C++ code task
INSERT INTO Questions (id_test, text_question, answer_question, type_question)
VALUES (
    1, -- Replace with an actual test ID
    'Напишите функцию factorial, которая вычисляет факториал числа.',
    '',
    'code'
);

INSERT INTO code_tasks (id_question, template_code, input_ct, output_ct, language, execution_timeout)
VALUES (
    LASTVAL(),
    '#include <iostream>
using namespace std;

// Функция для вычисления факториала числа
int factorial(int n) {
    // Ваш код здесь
}

int main() {
    // Тестирование функции
    for(int i = 0; i <= 5; i++) {
        cout << i << "! = " << factorial(i) << endl;
    }
    return 0;
}',
    '',
    '0! = 1
1! = 1
2! = 2
3! = 6
4! = 24
5! = 120',
    'cpp',
    5
); 