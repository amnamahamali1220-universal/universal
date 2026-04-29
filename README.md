# Universal Student Content Management System

## Project Overview
This project is a role-based Educational Content Management System (CMS) featuring dynamic user control and a dynamic sidebar based on the user's role and access permissions. The system supports multiple user roles, including Students, Teachers, Admins, and Super Admins, each with their own dedicated dashboards and functionalities.

## Existing Modules and Pages Description

### Admin Dashboard (`dashboards/admin/`)
- `activity_logs.php`: Displays system-wide user activity and logs.
- `add_teacher.php`: Allows administrators to register new teachers into the system.
- `assign_student_teacher.php`: Interface to link students with their respective teachers.
- `assign_teacher.php`: Handles assigning teachers to specific courses or departments.
- `calendar_action.php`: Backend processor for calendar event additions and modifications.
- `courses.php`: Manages all courses available in the system.
- `delete_user.php`: Handles the deletion of users from the database.
- `edit_user.php`: Provides a form to modify user profile information.
- `grade_distribution.php`: Shows analytical data and charts for grade distributions.
- `index.php`: The main landing dashboard for administrators.
- `list_teachers.php`: Displays a list of all active teachers.
- `students.php`: Manages student records and profiles.
- `teachers.php`: Comprehensive overview of teacher management.

### Student Dashboard (`dashboards/student/`)
- `action_view_material.php`: Handles the logic and view for course materials.
- `attendance_stats.php`: Shows the student's attendance records visually.
- `browse_courses.php`: Allows students to explore and enroll in new courses.
- `course_view.php`: Displays details and content of a specific course.
- `grades.php`: Shows the student's grades for assignments and quizzes.
- `index.php`: The main landing dashboard for students.
- `my_learning.php`: Displays the courses the student is currently enrolled in.
- `report_card.php`: Comprehensive report card showing overall performance.
- `take_quiz.php`: Interface for students to attempt quizzes.
- `visual_stats.php`: Displays visual charts of the student's academic progress.

### Teacher Dashboard (`dashboards/teacher/`)
- `assignment_dash.php`: Central hub for managing all assignments.
- `attendance.php`: Interface for teachers to mark student attendance.
- `course_view.php`: Displays course details from the instructor's perspective.
- `create_assignment.php`: Form to create and publish new assignments.
- `create_course.php`: Interface to add new courses to the curriculum.
- `create_quiz.php`: Tool to build quizzes with multiple-choice questions.
- `edit_assignment.php`: Modifies existing assignments.
- `edit_quiz.php`: Modifies existing quizzes.
- `enrolled_students.php`: Lists all students enrolled in a teacher's course.
- `exam_dash.php`: Dashboard specifically for managing exams.
- `exam_schedule.php`: Tool to schedule upcoming exams.
- `grade_assignment.php`: Interface to evaluate and grade student submissions.
- `index.php`: The main landing dashboard for teachers.
- `instructor_dash.php`: Alternative dashboard view for instructors.
- `manage_course.php`: Handles settings and content for existing courses.
- `monitor_activities.php`: Allows teachers to monitor student activities in real-time.
- `my_courses.php`: Lists the courses taught by the current teacher.
- `my_students.php`: Lists all students assigned to the teacher.
- `publish_results.php`: Publishes final grades or results for courses/exams.
- `quizzes.php`: Lists all quizzes created by the teacher.
- `reports.php`: Generates academic reports for classes.
- `senior_dash.php`: Dashboard for senior teachers or department heads.
- `student_performance.php`: Analytics on how students are performing.
- `upload_material.php`: Allows teachers to upload documents and resources.
- `view_submissions.php`: Displays assignments submitted by students.
- `visual_stats.php`: Analytical charts for teacher-specific statistics.

### Super Admin Dashboard (`dashboards/super_admin/`)
- `manage_pages.php`: Dynamic page registry and permission mapping.
- `manage_roles.php`: Creates and edits role definitions (Admin, Teacher, Student).
- `manage_users.php`: Comprehensive user management with role assignment.

### Other Important Pages
- `dashboards/common/academic_calendar.php`: System-wide academic calendar showing events.
- `dashboards/communication/messages.php`: In-system messaging platform.

## Missing Features from Existing Dashboards

1. **Student Dashboard Missing Features:**
   - **Assignment Submission Interface:** While `take_quiz.php` exists, there is no direct `submit_assignment.php` page visible for file uploads or textual submissions for regular assignments (this may be handled within `course_view.php`, but lacks a dedicated module).
   - **Peer Communication:** A dedicated forum or discussion board for students to interact with peers within a course context.

2. **Teacher Dashboard Missing Features:**
   - **Rubric Builder:** No apparent tool for creating and attaching grading rubrics to assignments.
   - **Automated Attendance Tracking:** Missing integration for automated attendance tracking (e.g., via QR code or login tracking, though manual marking exists).

3. **Admin Dashboard Missing Features:**
   - **Financial/Fee Management:** There are no pages related to student fees, invoices, or payments.
   - **System Backups:** No interface for the admin to take database backups directly from the UI.
   - **Helpdesk/Support Tickets:** No system for students or teachers to raise support tickets to the admins.

4. **Super Admin Dashboard Missing Features:**
   - **Global Settings Configuration:** Missing a page to configure site-wide variables like site name, logo, SMTP settings for emails, etc.

## Recommendations for Enhancements and New Features
To evolve this project into a full-fledged, enterprise-grade Educational CMS, consider adding the following modules:

1. **Payment Gateway Integration (Fee Management):** Add a complete fee structure module, automated invoicing, and online payment gateways (Stripe, PayPal) for tuition and course fees.
2. **Live Virtual Classrooms:** Integrate with Zoom API or Google Meet to schedule and launch live video classes directly from the teacher dashboard.
3. **Discussion Forums & Q&A:** Create course-specific discussion boards where students can ask questions and teachers or peers can answer, fostering community learning.
4. **Advanced Reporting & AI Analytics:** Use predictive analytics to flag "at-risk" students who may fail based on their current trajectory, and suggest interventions.
5. **Mobile Application / PWA:** Enhance the existing web app to be a Progressive Web App (PWA) with push notifications, or build a companion mobile app.
6. **Parent Portal:** A new user role and dashboard for parents to log in and monitor their child's attendance, grades, and teacher communications.
7. **Certificate Generation:** Automatically generate PDF certificates upon course completion.
8. **Plagiarism Detection:** Integrate with APIs like Turnitin for automatic checking of student submissions.
9. **Multi-language Support:** Add i18n support so the system can be used by non-English speakers.
