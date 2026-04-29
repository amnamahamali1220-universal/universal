<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';

?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Admin Dashboard</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>Students</h3>
                        <p>Manage Students</p>
                    </div>
                    <a href="students.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>Teachers</h3>
                        <p>Manage Teachers</p>
                    </div>
                    <a href="teachers.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>Courses</h3>
                        <p>Manage Courses</p>
                    </div>
                    <a href="courses.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
