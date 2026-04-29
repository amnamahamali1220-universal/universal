<?php
function logActivity($pdo, $user_id, $action) {
    if (!$pdo || !$user_id) return;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$user_id, $action]);
    } catch (PDOException $e) {
        // Silently fail so it doesn't break existing functionality
        error_log("Activity log error: " . $e->getMessage());
    }
}
?>
