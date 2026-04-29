<?php
require_once '../../core/session.php';
require_once '../../core/db.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Send Message
if (isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $msg = $_POST['message'];
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $receiver_id, $msg]);
    echo "<script>alert('Message Sent'); window.location.href='messages.php';</script>";
}

// Fetch Messages
$inbox = $pdo->prepare("
    SELECT m.*, u.name as sender_name 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = ? 
    ORDER BY m.created_at DESC
");
$inbox->execute([$user_id]);
$messages = $inbox->fetchAll();

// Fetch Sent
$sent = $pdo->prepare("
    SELECT m.*, u.name as receiver_name 
    FROM messages m 
    JOIN users u ON m.receiver_id = u.id 
    WHERE m.sender_id = ? 
    ORDER BY m.created_at DESC
");
$sent->execute([$user_id]);
$sent_messages = $sent->fetchAll();

// Fetch Recipients (Simplified: All users for now, or filtered)
// In a real app, only show relevant users (e.g. Teacher -> their students)
if ($role == 'admin') {
    $recipients_sql = "SELECT id, name, role FROM users WHERE id != ?";
} elseif ($role == 'teacher') {
    // Teachers can message anyone (simplified) or just students
    $recipients_sql = "SELECT id, name, role FROM users WHERE id != ?"; // restricted logic would be better
} else {
    // Students can message teachers and admins
    $recipients_sql = "SELECT id, name, role FROM users WHERE id != ? AND role IN ('teacher', 'admin')";
}
$stmt = $pdo->prepare($recipients_sql);
$stmt->execute([$user_id]);
$recipients = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Messages</h3></div>
            <div class="col-sm-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">Compose</button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <ul class="nav nav-tabs" id="msgTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#inbox">Inbox</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sent">Sent</a></li>
        </ul>
        
        <div class="tab-content p-3 border border-top-0 bg-white">
            <div class="tab-pane fade show active" id="inbox">
                <div class="list-group">
                    <?php foreach($messages as $m): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?= htmlspecialchars($m['sender_name']) ?></h5>
                            <small><?= $m['created_at'] ?></small>
                        </div>
                        <p class="mb-1"><?= htmlspecialchars($m['message']) ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($messages)) echo "No messages recieved."; ?>
                </div>
            </div>
            
            <div class="tab-pane fade" id="sent">
                 <div class="list-group">
                    <?php foreach($sent_messages as $m): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">To: <?= htmlspecialchars($m['receiver_name']) ?></h5>
                            <small><?= $m['created_at'] ?></small>
                        </div>
                        <p class="mb-1"><?= htmlspecialchars($m['message']) ?></p>
                    </div>
                    <?php endforeach; ?>
                     <?php if(empty($sent_messages)) echo "No messages sent."; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Compose Message</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Recipient</label>
                    <select name="receiver_id" class="form-control" required>
                        <option value="">Select User</option>
                        <?php foreach($recipients as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?> (<?= ucfirst($r['role']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Message</label>
                    <textarea name="message" class="form-control" rows="4" required></textarea>
                </div>
                <input type="hidden" name="send_message" value="1">
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Send</button></div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
