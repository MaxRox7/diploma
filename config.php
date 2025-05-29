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

function is_admin() {
    $user = get_authenticated_user();
    return $user && $user['role_user'] === 'admin';
}