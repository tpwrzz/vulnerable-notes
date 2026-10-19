<?php

/**
 * Escape output to prevent XSS
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token and store in session
 */
function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from POST request
 */
function csrf_check()
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token validation failed.');
    }
}

/**
 * Require user to be logged in
 */
function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Optional: check session idle timeout (30 min)
    $timeout = 30 * 60;
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeout) {
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Require user to be admin
 */
function require_admin()
{
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied.');
    }
}

/**
 * Log an event (login, logout, create/edit/delete, errors, etc.)
 */
function log_event($type, $details)
{
    $log_dir = dirname(__DIR__,2) . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $message = "[$timestamp] User:$user_id IP:$ip Type:$type Details:$details\n";

    $result = file_put_contents($log_file, $message, FILE_APPEND | LOCK_EX);

    if ($result === false) {
        error_log("Cannot write to log file: $log_file");
        return false;
    }

    return true;
}