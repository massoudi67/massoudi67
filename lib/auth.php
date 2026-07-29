<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_admin_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

function is_admin_logged_in(): bool
{
    start_admin_session();
    
    if (!isset($_SESSION['admin_id']) || !is_numeric($_SESSION['admin_id'])) {
        return false;
    }
    
    if (!isset($_SESSION['admin_login_time']) || !is_numeric($_SESSION['admin_login_time'])) {
        return false;
    }
    
    $sessionExpiry = 86400;
    if ((time() - $_SESSION['admin_login_time']) > $sessionExpiry) {
        logout_admin();
        return false;
    }
    
    return true;
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function login_admin(string $username, string $password): bool
{
    start_admin_session();
    
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        error_log("Admin login failed: user not found - $username");
        return false;
    }
    
    $hash = (string)$admin['password_hash'];
    if (!password_verify($password, $hash)) {
        error_log("Admin login failed: wrong password for $username");
        return false;
    }
    
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_username'] = (string)$admin['username'];
    $_SESSION['admin_login_time'] = time();
    $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    error_log("Admin login success: $username");
    return true;
}

function logout_admin(): void
{
    start_admin_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
