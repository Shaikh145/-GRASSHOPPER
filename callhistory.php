<?php
include 'header.php';
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch call history
$stmt = $pdo->prepare("
    SELECT ch.*, 
           u1.username as caller_name,
           u1.virtual_number as caller_number,
           u2.username as receiver_name,
           u2.virtual_number as receiver_number
    FROM call_history ch
    JOIN users u1 ON ch.caller_id = u1.id
    JOIN users u2 ON ch.receiver_id = u2.id
    WHERE ch.caller_id = ? OR ch.receiver_id = ?
    ORDER BY ch.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$calls = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .history-container {
            max-width: 900px;
            margin: 30px auto;
        }
        
        .call-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .call-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .call-item:last-child {
            border-bottom: none;
        }
        
        .call-info {
            flex-grow: 1;
        }
        
        .call-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        
        .status-missed {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .status-answered {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-forwarded {
            background-color: #e3f2fd;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container history-container">
        <h2 class="mb-4">Call History</h2>
        
        <div class="call-box">
            <?php if(count($calls) > 0): ?>
                <?php foreach($calls as $call): ?>
                    <div class="call-item">
                        <div class="call-info">
                            <?php if($call['caller_id'] == $_SESSION['user_id']): ?>
                                <div>
                                    <strong>Outgoing call to:</strong> 
                                    <?php echo htmlspecialchars($call['receiver_name']); ?>
                                    (<?php echo htmlspecialchars($call['receiver_number']); ?>)
                                </div>
                            <?php else: ?>
                                <div>
                                    <strong>Incoming call from:</strong>
                                    <?php echo htmlspecialchars($call['caller_name']); ?>
                                    (<?php echo htmlspecialchars($call['caller_number']); ?>)
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-muted">
                                <?php echo date('M d, Y H:i', strtotime($call['created_at'])); ?>
                                - Duration: <?php echo floor($call['call_duration']/60).':'.str_pad($call['call_duration']%60, 2, '0', STR_PAD_LEFT); ?>
                            </div>
                        </div>
                        
                        <span class="call-status status-<?php echo $call['call_status']; ?>">
                            <?php echo ucfirst($call['call_status']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No call history</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
