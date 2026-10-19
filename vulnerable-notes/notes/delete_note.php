<?php
require_once '../config/config.php';
require_once '../config/helpers.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

csrf_check();

$id = (int)($_POST['id'] ?? 0);
$userId = $_SESSION['user_id'];

try {
    // Verify ownership before delete
    $stmt = $pdo->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$note) {
        log_event('note_delete_denied', "Note ID: $id (not owned)");
        header('Location: dashboard.php');
        exit;
    }
    
    // Delete with user_id check
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    log_event('note_deleted', "Note ID: $id");
    header('Location: dashboard.php');
    exit;
} catch (PDOException $e) {
    log_event('note_delete_error', "Note ID: $id, Error: " . $e->getMessage());
    die('Ошибка при удалении заметки.');
}

