<?php
session_start();

define('USERS_JSON', 'data/users.json');
define('COURSES_JSON', 'data/courses.json');

// Функции для работы с пользователями
function get_users() {
    return file_exists(USERS_JSON) ? 
        json_decode(file_get_contents(USERS_JSON), true) : [];
}

function save_users($users) {
    file_put_contents(USERS_JSON, json_encode($users, JSON_PRETTY_PRINT));
}

// Функции для работы с курсами
function get_courses() {
    return file_exists(COURSES_JSON) ? 
        json_decode(file_get_contents(COURSES_JSON), true) : [];
}

function save_courses($courses) {
    file_put_contents(COURSES_JSON, json_encode($courses, JSON_PRETTY_PRINT));
}

// Проверки авторизации
function is_authenticated() {
    return isset($_SESSION['user']);
}

function redirect_unauthenticated() {
    if (!is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

function is_admin() {
    return is_authenticated() && $_SESSION['user']['role'] === 'admin';
}
?>