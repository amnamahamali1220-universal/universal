-- SQL Migration for Visual Enhancement Module

-- 1. Register New Pages in sys_pages
-- Student Pages
INSERT IGNORE INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES 
(0, 'Visual Analytics', '#', 'bi bi-pie-chart-fill', 10);
SET @student_viz_header = LAST_INSERT_ID();

INSERT IGNORE INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES 
(@student_viz_header, 'My Visual Stats', 'dashboards/student/visual_stats.php', 'bi bi-graph-up', 1),
(@student_viz_header, 'Attendance Analytics', 'dashboards/student/attendance_stats.php', 'bi bi-calendar-check', 2),
(@student_viz_header, 'Academic Calendar', 'dashboards/common/academic_calendar.php', 'bi bi-calendar3', 3);

-- Teacher Pages
INSERT IGNORE INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES 
(0, 'Teacher Analytics', '#', 'bi bi-bar-chart-line-fill', 11);
SET @teacher_viz_header = LAST_INSERT_ID();

INSERT IGNORE INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES 
(@teacher_viz_header, 'Course Performance', 'dashboards/teacher/visual_stats.php', 'bi bi-activity', 1),
(@teacher_viz_header, 'Academic Calendar', 'dashboards/common/academic_calendar.php', 'bi bi-calendar3', 2);

-- Admin Pages
INSERT IGNORE INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES 
(0, 'Global Reports', '#', 'bi bi-clipboard-data-fill', 12);
SET @admin_viz_header = LAST_INSERT_ID();

INSERT IGNORE INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES 
(@admin_viz_header, 'Grade Distribution', 'dashboards/admin/grade_distribution.php', 'bi bi-briefcase-fill', 1),
(@admin_viz_header, 'Academic Calendar', 'dashboards/common/academic_calendar.php', 'bi bi-calendar3', 2);

-- 2. Grant Access Permissions
-- Student Access
INSERT IGNORE INTO role_access (role_key, page_id) 
SELECT 'student', id FROM sys_pages WHERE parent_id = @student_viz_header OR id = @student_viz_header;

-- Teacher Access
INSERT IGNORE INTO role_access (role_key, page_id) 
SELECT 'teacher', id FROM sys_pages WHERE parent_id = @teacher_viz_header OR id = @teacher_viz_header;

-- Admin Access
INSERT IGNORE INTO role_access (role_key, page_id) 
SELECT 'admin', id FROM sys_pages WHERE parent_id = @admin_viz_header OR id = @admin_viz_header;

-- Ensure common page access for specific roles if not caught by parent logic
INSERT IGNORE INTO role_access (role_key, page_id)
SELECT 'student', id FROM sys_pages WHERE page_url = 'dashboards/common/academic_calendar.php';
INSERT IGNORE INTO role_access (role_key, page_id)
SELECT 'teacher', id FROM sys_pages WHERE page_url = 'dashboards/common/academic_calendar.php';
INSERT IGNORE INTO role_access (role_key, page_id)
SELECT 'admin', id FROM sys_pages WHERE page_url = 'dashboards/common/academic_calendar.php';
