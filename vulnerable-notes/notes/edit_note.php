<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

// Fetch note and verify ownership
$stmt = $pdo->prepare("SELECT id, title, content FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    log_event('note_access_denied', "Note ID: $id");
    die('Заметка не найдена.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    
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
            // Prepared statement with user_id check
            $stmt = $pdo->prepare(
                "UPDATE notes SET title = ?, content = ? WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$title, $content, $id, $userId]);
            
            log_event('note_updated', "Note ID: $id, Title: " . substr($title, 0, 50));
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            log_event('note_update_error', "Note ID: $id, Error: " . $e->getMessage());
            $message = 'Ошибка при обновлении заметки.';
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Изменение заметки</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="card">
    <h1>Изменение заметки</h1>
    <?php if (isset($message)): ?><div class="message error"><?= e($message) ?></div><?php endif; ?>
    <form method="post">
        <label>Заголовок</label>
        <input type="text" name="title" value="<?= e($note['title']) ?>" maxlength="255" required>

        <label>Текст</label>
        <textarea name="content" rows="8" required><?= e($note['content']) ?></textarea>

        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <button type="submit">Сохранить изменения</button>
        <a class="button secondary" href="dashboard.php">Отмена</a>
    </form>
</main>
</body>
</html>