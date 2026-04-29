<?php
require_once '../../core/session.php';
require_once '../../core/db.php';

// Only admins can perform actions
if (!in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $title = $_POST['title'] ?? '';
            $event_type = $_POST['event_type'] ?? 'Academic Event';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            $created_by = $_SESSION['user_id'];

            if (empty($title) || empty($start_date)) {
                throw new Exception("Title and Start Date are required.");
            }

            $stmt = $pdo->prepare("INSERT INTO academic_events (title, event_type, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $event_type, $start_date, $end_date, $created_by]);

            echo json_encode(['status' => 'success', 'message' => 'Event added successfully']);
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $event_type = $_POST['event_type'] ?? 'Academic Event';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            if (empty($id) || empty($title) || empty($start_date)) {
                throw new Exception("ID, Title and Start Date are required.");
            }

            $stmt = $pdo->prepare("UPDATE academic_events SET title = ?, event_type = ?, start_date = ?, end_date = ? WHERE id = ?");
            $stmt->execute([$title, $event_type, $start_date, $end_date, $id]);

            echo json_encode(['status' => 'success', 'message' => 'Event updated successfully']);
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if (empty($id)) throw new Exception("ID is required.");

            $stmt = $pdo->prepare("DELETE FROM academic_events WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['status' => 'success', 'message' => 'Event deleted successfully']);
        } else {
            throw new Exception("Invalid action.");
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>
