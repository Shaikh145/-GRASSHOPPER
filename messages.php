<?php
include 'header.php';
include 'db_connect.php';
<?php
include 'header.php';
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all users except current user
$stmt = $pdo->prepare("
    SELECT id, username, virtual_number 
    FROM users 
    WHERE id != ?
");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();

// Fetch conversations
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .messages-container {
            display: flex;
            gap: 20px;
            margin: 20px;
            height: calc(100vh - 100px);
        }
        
        .users-list {
            width: 300px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .chat-area {
            flex-grow: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        .messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 10px;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
        }
        
        .sent {
            background-color: var(--primary-color);
            color: white;
            margin-left: auto;
        }
        
        .received {
            background-color: #f1f1f1;
            margin-right: auto;
        }
        
        .message-time {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        
        .user-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        
        .user-item:hover {
            background-color: #f8f9fa;
        }
        
        .message-form {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="messages-container">
        <div class="users-list">
            <h4 class="mb-3">Users</h4>
            <?php foreach($users as $user): ?>
                <div class="user-item" onclick="selectUser(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>')">
                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                    <div class="text-muted small"><?php echo htmlspecialchars($user['virtual_number']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="chat-area">
            <h4 class="mb-3">Messages <span id="chatWith"></span></h4>
            <div class="messages" id="messageArea">
                <?php foreach($messages as $message): ?>
                    <div class="message-bubble <?php echo ($message['sender_id'] == $_SESSION['user_id']) ? 'sent' : 'received'; ?>">
                        <div><?php echo htmlspecialchars($message['message_text']); ?></div>
                        <div class="message-time">
                            <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <form id="messageForm" class="message-form">
                <input type="hidden" id="recipient_id" name="recipient_id">
                <input type="text" class="form-control" id="message" name="message" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>

    <script>
    let selectedUserId = null;
    
    function selectUser(userId, username) {
        selectedUserId = userId;
        document.getElementById('recipient_id').value = userId;
        document.getElementById('chatWith').textContent = '- Chatting with ' + username;
        loadMessages(userId);
    }
    
    function loadMessages(userId) {
        $.ajax({
            url: 'get_messages.php',
            method: 'POST',
            data: { user_id: userId },
            success: function(response) {
                if(response.success) {
                    displayMessages(response.messages);
                }
            }
        });
    }
    
    function displayMessages(messages) {
        const messageArea = document.getElementById('messageArea');
        messageArea.innerHTML = '';
        
        messages.forEach(message => {
            const bubble = document.createElement('div');
            bubble.className = `message-bubble ${message.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received'}`;
            bubble.innerHTML = `
                <div>${message.message_text}</div>
                <div class="message-time">${message.created_at}</div>
            `;
            messageArea.appendChild(bubble);
        });
        
        messageArea.scrollTop = messageArea.scrollHeight;
    }
    
    $('#messageForm').on('submit', function(e) {
        e.preventDefault();
        
        if(!selectedUserId) {
            alert('Please select a user to message');
            return;
        }
        
        const message = $('#message').val();
        
        $.ajax({
            url: 'send_message.php',
            method: 'POST',
            data: {
                recipient_id: selectedUserId,
                message: message
            },
            success: function(response) {
                if(response.success) {
                    $('#message').val('');
                    loadMessages(selectedUserId);
                } else {
                    alert(response.error || 'Error sending message');
                }
            }
        });
    });
    
    // Check for new messages every 5 seconds
    setInterval(function() {
        if(selectedUserId) {
            loadMessages(selectedUserId);
        }
    }, 5000);
    </script>
</body>
</html>
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all users except current user
$stmt = $pdo->prepare("
    SELECT id, username, virtual_number 
    FROM users 
    WHERE id != ?
");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();

// Fetch conversations
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .messages-container {
            display: flex;
            gap: 20px;
            margin: 20px;
            height: calc(100vh - 100px);
        }
        
        .users-list {
            width: 300px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .chat-area {
            flex-grow: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        .messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 10px;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
        }
        
        .sent {
            background-color: var(--primary-color);
            color: white;
            margin-left: auto;
        }
        
        .received {
            background-color: #f1f1f1;
            margin-right: auto;
        }
        
        .message-time {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        
        .user-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        
        .user-item:hover {
            background-color: #f8f9fa;
        }
        
        .message-form {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="messages-container">
        <div class="users-list">
            <h4 class="mb-3">Users</h4>
            <?php foreach($users as $user): ?>
                <div class="user-item" onclick="selectUser(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>')">
                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                    <div class="text-muted small"><?php echo htmlspecialchars($user['virtual_number']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="chat-area">
            <h4 class="mb-3">Messages <span id="chatWith"></span></h4>
            <div class="messages" id="messageArea">
                <?php foreach($messages as $message): ?>
                    <div class="message-bubble <?php echo ($message['sender_id'] == $_SESSION['user_id']) ? 'sent' : 'received'; ?>">
                        <div><?php echo htmlspecialchars($message['message_text']); ?></div>
                        <div class="message-time">
                            <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <form id="messageForm" class="message-form">
                <input type="hidden" id="recipient_id" name="recipient_id">
                <input type="text" class="form-control" id="message" name="message" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary">
