<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/error.log');

error_reporting(E_ALL);

$host = 'localhost';
$database = 'vulnerable_notes';
$username = 'root';
$password = '';

$pdo = new PDO(
    "mysql:host=$host;dbname=$database;charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
]);

ini_set('session.use_strict_mode', '1');

session_start();