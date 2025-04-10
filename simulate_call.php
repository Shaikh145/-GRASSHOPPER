<?php
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caller_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    
    try {
        // Get receiver's settings
        $stmt = $pdo->prepare("
            SELECT u.*, s.*
            FROM users u
            LEFT JOIN settings s ON u.id = s.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$receiver_id]);
        $receiver = $stmt->fetch();
        
        $call_status = 'missed';
        $forward_to = null;
        
        // Check if call forwarding is enabled
        if($receiver['call_forwarding_enabled']) {
            // Check business hours if enabled
            if($receiver['business_hours_only']) {
                $current_time = date('H:i:s');
                $business_start = $receiver['business_hours_start'];
                $business_end = $receiver['business_hours_end'];
                
                if($current_time >= $business_start && $current_time <= $business_end) {
                    $call_status = 'forwarded';
                    $forward_to = $receiver['forward_number'];
                }
            } else {
                $call_status = 'forwarded';
                $forward_to = $receiver['forward_number'];
            }
        }
        
        // Record call in history
        $stmt = $pdo->prepare("
            INSERT INTO call_history (caller_id, receiver_id, call_status, call_duration)
            VALUES (?, ?, ?, ?)
        ");
        $duration = rand(30, 300); // Random duration between 30-300 seconds
        $stmt->execute([$caller_id, $receiver_id, $call_status, $duration]);
        
        // Create notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, content)
            VALUES (?, 'call', ?)
        ");
        $notification_content = "Missed call from " . $_SESSION['username'];
        if($call_status == 'forwarded') {
            $notification_content = "Call forwarded to " . $forward_to;
        }
        $stmt->execute([$receiver_id, $notification_content]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'status' => $call_status,
            'forward_to' => $forward_to
        ]);
        
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
