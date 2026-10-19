<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    $errors = [];
    
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = 'Имя должно быть от 2 до 100 символов.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов.';
    }
    
    if ($errors) {
        $message = implode(' ', $errors);
    } else {
        try {
            // Hash password server-side
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepared statement (role is always 'user')
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')"
            );
            $stmt->execute([$name, $email, $password_hash]);
            
            log_event('register', "Email: $email");
            $message = 'Регистрация выполнена. Теперь можно войти.';
        } catch (PDOException $e) {
            log_event('register_error', "Email: $email, Error: " . $e->getMessage());
            $message = 'Ошибка регистрации. Возможно, этот email уже используется.';
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация | Vulnerable Notes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="card narrow">
    <div class="warning">Учебный проект. Использовать только локально.</div>
    <h1>Регистрация</h1>

    <?php if ($message): ?>
        <div class="message<?php echo strpos($message, 'выполнена') === false ? ' error' : ''; ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Имя</label>
        <input type="text" name="name" required>

        <label>Электронная почта</label>
        <input type="email" name="email" required>

        <label>Пароль (минимум 8 символов)</label>
        <input type="password" name="password" required>

        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <button type="submit">Зарегистрироваться</button>
    </form>

    <p><a href="login.php">Уже есть аккаунт? Войти</a></p>
</main>
</body>
</html>