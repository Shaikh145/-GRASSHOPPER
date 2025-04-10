<?php
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Get unread messages count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as messages,
    (SELECT COUNT(*) FROM voicemails WHERE user_id = ? AND is_read = 0) as voicemails,
    (SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0) as notifications
    FROM messages 
    WHERE receiver_id = ? AND is_read = 0
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$counts = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode([
    'messages' => (int)$counts['messages'],
    'voicemails' => (int)$counts['voicemails'],
    'notifications' => (int)$counts['notifications']
]);
