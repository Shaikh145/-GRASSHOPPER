<?php
include 'header.php';
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user settings
$stmt = $pdo->prepare("
    SELECT u.*, s.*
    FROM users u
    LEFT JOIN settings s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();

$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Update forward number
        $stmt = $pdo->prepare("UPDATE users SET forward_number = ? WHERE id = ?");
        $stmt->execute([$_POST['forward_number'], $_SESSION['user_id']]);
        
        // Update or insert settings
        $stmt = $pdo->prepare("
            INSERT INTO settings 
            (user_id, voicemail_greeting, call_forwarding_enabled, business_hours_only, 
             business_hours_start, business_hours_end) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            voicemail_greeting = VALUES(voicemail_greeting),
            call_forwarding_enabled = VALUES(call_forwarding_enabled),
            business_hours_only = VALUES(business_hours_only),
            business_hours_start = VALUES(business_hours_start),
            business_hours_end = VALUES(business_hours_end)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['voicemail_greeting'],
            isset($_POST['call_forwarding_enabled']),
            isset($_POST['business_hours_only']),
            $_POST['business_hours_start'],
            $_POST['business_hours_end']
        ]);
        
        $success_message = 'Settings updated successfully!';
    } catch(PDOException $e) {
        $error_message = 'Error updating settings: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .settings-container {
            max-width: 800px;
            margin: 30px auto;
        }
        
        .settings-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .form-switch {
            padding-left: 2.5em;
        }
    </style>
</head>
<body>
    <div class="container settings-container">
        <h2 class="mb-4">Settings</h2>
        
        <?php if($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="settings-box">
            <form method="POST" action="">
                <h4 class="section-title">Phone Numbers</h4>
                <div class="mb-3">
                    <label class="form-label">Virtual Number</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['virtual_number']); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Forward Number</label>
                    <input type="text" class="form-control" name="forward_number" 
                           value="<?php echo htmlspecialchars($user_data['forward_number']); ?>">
                </div>
                
                <h4 class="section-title mt-4">Call Settings</h4>
                <div class="mb-3 form-switch">
                    <input type="checkbox" class="form-check-input" id="callForwarding" 
                           name="call_forwarding_enabled" 
                           <?php echo $user_data['call_forwarding_enabled'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="callForwarding">Enable Call Forwarding</label>
                </div>
                
                <div class="mb-3 form-switch">
                    <input type="checkbox" class="form-check-input" id="businessHours" 
                           name="business_hours_only"
                           <?php echo $user_data['business_hours_only'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="businessHours">Business Hours Only</label>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Business Hours Start</label>
                            <input type="time" class="form-control" name="business_hours_start"
                                   value="<?php echo $user_data['business_hours_start']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Business Hours End</label>
                            <input type="time" class="form-control" name="business_hours_end"
                                   value="<?php echo $user_data['business_hours_end']; ?>">
                        </div>
                    </div>
                </div>
                
                <h4 class="section-title mt-4">Voicemail Settings</h4>
                <div class="mb-3">
                    <label class="form-label">Voicemail Greeting</label>
                    <textarea class="form-control" name="voicemail_greeting" rows="3"><?php echo htmlspecialchars($user_data['voicemail_greeting']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</body>
</html>
