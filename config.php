<?php
session_start();

// Database configuration
define('DB_HOST', 'postgres');
define('DB_NAME', 'diploma');
define('DB_USER', 'pguser');
define('DB_PASS', 'pguser');

// JWT configuration
define('JWT_SECRET', 'your-secret-key'); // В реальном приложении использовать безопасный ключ
define('JWT_EXPIRATION', 60 * 60 * 24); // 24 часа

// Константы ролей пользователей
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');

// Database connection
function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}

// JWT functions
function generate_jwt($payload, $secret = JWT_SECRET) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $header = base64_encode($header);
    
    $payload = json_encode($payload);
    $payload = base64_encode($payload);
    
    $signature = hash_hmac('sha256', "$header.$payload", $secret, true);
    $signature = base64_encode($signature);
    
    return "$header.$payload.$signature";
}

function verify_jwt($token, $secret = JWT_SECRET) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    list($header, $payload, $signature) = $parts;

    $valid_signature = base64_encode(
        hash_hmac('sha256', "$header.$payload", $secret, true)
    );

    if ($signature !== $valid_signature) {
        return false;
    }

    $payload = json_decode(base64_decode($payload), true);
    
    if (!isset($payload['exp']) || $payload['exp'] < time()) {
        return false;
    }

    return $payload;
}

// Authentication functions
function is_authenticated() {
    if (!isset($_SESSION['jwt'])) {
        return false;
    }

    $payload = verify_jwt($_SESSION['jwt']);
    if (!$payload) {
        unset($_SESSION['jwt']);
        unset($_SESSION['user']);
        return false;
    }

    return true;
}

function get_authenticated_user() {
    if (!is_authenticated()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

function redirect_unauthenticated() {
    if (!is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

// Функция проверки роли администратора
function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role_user'] === ROLE_ADMIN;
}

// Функция проверки роли преподавателя
function is_teacher() {
    return isset($_SESSION['user']) && $_SESSION['user']['role_user'] === ROLE_TEACHER;
}

// Функция проверки роли студента
function is_student() {
    return isset($_SESSION['user']) && $_SESSION['user']['role_user'] === ROLE_STUDENT;
}

// Функция проверки, является ли пользователь создателем курса
function is_course_creator($pdo, $course_id, $user_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM create_passes 
        WHERE id_course = ? AND id_user = ? 
        AND id_user IN (
            SELECT id_user 
            FROM users 
            WHERE role_user IN (?, ?)
        )
    ");
    $stmt->execute([$course_id, $user_id, ROLE_ADMIN, ROLE_TEACHER]);
    return $stmt->fetchColumn() > 0;
}

// Функция проверки, записан ли студент на курс
function is_enrolled_student($pdo, $course_id, $user_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM create_passes 
        WHERE id_course = ? AND id_user = ? 
        AND id_user IN (
            SELECT id_user 
            FROM users 
            WHERE role_user = ?
        )
    ");
    $stmt->execute([$course_id, $user_id, ROLE_STUDENT]);
    return $stmt->fetchColumn() > 0;
}

// Функция отправки email через PHPMailer
function send_email_smtp($to, $subject, $body_html) {
    require_once __DIR__ . '/vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'ssl://smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPAuth = true;
        $mail->Username = 'maximwork19@gmail.com'; // заменить на свой
        $mail->Password = 'uvrwkhhpxolxchlg'; // заменить на свой app password
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom('maximwork19@gmail.com', 'CodeSphere');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body_html;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Можно залогировать $mail->ErrorInfo
        return false;
    }
}

// Функция отправки email
function send_email($to, $subject, $message) {
    $headers = "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: CodeSphere <noreply@codesphere.local>\r\n";
    return mail($to, $subject, $message, $headers);
}