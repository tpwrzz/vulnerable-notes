<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

require_login();

$userId = $_SESSION['user_id'];

// Prepared statement with user_id check
$stmt = $pdo->prepare(
    "SELECT id, title, content, created_at, updated_at FROM notes WHERE user_id = ? ORDER BY created_at DESC"
);
$stmt->execute([$userId]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Мои заметки | Vulnerable Notes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="topbar">
    <strong>Vulnerable Notes</strong>
    <nav>
        <a href="dashboard.php">Мои заметки</a>
        <a href="add_note.php">Добавить</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../login/admin.php">Администрирование</a>
        <?php endif; ?>
        <a href="../login/logout.php">Выйти</a>
    </nav>
</header>

<main class="container">
    <h1>Здравствуйте, <?= e($_SESSION['name']) ?>!</h1>
    <p>Ваш уровень доступа: <strong><?= e($_SESSION['role']) ?></strong></p>

    <div class="actions">
        <a class="button" href="add_note.php">Новая заметка</a>
    </div>

    <?php if (!$notes): ?>
        <div class="empty">У вас пока нет заметок.</div>
    <?php endif; ?>

    <section class="notes">
        <?php foreach ($notes as $note): ?>
            <article class="note">
                <h2><?= e($note['title']) ?></h2>
                <div><?= nl2br(e($note['content'])) ?></div>
                <small><?= e($note['created_at']) ?></small>
                <div class="note-actions">
                    <a href="edit_note.php?id=<?= (int)$note['id'] ?>">Изменить</a>
                    <form method="post" action="delete_note.php" style="display: inline;">
                        <input type="hidden" name="id" value="<?= (int)$note['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="danger" onclick="return confirm('Удалить заметку?');">Удалить</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>