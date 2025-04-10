<?php
include 'header.php';
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch voicemails
$stmt = $pdo->prepare("
    SELECT v.*, u.username as caller_name
    FROM voicemails v
    LEFT JOIN users u ON v.caller_id = u.id
    WHERE v.user_id = ?
    ORDER BY v.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$voicemails = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .voicemail-container {
            max-width: 800px;
            margin: 30px auto;
        }
        
        .voicemail-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .voicemail-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .voicemail-item:last-child {
            border-bottom: none;
        }
        
        .voicemail-info {
            flex-grow: 1;
        }
        
        .voicemail-controls {
            display: flex;
            gap: 10px;
        }
        
        .unread {
            background-color: #f8f9fa;
        }
        
        .btn-play {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
        }
        
        .duration-badge {
            background-color: #e9ecef;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container voicemail-container">
        <h2 class="mb-4">Voicemail</h2>
        
        <div class="voicemail-box">
            <?php if(count($voicemails) > 0): ?>
                <?php foreach($voicemails as $voicemail): ?>
                    <div class="voicemail-item <?php echo (!$voicemail['is_read']) ? 'unread' : ''; ?>">
                        <div class="voicemail-info">
                            <div>
                                <strong><?php echo htmlspecialchars($voicemail['caller_name']); ?></strong>
                                <span class="text-muted ms-2">
                                    <?php echo date('M d, Y H:i', strtotime($voicemail['created_at'])); ?>
                                </span>
                            </div>
                            <span class="duration-badge">
                                <?php echo floor($voicemail['duration']/60).':'.str_pad($voicemail['duration']%60, 2, '0', STR_PAD_LEFT); ?>
                            </span>
                        </div>
                        <div class="voicemail-controls">
                            <button class="btn btn-play" onclick="playVoicemail('<?php echo $voicemail['audio_file']; ?>', <?php echo $voicemail['id']; ?>)">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="btn btn-danger" onclick="deleteVoicemail(<?php echo $voicemail['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No voicemails</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Audio Player Modal -->
    <div class="modal fade" id="audioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Playing Voicemail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <audio id="audioPlayer" controls>
                        Your browser does not support the audio element.
                    </audio>
                </div>
            </div>
        </div>
    </div>

    <script>
    function playVoicemail(audioFile, voicemailId) {
        const player = document.getElementById('audioPlayer');
        player.src = 'uploads/voicemails/' + audioFile;
        
        // Mark as read
        $.post('mark_voicemail_read.php', { voicemail_id: voicemailId });
        
        // Show modal
        new bootstrap.Modal(document.getElementById('audioModal')).show();
        player.play();
    }

    function deleteVoicemail(voicemailId) {
        if(confirm('Are you sure you want to delete this voicemail?')) {
            $.post('delete_voicemail.php', { voicemail_id: voicemailId }, function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error deleting voicemail');
                }
            });
        }
    }
    </script>
</body>
</html>
