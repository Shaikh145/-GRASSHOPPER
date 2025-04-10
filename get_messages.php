<?php
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    try {
        $other_user_id = $_POST['user_id'];
        
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   u1.username as sender_name,
                   u2.username as receiver_name
            FROM messages m
            JOIN users u1 ON m.sender_id = u1.id
            JOIN users u2 ON m.receiver_id = u2.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([
            $_SESSION['user_id'], $other_user_id,
            $other_user_id, $_SESSION['user_id']
        ]);
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE receiver_id = ? AND sender_id = ? AND is_read = 0
        ");
        $stmt->execute([$_SESSION['user_id'], $other_user_id]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    } catch(Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
?>
