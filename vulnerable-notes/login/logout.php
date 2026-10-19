<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Show logout confirmation form
    ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Выход</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="card narrow">
    <h1>Выход</h1>
    <p>Вы уверены, что хотите выйти?</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit">Да, выйти</button>
        <a class="button secondary" href="../notes/dashboard.php">Отмена</a>
    </form>
</main>
</body>
</html>
    <?php
    exit;
}

// Handle POST logout
csrf_check();

log_event('logout', 'User logged out');

// Clear session data
unset($_SESSION['user_id']);
unset($_SESSION['name']);
unset($_SESSION['role']);
unset($_SESSION['last_activity']);
unset($_SESSION['csrf_token']);

// Destroy session
session_destroy();

// Expire the session cookie
setcookie(session_name(), '', time() - 3600, '/');

header('Location: login.php');
exit;