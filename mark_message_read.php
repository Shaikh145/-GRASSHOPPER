<?php
include 'db_connect.php';

if(!isset($_SESSION['user_id']) || !isset($_POST['message_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit();
}

$message_id = $_POST['message_id'];

try {
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE id = ? AND receiver_id = ?
    ");
    $stmt->execute([$message_id, $_SESSION['user_id']]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
