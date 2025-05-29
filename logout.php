<?php
require_once 'config.php';

// Очищаем все данные сессии
session_unset();
session_destroy();

// Перенаправляем на страницу входа
header('Location: login.php');
exit;
?>