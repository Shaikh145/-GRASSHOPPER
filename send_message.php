<?php
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $recipient_id = $_POST['recipient_id'];
        $message = $_POST['message'];
        
        // Validate inputs
        if(empty($recipient_id) || empty($message)) {
            throw new Exception('Recipient and message are required');
        }

        // Insert message
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message_text, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], $recipient_id, $message]);
        
        // Create notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, content, created_at) 
            VALUES (?, 'message', ?, NOW())
        ");
        $stmt->execute([$recipient_id, "New message from " . $_SESSION['username']]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully'
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
