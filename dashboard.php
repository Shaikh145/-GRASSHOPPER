<?php
include 'header.php';
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch recent calls
$stmt = $pdo->prepare("
    SELECT ch.*, u.username as caller_name 
    FROM call_history ch 
    LEFT JOIN users u ON ch.caller_id = u.id 
    WHERE ch.receiver_id = ? 
    ORDER BY ch.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_calls = $stmt->fetchAll();

// Fetch unread messages count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM messages 
    WHERE receiver_id = ? AND is_read = 0
");
$stmt->execute([$_SESSION['user_id']]);
$unread_messages = $stmt->fetch()['count'];

// Fetch unread voicemails count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM voicemails 
    WHERE user_id = ? AND is_read = 0
");
$stmt->execute([$_SESSION['user_id']]);
$unread_voicemails = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .dashboard-container {
            padding: 30px 0;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .stats-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .recent-calls {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .call-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .call-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container dashboard-container">
        <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>Virtual Number</h4>
                    <div class="stats-number"><?php echo htmlspecialchars($user['virtual_number']); ?></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>Unread Messages</h4>
                    <div class="stats-number"><?php echo $unread_messages; ?></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>New Voicemails</h4>
                    <div class="stats-number"><?php echo $unread_voicemails; ?></div>
                </div>
            </div>
        </div>
        
        <div class="recent-calls mt-4">
            <h3>Recent Calls</h3>
            <?php if(count($recent_calls) > 0): ?>
                <?php foreach($recent_calls as $call): ?>
                    <div class="call-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($call['caller_name']); ?></strong>
                                <span class="text-muted ms-2"><?php echo $call['call_status']; ?></span>
                            </div>
                            <div class="text-muted">
                                <?php echo date('M d, Y H:i', strtotime($call['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No recent calls</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Real-time updates for messages and notifications
        function checkUpdates() {
            $.ajax({
                url: 'get_notification.php',
                success: function(data) {
                    if(data.messages > 0) {
                        $('.message-count').text(data.messages).show();
                    } else {
                        $('.message-count').hide();
                    }
                }
            });
        }

        setInterval(checkUpdates, 30000); // Check every 30 seconds
    </script>
</body>
</html>
