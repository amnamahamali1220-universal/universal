<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['exam_controller', 'senior_teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

// This is a placeholder for exam scheduling feature
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Exam Schedule Management</h3></div>
            <div class="col-sm-6 text-end">
                <a href="exam_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Uploaded Schedules</h3></div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> This feature allows you to upload and publish the exam schedules for all courses.
                </div>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Upload Schedule (PDF/Excel)</label>
                        <input type="file" class="form-control" name="schedule_file" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester/Session</label>
                        <input type="text" class="form-control" value="Spring 2026" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload & Publish</button>
                </form>

                <hr>

                <table class="table table-sm mt-4">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Uploaded At</th>
                            <th>Published By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Final_Term_Schedule_2026.pdf</td>
                            <td>2026-02-15</td>
                            <td>Exam Controller</td>
                            <td><button class="btn btn-xs btn-primary">View</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
