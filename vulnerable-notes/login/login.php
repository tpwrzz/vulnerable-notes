<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Пользователь не найден или пароль неправильный.';
        log_event('login_failed', "Invalid email format: $email");
    } else {
        try {
            // Prepared statement
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                log_event('login_success', "Email: $email");
                header('Location: ../notes/dashboard.php');
                exit;
            } else {
                // Generic error message for security
                $message = 'Пользователь не найден или пароль неправильный.';
                log_event('login_failed', "Email: $email");
            }
        } catch (PDOException $e) {
            $message = 'Ошибка входа. Попробуйте позже.';
            log_event('login_error', "Email: $email, Error: " . $e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход | Vulnerable Notes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="card narrow">
    <div class="warning">Учебный проект. Использовать только локально.</div>
    <h1>Vulnerable Notes</h1>
    <p class="muted">Приложение намеренно содержит уязвимости.</p>

    <?php if ($message): ?>
        <div class="message error"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['expired'])): ?>
        <div class="message">Сессия истекла. Пожалуйста, войдите заново.</div>
    <?php endif; ?>

    <form method="post">
        <label>Электронная почта</label>
        <input type="email" name="email" required>

        <label>Пароль</label>
        <input type="password" name="password" required>

        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <button type="submit">Войти</button>
    </form>

    <p><a href="register.php">Создать аккаунт</a></p>
</main>
</body>
</html>