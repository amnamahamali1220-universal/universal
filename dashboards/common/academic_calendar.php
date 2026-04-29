<?php
require_once '../../core/session.php';
// This page is common to all logged-in users
require_once '../../core/db.php';
require_once '../../includes/visual_helper.php';
require_once '../../includes/header.php';

$student_id = ($_SESSION['role'] == 'student') ? $_SESSION['user_id'] : null;
$teacher_id = ($_SESSION['role'] == 'teacher') ? $_SESSION['user_id'] : null;
$is_admin = in_array($_SESSION['role'], ['admin', 'super_admin']);

// Fetch Assignments as Events
$events = [];

if ($student_id) {
    // Only assignments from enrolled courses
    $stmt = $pdo->prepare("
        SELECT a.title, a.due_date, c.title as course_name 
        FROM assignments a 
        JOIN enrollments e ON a.course_id = e.course_id 
        JOIN courses c ON a.course_id = c.id
        WHERE e.student_id = ?
    ");
    $stmt->execute([$student_id]);
} elseif ($teacher_id) {
    // Only assignments from teacher's courses
    $stmt = $pdo->prepare("
        SELECT a.title, a.due_date, c.title as course_name 
        FROM assignments a 
        JOIN courses c ON a.course_id = c.id
        WHERE c.teacher_id = ?
    ");
    $stmt->execute([$teacher_id]);
} else {
    // Admin sees all
    $stmt = $pdo->prepare("
        SELECT a.title, a.due_date, c.title as course_name 
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
    ");
    $stmt->execute();
}

while ($row = $stmt->fetch()) {
    $events[] = [
        'title' => 'Deadline: ' . $row['title'] . ' (' . $row['course_name'] . ')',
        'start' => $row['due_date'],
        'color' => '#f39c12' // Warning/Orange
    ];
}

// Fetch Announcements/Events (if any date is associated)
$ann_stmt = $pdo->query("SELECT title, created_at FROM announcements");
while ($row = $ann_stmt->fetch()) {
    $events[] = [
        'title' => 'Event: ' . $row['title'],
        'start' => $row['created_at'],
        'color' => '#007bff' // Primary/Blue
    ];
}

// Fetch Academic Events (Holidays, Exams, etc. created by Admin)
$ev_stmt = $pdo->query("SELECT id, title, start_date, end_date, event_type FROM academic_events");
while ($row = $ev_stmt->fetch()) {
    $color = '#28a745'; // Green for General Event
    if ($row['event_type'] == 'Exam') $color = '#dc3545'; // Red for Exam
    if ($row['event_type'] == 'Holiday') $color = '#17a2b8'; // Teal for Holiday
    
    $events[] = [
        'id' => 'ev_' . $row['id'],
        'title' => $row['event_type'] . ': ' . $row['title'],
        'start' => $row['start_date'],
        'end' => $row['end_date'],
        'color' => $color,
        'extendedProps' => [
            'is_editable' => true,
            'real_id' => $row['id'],
            'raw_title' => $row['title'],
            'event_type' => $row['event_type']
        ]
    ];
}

echo getCalendarScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Academic Calendar</h3>
            </div>
            <div class="col-sm-6 text-end">
                <?php if ($is_admin): ?>
                <button class="btn btn-primary shadow-sm" onclick="openAddModal()">
                    <i class="bi bi-plus-lg"></i> Add Event
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card p-3 shadow-sm border-0">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="eventModalTitle">Add/Edit Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="eventForm">
            <input type="hidden" id="eventId" name="id">
            <input type="hidden" id="eventAction" name="action" value="create">
            <div class="mb-3">
                <label class="form-label fw-semibold">Event Title</label>
                <input type="text" class="form-control" id="eventTitle" name="title" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Event Type</label>
                <select class="form-select" id="eventType" name="event_type">
                    <option value="Academic Event">Academic Event</option>
                    <option value="Exam">Exam</option>
                    <option value="Holiday">Holiday</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Start Date & Time</label>
                <input type="datetime-local" class="form-control" id="eventStart" name="start_date" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">End Date & Time (Optional)</label>
                <input type="datetime-local" class="form-control" id="eventEnd" name="end_date">
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-danger me-auto" id="btnDeleteEvent" style="display:none;" onclick="deleteEvent()">Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveEvent()">Save Event</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?= json_encode($events) ?>,
        themeSystem: 'standard',
        eventClick: function(info) {
            <?php if ($is_admin): ?>
            if (info.event.extendedProps && info.event.extendedProps.is_editable) {
                document.getElementById('eventId').value = info.event.extendedProps.real_id;
                document.getElementById('eventTitle').value = info.event.extendedProps.raw_title;
                document.getElementById('eventType').value = info.event.extendedProps.event_type;
                
                // Format dates to local datetime-local string format
                const toLocalISOString = (date) => {
                    const tzoffset = (new Date()).getTimezoneOffset() * 60000;
                    return (new Date(date.getTime() - tzoffset)).toISOString().slice(0, 16);
                };
                
                document.getElementById('eventStart').value = toLocalISOString(info.event.start);
                if (info.event.end) {
                    document.getElementById('eventEnd').value = toLocalISOString(info.event.end);
                } else {
                    document.getElementById('eventEnd').value = '';
                }
                
                document.getElementById('eventAction').value = 'update';
                document.getElementById('eventModalTitle').innerText = 'Edit Event';
                document.getElementById('btnDeleteEvent').style.display = 'block';
                
                var modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();
            }
            <?php endif; ?>
        }
    });
    calendar.render();
});

<?php if ($is_admin): ?>
function openAddModal() {
    document.getElementById('eventForm').reset();
    document.getElementById('eventId').value = '';
    document.getElementById('eventAction').value = 'create';
    document.getElementById('eventModalTitle').innerText = 'Add Event';
    document.getElementById('btnDeleteEvent').style.display = 'none';
    var modal = new bootstrap.Modal(document.getElementById('eventModal'));
    modal.show();
}

function saveEvent() {
    let formData = new FormData(document.getElementById('eventForm'));
    fetch('../admin/calendar_action.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if(data.status === 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    }).catch(err => {
        console.error(err);
        alert('An error occurred.');
    });
}

function deleteEvent() {
    if(!confirm("Are you sure you want to delete this event?")) return;
    let formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', document.getElementById('eventId').value);
    
    fetch('../admin/calendar_action.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if(data.status === 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    }).catch(err => {
        console.error(err);
        alert('An error occurred.');
    });
}
<?php endif; ?>
</script>

<style>
/* Adjusting FullCalendar to fit AdminLTE theme better if needed */
#calendar {
    max-width: 1100px;
    margin: 0 auto;
}
.fc-event {
    cursor: pointer;
}
</style>

<?php require_once '../../includes/footer.php'; ?>
