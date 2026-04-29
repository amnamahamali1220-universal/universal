-- Database Extension for Teacher RBAC

-- 1. Create a table to store additional teacher information
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    teacher_role VARCHAR(50) NOT NULL, -- e.g., 'course_instructor', 'assignment_manager', etc.
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 2. Add new roles to sys_roles table
INSERT IGNORE INTO sys_roles (role_key, role_name, is_system_role) VALUES 
('course_instructor', 'Course Instructor', 0),
('assignment_manager', 'Assignment Manager', 0),
('exam_controller', 'Exam Controller', 0),
('senior_teacher', 'Senior Teacher', 0);

-- 3. Define initial pages for each role (placeholders for now)
INSERT IGNORE INTO sys_pages (page_name, page_url, icon_class, sort_order, parent_id) VALUES 
('Instructor Dashboard', 'dashboards/teacher/instructor_dash.php', 'bi bi-person-video3', 1, 0),
('Assignment Dashboard', 'dashboards/teacher/assignment_dash.php', 'bi bi-file-earmark-text', 1, 0),
('Exam Dashboard', 'dashboards/teacher/exam_dash.php', 'bi bi-journal-check', 1, 0),
('Senior Teacher Dashboard', 'dashboards/teacher/senior_dash.php', 'bi bi-shield-check', 1, 0);

-- 4. Map pages to roles
-- Instructor Dashboard to course_instructor
INSERT IGNORE INTO role_access (role_key, page_id) 
SELECT 'course_instructor', id FROM sys_pages WHERE page_url = 'dashboards/teacher/instructor_dash.php';

-- Assignment Dashboard to assignment_manager
INSERT IGNORE INTO role_access (role_key, page_id) 
SELECT 'assignment_manager', id FROM sys_pages WHERE page_url = 'dashboards/teacher/assignment_dash.php';

-- Exam Dashboard to exam_controller
INSERT IGNORE INTO role_access (role_key, page_id) 
SELECT 'exam_controller', id FROM sys_pages WHERE page_url = 'dashboards/teacher/exam_dash.php';

-- Senior Dashboard to senior_teacher
INSERT IGNORE INTO role_access (role_key, page_id) 
SELECT 'senior_teacher', id FROM sys_pages WHERE page_url = 'dashboards/teacher/senior_dash.php';

-- All roles should typically have access to their profile and common teacher tools if needed.
-- For now, we follow the strict requirement of "separate dashboard per role".
