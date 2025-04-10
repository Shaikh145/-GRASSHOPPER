<?php
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

function createNotification($user_id, $type, $content) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, content)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $type, $content]);
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'send':
            try {
                $recipient_id = $_POST['recipient_id'];
                $message = $_POST['message'];
                
                $stmt = $pdo->prepare("
                    INSERT INTO messages (sender_id, receiver_id, message_text)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$_SESSION['user_id'], $recipient_id, $message]);
                
                createNotification($recipient_id, 'message', "New message from " . $_SESSION['username']);
                
                echo json_encode(['success' => true]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        case 'mark_read':
            try {
                $message_id = $_POST['message_id'];
                
                $stmt = $pdo->prepare("
                    UPDATE messages 
                    SET is_read = 1 
                    WHERE id = ? AND receiver_id = ?
                ");
                $stmt->execute([$message_id, $_SESSION['user_id']]);
                
                echo json_encode(['success' => true]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        case 'delete':
            try {
                $message_id = $_POST['message_id'];
                
                $stmt = $pdo->prepare("
                    DELETE FROM messages 
                    WHERE id = ? AND (sender_id = ? OR receiver_id = ?)
                ");
                $stmt->execute([$message_id, $_SESSION['user_id'], $_SESSION['user_id']]);
                
                echo json_encode(['success' => true]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}
?>
