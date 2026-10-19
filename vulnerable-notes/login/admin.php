<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

require_admin();

// Prepared statement, select only non-sensitive fields
$stmt = $pdo->prepare(
    'SELECT id, name, email, role FROM users ORDER BY id'
);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

log_event('admin_page_accessed', 'User viewed admin panel');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Администрирование</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="container">
    <h1>Пользователи системы</h1>
    <p class="warning">Эта страница доступна только администратору.</p>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Роль</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['id']) ?></td>
                <td><?= e($user['name']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td><?= e($user['role']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p><a class="button secondary" href="../notes/dashboard.php">Назад</a></p>
</main>
</body>
</html>