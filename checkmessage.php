<?php
include 'header.php';
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all messages
$stmt = $pdo->prepare("
    SELECT m.*, 
           u1.username as sender_name,
           u2.username as receiver_name
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id
    JOIN users u2 ON m.receiver_id = u2.id
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$messages = $stmt->fetchAll();

// Mark messages as read
$stmt = $pdo->prepare("
    UPDATE messages 
    SET is_read = 1 
    WHERE receiver_id = ? AND is_read = 0
");
$stmt->execute([$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .messages-container {
            max-width: 800px;
            margin: 30px auto;
        }
        
        .message-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .message-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .message-sender {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .message-time {
            color: #666;
            font-size: 0.9em;
        }
        
        .message-content {
            margin-top: 10px;
        }
        
        .unread {
            background-color: #f8f9fa;
        }
        
        .compose-message {
            position: fixed;
            bottom: 30px;
            right: 30px;
        }
        
        .btn-compose {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container messages-container">
        <h2 class="mb-4">Messages</h2>
        
        <div class="message-box">
            <?php if(count($messages) > 0): ?>
                <?php foreach($messages as $message): ?>
                    <div class="message-item <?php echo (!$message['is_read'] && $message['receiver_id'] == $_SESSION['user_id']) ? 'unread' : ''; ?>">
                        <div class="message-header">
                            <span class="message-sender">
                                <?php 
                                if($message['sender_id'] == $_SESSION['user_id']) {
                                    echo "To: " . htmlspecialchars($message['receiver_name']);
                                } else {
                                    echo "From: " . htmlspecialchars($message['sender_name']);
                                }
                                ?>
                            </span>
                            <span class="message-time">
                                <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                            </span>
                        </div>
                        <div class="message-content">
                            <?php echo htmlspecialchars($message['message_text']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No messages yet</p>
            <?php endif; ?>
        </div>
        
        <div class="compose-message">
            <button type="button" class="btn btn-compose" data-bs-toggle="modal" data-bs-target="#composeModal">
                <i class="fas fa-pen"></i>
            </button>
        </div>
    </div>

    <!-- Compose Message Modal -->
    <div class="modal fade" id="composeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="messageForm">
                        <div class="mb-3">
                            <label for="recipient" class="form-label">Recipient</label>
                            <select class="form-select" id="recipient" required>
                                <?php
                                $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id != ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                while($user = $stmt->fetch()) {
                                    echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['username']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="sendMessage()">Send</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function sendMessage() {
        const recipient = document.getElementById('recipient').value;
        const message = document.getElementById('message').value;
        
        $.ajax({
            url: 'send_message.php',
            method: 'POST',
            data: {
                recipient_id: recipient,
                message: message
            },
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error sending message');
                }
            },
            error: function() {
                alert('Error sending message');
            }
        });
    }
    </script>
</body>
</html>
