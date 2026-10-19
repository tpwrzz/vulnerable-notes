<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

require_login();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $userId = $_SESSION['user_id'];
    
    // Validate input
    $errors = [];
    
    if (strlen(trim($title)) < 1 || strlen($title) > 255) {
        $errors[] = 'Заголовок должен быть от 1 до 255 символов.';
    }
    
    if (strlen(trim($content)) < 1) {
        $errors[] = 'Содержимое заметки не может быть пустым.';
    }
    
    if ($errors) {
        $message = implode(' ', $errors);
    } else {
        try {
            // Prepared statement
            $stmt = $pdo->prepare(
                "INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)"
            );
            $stmt->execute([$userId, $title, $content]);
            
            log_event('note_created', "Title: " . substr($title, 0, 50));
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            log_event('note_create_error', "Error: " . $e->getMessage());
            $message = 'Ошибка при создании заметки.';
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Новая заметка</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="card">
    <h1>Новая заметка</h1>
    <?php if ($message): ?><div class="message error"><?= e($message) ?></div><?php endif; ?>
    <form method="post">
        <label>Заголовок</label>
        <input type="text" name="title" maxlength="255" required>

        <label>Текст</label>
        <textarea name="content" rows="8" required></textarea>

        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <button type="submit">Сохранить</button>
        <a class="button secondary" href="dashboard.php">Отмена</a>
    </form>
</main>
</body>
</html>