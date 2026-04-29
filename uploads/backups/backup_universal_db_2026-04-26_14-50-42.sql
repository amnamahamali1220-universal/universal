-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: universal_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_events`
--

DROP TABLE IF EXISTS `academic_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `event_type` enum('Exam','Holiday','Academic Event') NOT NULL DEFAULT 'Academic Event',
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `academic_events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_events`
--

LOCK TABLES `academic_events` WRITE;
/*!40000 ALTER TABLE `academic_events` DISABLE KEYS */;
INSERT INTO `academic_events` VALUES (1,'Sport Week','Academic Event','2026-03-26 23:17:00','2026-03-30 23:17:00',1,'2026-03-19 17:17:39');
/*!40000 ALTER TABLE `academic_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,12,'Logged in','2026-04-18 14:44:24'),(2,12,'Logged in','2026-04-18 14:45:20'),(3,1,'Logged in','2026-04-20 12:42:30'),(4,1,'Logged in','2026-04-26 12:13:21'),(5,12,'Logged in','2026-04-26 12:15:14');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
INSERT INTO `assignments` VALUES (1,1,'Evolution and History of ERP System','1. Introduction to ERP System\r\n2. Role of ERP\r\n3. Benefit of ERP','2026-02-16 09:57:00','2026-02-16 03:58:10'),(2,3,'Data Structures & Algorithms Introduction and history','Study arrays, linked lists, stacks, queues and sorting algorithms used to build efficient programs.','2026-02-18 21:40:00','2026-02-16 15:41:05'),(3,2,'Web Development Fundamentals','Introduction to HTML, CSS and basic website structure. Students learn how web pages are created and styled.','2026-02-19 21:42:00','2026-02-16 15:42:29'),(4,2,'History','','2026-02-23 21:52:00','2026-02-16 15:52:49'),(5,7,'project management introduction','Project Management is the process of planning, organizing, and managing resources to successfully complete a project within a specific time, cost, and scope. It ensures that project goals are achieved efficiently through proper coordination and control.','2026-03-10 14:52:00','2026-03-08 08:52:34'),(6,7,'project management introduction','Project Management is the process of planning, organizing, and managing resources to successfully complete a project within a specific time, cost, and scope. It ensures that project goals are achieved efficiently through proper coordination and control.','2026-03-10 14:52:00','2026-03-08 08:57:56');
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,1,'2026-02-20','present'),(2,1,10,'2026-02-20','present'),(3,1,1,'2026-02-25','late'),(4,1,10,'2026-02-25','present'),(5,1,1,'2026-02-25','late'),(6,1,10,'2026-02-25','present'),(7,1,1,'2026-02-25','late'),(8,1,10,'2026-02-25','present'),(9,1,1,'2026-02-25','absent'),(10,1,10,'2026-02-25','present'),(11,1,1,'2026-03-08','present'),(12,1,10,'2026-03-08','absent'),(13,1,12,'2026-03-08','late'),(14,1,1,'2026-03-12','present'),(15,1,10,'2026-03-12','present'),(16,1,12,'2026-03-12','absent'),(17,1,13,'2026-03-12','late'),(18,1,1,'2026-04-18','present'),(19,1,10,'2026-04-18','absent'),(20,1,12,'2026-04-18','present'),(21,1,13,'2026-04-18','present');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_materials`
--

DROP TABLE IF EXISTS `course_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `type` enum('pdf','video','text','assignment') NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_materials`
--

LOCK TABLES `course_materials` WRITE;
/*!40000 ALTER TABLE `course_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `course_code` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,1,NULL,'Enterprise Resource Planning','ERP (Enterprise Resource Planning) is an integrated software system that helps organizations manage and automate core business processes like finance, HR, inventory, sales, and operations in one centralized platform.\r\nIt improves efficiency, data accuracy, and decision-making by connecting all departments through a single database.','2026-02-13 05:19:11'),(2,1,'CS101','Web Development Fundamentals','Introduction to HTML, CSS and basic website structure. Students learn how web pages are created and styled.','2026-02-16 15:34:46'),(3,1,'CS220','Data Structures & Algorithms','Study arrays, linked lists, stacks, queues and sorting algorithms used to build efficient programs.','2026-02-16 15:35:54'),(4,2,'CS221','Digital Marketing',NULL,'2026-02-16 16:50:37'),(5,1,'cs000','Decision support system','**Decision Support System (DSS)** is a computer-based information system that helps managers and organizations make better decisions by analyzing data and presenting useful information.\r\n\r\nIt supports problem-solving and decision-making in areas like planning, forecasting, and management.','2026-02-25 03:56:19'),(6,11,'cs227','Project management',NULL,'2026-03-08 08:46:49'),(7,1,'cs227','Project Management','Project Management is the process of planning, organizing, and managing resources to successfully complete a project within a specific time, cost, and scope. It ensures that project goals are achieved efficiently through proper coordination and control.','2026-03-08 08:49:46'),(8,11,'7654','Business Process Modelimg','','2026-03-11 18:16:50'),(9,1,'cs000','digital marketing','','2026-04-02 04:35:56');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (1,1,1,'2026-02-15 15:30:53'),(2,1,2,'2026-02-16 15:42:52'),(3,1,3,'2026-02-16 15:42:59'),(4,10,1,'2026-02-20 14:44:09'),(5,1,7,'2026-03-08 08:52:55'),(6,12,1,'2026-03-08 08:56:55'),(7,12,2,'2026-03-11 18:24:02'),(8,12,4,'2026-03-11 18:24:09'),(9,12,5,'2026-03-11 18:24:46'),(10,12,3,'2026-03-11 18:25:00'),(11,12,6,'2026-03-11 18:25:05'),(12,12,8,'2026-03-11 18:25:10'),(13,12,7,'2026-03-11 18:25:15'),(14,13,1,'2026-03-12 10:00:52'),(15,13,3,'2026-03-12 10:00:57'),(16,13,6,'2026-03-12 10:01:03'),(17,13,8,'2026-03-12 10:01:08'),(18,13,7,'2026-03-12 10:01:13'),(19,13,2,'2026-03-12 10:01:18'),(20,13,4,'2026-03-12 10:01:23'),(21,13,5,'2026-03-12 10:01:28'),(22,1,6,'2026-03-30 04:55:38');
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_views`
--

DROP TABLE IF EXISTS `material_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `material_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_mat` (`student_id`,`material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_views`
--

LOCK TABLES `material_views` WRITE;
/*!40000 ALTER TABLE `material_views` DISABLE KEYS */;
INSERT INTO `material_views` VALUES (1,12,2,'2026-04-18 14:52:10');
/*!40000 ALTER TABLE `material_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` enum('pdf','video','doc','link') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,1,'ERP','pdf','uploads/materials/1771213865_ERP FINAL PROJECT.pdf','2026-02-16 03:51:05'),(2,2,'Web Development Fundamentals','pdf','uploads/materials/1771256356_Web_Development_Fundamentals.pdf','2026-02-16 15:39:16'),(3,3,'Data Structures & Algorithms','pdf','uploads/materials/1771256398_Data_Structures_Algorithms.pdf','2026-02-16 15:39:58'),(4,5,'assignment project','pdf','uploads/materials/1771991821_DSS_Project_Assignment.pdf','2026-02-25 03:57:01'),(5,7,'Project Management','pdf','uploads/materials/1772959898_project_management_short.pdf','2026-03-08 08:51:38');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT '#',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,'What does ERP stand for?\r\n','Enterprise Resource Planning','Electronic Resource Planning','Enterprise Record Process','Electronic Record Program','A'),(2,1,'What does ERP stand for?\r\n','Enterprise Resource Planning','Electronic Resource Planning','Enterprise Record Process','Electronic Record Program','A');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_attempts`
--

DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `score` float DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_attempts`
--

LOCK TABLES `quiz_attempts` WRITE;
/*!40000 ALTER TABLE `quiz_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `quiz_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quizzes`
--

LOCK TABLES `quizzes` WRITE;
/*!40000 ALTER TABLE `quizzes` DISABLE KEYS */;
INSERT INTO `quizzes` VALUES (1,1,'final','2026-02-20 15:25:18');
/*!40000 ALTER TABLE `quizzes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_access`
--

DROP TABLE IF EXISTS `role_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_access` (
  `role_key` varchar(50) NOT NULL,
  `page_id` int(11) NOT NULL,
  PRIMARY KEY (`role_key`,`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_access`
--

LOCK TABLES `role_access` WRITE;
/*!40000 ALTER TABLE `role_access` DISABLE KEYS */;
INSERT INTO `role_access` VALUES ('admin',16),('admin',17),('admin',18),('admin',19),('admin',20),('admin',28),('admin',31),('admin',32),('admin',35),('admin',38),('assignment_manager',22),('course_instructor',21),('exam_controller',23),('senior_teacher',24),('student',2),('student',8),('student',10),('student',25),('student',26),('student',27),('student',28),('student',36),('student',39),('super_admin',1),('super_admin',2),('super_admin',3),('super_admin',4),('super_admin',5),('super_admin',35),('super_admin',37),('super_admin',38),('super_admin',39),('super_admin',40),('teacher',2),('teacher',6),('teacher',7),('teacher',9),('teacher',28),('teacher',29),('teacher',30),('teacher',40);
/*!40000 ALTER TABLE `role_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rubric_criteria`
--

DROP TABLE IF EXISTS `rubric_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rubric_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rubric_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `weight` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `rubric_id` (`rubric_id`),
  CONSTRAINT `rubric_criteria_ibfk_1` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rubric_criteria`
--

LOCK TABLES `rubric_criteria` WRITE;
/*!40000 ALTER TABLE `rubric_criteria` DISABLE KEYS */;
INSERT INTO `rubric_criteria` VALUES (1,1,'knowledge',1);
/*!40000 ALTER TABLE `rubric_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rubric_levels`
--

DROP TABLE IF EXISTS `rubric_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rubric_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `criteria_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `criteria_id` (`criteria_id`),
  CONSTRAINT `rubric_levels_ibfk_1` FOREIGN KEY (`criteria_id`) REFERENCES `rubric_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rubric_levels`
--

LOCK TABLES `rubric_levels` WRITE;
/*!40000 ALTER TABLE `rubric_levels` DISABLE KEYS */;
INSERT INTO `rubric_levels` VALUES (1,1,1,'Excellent');
/*!40000 ALTER TABLE `rubric_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rubrics`
--

DROP TABLE IF EXISTS `rubrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rubrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assignment_id` (`assignment_id`),
  CONSTRAINT `rubrics_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rubrics`
--

LOCK TABLES `rubrics` WRITE;
/*!40000 ALTER TABLE `rubrics` DISABLE KEYS */;
INSERT INTO `rubrics` VALUES (1,1,'Grading Rubric','2026-04-26 12:44:44');
/*!40000 ALTER TABLE `rubrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_enrollments`
--

DROP TABLE IF EXISTS `student_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `student_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_enrollments`
--

LOCK TABLES `student_enrollments` WRITE;
/*!40000 ALTER TABLE `student_enrollments` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_teacher_assignments`
--

DROP TABLE IF EXISTS `student_teacher_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_teacher_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_teacher_assignments`
--

LOCK TABLES `student_teacher_assignments` WRITE;
/*!40000 ALTER TABLE `student_teacher_assignments` DISABLE KEYS */;
INSERT INTO `student_teacher_assignments` VALUES (1,11,10,'2026-03-11 18:18:30'),(6,18,12,'2026-03-12 09:41:40'),(7,17,13,'2026-03-12 09:41:52');
/*!40000 ALTER TABLE `student_teacher_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grade` float DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
INSERT INTO `submissions` VALUES (1,1,12,'uploads/submissions/1776523765_12_ERP_Assignment.pdf','2026-04-18 14:49:25',10,'Excellent\r\n'),(2,5,12,'uploads/submissions/1773253559_12_project_management_short.pdf','2026-03-11 18:25:59',9,'good work'),(3,3,12,'uploads/submissions/1776523882_12_Web_Development_Fundamentals.pdf','2026-04-18 14:51:22',8,''),(4,4,12,'uploads/submissions/1776523905_12_Data_Structures_Algorithms.pdf','2026-04-18 14:51:45',10,''),(5,2,12,'uploads/submissions/1776524031_12_Data_Structures_Algorithms.pdf','2026-04-18 14:53:51',10,''),(6,6,12,'uploads/submissions/1776524350_12_Chapter presentation 3 (1).pptx','2026-04-18 14:59:10',8,''),(7,1,1,'uploads/submissions/1776524474_1_ERP Project Proposal.pdf','2026-04-18 15:01:14',9,''),(8,5,1,'uploads/submissions/1776690364_1_Project Management Book.pdf','2026-04-20 13:06:04',NULL,NULL),(9,6,1,'uploads/submissions/1776690357_1_project_management_short.pdf','2026-04-20 13:05:57',NULL,NULL),(10,2,1,'uploads/submissions/1776690732_1_Symetric and Asymetric.pdf','2026-04-20 13:12:12',NULL,NULL);
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_pages`
--

DROP TABLE IF EXISTS `sys_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT 0,
  `page_name` varchar(100) NOT NULL,
  `page_url` varchar(255) DEFAULT '#',
  `icon_class` varchar(50) DEFAULT 'bi bi-circle',
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_pages`
--

LOCK TABLES `sys_pages` WRITE;
/*!40000 ALTER TABLE `sys_pages` DISABLE KEYS */;
INSERT INTO `sys_pages` VALUES (1,0,'Dashboard','index.php','bi bi-speedometer2',1),(2,0,'System Management','#','bi bi-gear-fill',2),(3,2,'Manage Users','dashboards/super_admin/manage_users.php','bi bi-people',1),(4,2,'Manage Roles','dashboards/super_admin/manage_roles.php','bi bi-shield-lock',2),(5,2,'Manage Pages','dashboards/super_admin/manage_pages.php','bi bi-file-earmark-text',3),(6,9,'My Courses','dashboards/teacher/my_courses.php','bi bi-book',0),(7,9,'Create Course','dashboards/teacher/create_course.php','bi bi-plus-circle',0),(8,10,'My Learning','dashboards/student/my_learning.php','bi bi-journal-bookmark',0),(9,0,'Teacher Dashboard','#','bi bi-person-workspace',4),(10,0,'Student Area','#','bi bi-mortarboard',9),(16,0,'Admin Dashboard','#','bi bi-speedometer2',3),(17,16,'Dashboard Home','dashboards/admin/index.php','bi bi-house',0),(18,16,'Manage Teachers','dashboards/admin/list_teachers.php','bi bi-people',0),(19,16,'Manage Students','dashboards/admin/students.php','bi bi-mortarboard',0),(20,16,'Manage Courses','dashboards/admin/courses.php','bi bi-book',0),(21,0,'Instructor Dashboard','dashboards/teacher/instructor_dash.php','bi bi-person-video3',5),(22,0,'Assignment Dashboard','dashboards/teacher/assignment_dash.php','bi bi-file-earmark-text',6),(23,0,'Exam Dashboard','dashboards/teacher/exam_dash.php','bi bi-journal-check',7),(24,0,'Senior Teacher Dashboard','dashboards/teacher/senior_dash.php','bi bi-shield-check',8),(25,0,'Visual Analytics','#','bi bi-pie-chart-fill',10),(26,25,'My Visual Stats','dashboards/student/visual_stats.php','bi bi-graph-up',10),(27,25,'Attendance Analytics','dashboards/student/attendance_stats.php','bi bi-calendar-check',10),(28,25,'Academic Calendar','dashboards/common/academic_calendar.php','bi bi-calendar3',10),(29,0,'Teacher Analytics','#','bi bi-bar-chart-line-fill',11),(30,29,'Course Performance','dashboards/teacher/visual_stats.php','bi bi-activity',10),(31,0,'Global Reports','#','bi bi-clipboard-data-fill',12),(32,31,'Grade Distribution','dashboards/admin/grade_distribution.php','bi bi-briefcase-fill',10),(33,2,'Assign Students','dashboards/admin/assign_student_teacher.php','bi bi-person-gear',20),(34,9,'My Students','dashboards/teacher/my_students.php','bi bi-people',5),(35,0,'Activity Logs','dashboards/admin/activity_logs.php','bi bi-journal-text',99),(36,0,'Report Card','dashboards/student/report_card.php','bi bi-file-earmark-pdf',99),(37,0,'Global Settings','dashboards/super_admin/global_settings.php','fas fa-cogs',0),(38,0,'System Backups','dashboards/admin/system_backups.php','fas fa-database',0),(39,0,'Submit Assignment','dashboards/student/submit_assignment.php','fas fa-upload',0),(40,0,'Rubric Builder','dashboards/teacher/rubric_builder.php','fas fa-table',0);
/*!40000 ALTER TABLE `sys_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_roles`
--

DROP TABLE IF EXISTS `sys_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `role_key` varchar(50) NOT NULL,
  `is_system_role` tinyint(1) DEFAULT 0 COMMENT '1=Cannot Delete',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_key` (`role_key`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_roles`
--

LOCK TABLES `sys_roles` WRITE;
/*!40000 ALTER TABLE `sys_roles` DISABLE KEYS */;
INSERT INTO `sys_roles` VALUES (1,'Super Admin','super_admin',1),(2,'Administrator','admin',0),(3,'Student','student',0),(4,'Suspended','suspended',1),(7,'Teacher','teacher',0),(11,'Course Instructor','course_instructor',0),(12,'Assignment Manager','assignment_manager',0),(13,'Exam Controller','exam_controller',0),(14,'Senior Teacher','senior_teacher',0);
/*!40000 ALTER TABLE `sys_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES ('footer_text','© 2026 Universal Systems. All rights reserved.'),('system_logo','https://cdn-icons-png.flaticon.com/512/906/906343.png'),('system_name','Universal ERP');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_student_assign`
--

DROP TABLE IF EXISTS `teacher_student_assign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_student_assign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assignment` (`teacher_id`,`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_student_assign`
--

LOCK TABLES `teacher_student_assign` WRITE;
/*!40000 ALTER TABLE `teacher_student_assign` DISABLE KEYS */;
INSERT INTO `teacher_student_assign` VALUES (1,11,16,'2026-03-12 18:15:03'),(2,17,12,'2026-04-18 14:27:36'),(3,18,13,'2026-04-18 14:27:45'),(5,17,10,'2026-04-18 14:28:00'),(6,9,14,'2026-04-18 14:29:39');
/*!40000 ALTER TABLE `teacher_student_assign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `teacher_role` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (1,2,'course_instructor','Computer Science','2026-02-20 15:10:52'),(2,5,'assignment_manager','Computer Science','2026-02-20 15:10:52'),(3,9,'exam_controller','Computer Science','2026-02-20 15:10:52');
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `identity_no` varchar(50) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_identity` (`identity_no`),
  UNIQUE KEY `idx_reg_no` (`registration_no`),
  KEY `role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Root Admin','admin@sys.com','$2y$10$YtdN9LmiJ9M5Ewy1HYdWU.zffCQycmbzrekq6mjeTxJe9OKp0lUhm','super_admin','12345-1234567-1','ADM-001',1,'2026-02-19 04:45:17'),(2,'Demo Teacher','teacher@demo.com','$2y$10$tIBhqhXizEcl5PmwH2HX5.aYm.d60Gu/D3euvwTSzeddGHXPSW7Kq','teacher',NULL,NULL,1,'2026-02-19 04:45:17'),(3,'Demo Student','student@demo.com','$2y$10$dzb3lvep5mYxEJg99remIO7s24ikgv5HNowSfc40wiF3YRiGXjaB6','student',NULL,NULL,1,'2026-02-19 04:45:17'),(4,'Super Administrator','admin@universal.com','$2y$10$wUz26x/0.LaHxJELipS7S.K14iYNA1JoPeYS0fM6MlE5ksQAK7kzK','super_admin',NULL,NULL,1,'2026-02-19 04:45:17'),(5,'John Teacher','teacher@universal.com','$2y$10$VIDvJ8QqlzP3TyVDA7lHSepLAIZ4mMITAkPlh.oimiklyGoxa17V2','teacher',NULL,NULL,1,'2026-02-19 04:45:17'),(6,'Jane Student','student@universal.com','$2y$10$9bsN0iL4P.C.CxVQ4rHFO.7T.gVKcTXEuG/yiZvUP1NH6aOBRygyG','student',NULL,NULL,1,'2026-02-19 04:45:17'),(7,'Admin User','admin@example.com','$2y$10$ZE0AjSJHERUnaVAwr5AQSuWoyTYxak6VfLRECBWlGSe6wKiEU0If.','admin','123456',NULL,1,'2026-02-19 04:45:17'),(9,'sir umair','root@gmail.com','$2y$10$UwymexI4/UhHMkJ4RUd8o.VBwzD/Uo4R/WqyYvEBNM7FOzml/Ry/S','teacher','3534578976532',NULL,1,'2026-02-19 04:52:16'),(10,'laiba','laiba@gmail.com','$2y$10$18rjTMRI5XPxbKHA0fes9OOM0scp/CIOZKReU3Z1k7ikht3BZI9oS','student','3509876645287','8349',1,'2026-02-19 04:54:44'),(11,'Dr. Atif','atif@24gmail.com','$2y$10$BOb/S6LHK5.9Ouqx22zkLeDCTWyY/UTyzT2CeD311YvooGer8txJ2','teacher','345678890098',NULL,1,'2026-03-08 08:44:18'),(12,'Amna','maham@12gmail.com','$2y$10$g.zuxf5YF8d9jO7ZcOP1YuFmha6xqZ6IvwWPsZ4J43aDy.6iVQRnq','student','32456789098','5678',1,'2026-03-08 08:54:19'),(13,'Sarah','sarah@45gmail.com','$2y$10$/6HZj6y5N5txZgRjmmrh2eWLoNH60hnLR9b3zlwVTtHyBWTSi/VbO','student','3456690875432','6789',1,'2026-03-11 17:50:54'),(14,'Rehan','rehan@gmail.com','$2y$10$Mogu4UYy7UHLZVcWceQN5OZ5Ca.SjQkp1DpohT32D4SvnaDqH0d1O','student','34567789909877','8765',1,'2026-03-11 17:52:08'),(16,'Tuba','tuba@gamil.com','$2y$10$/ynXN34.sql8/xOUEmkVEu5KBTGJteO8tasMg64RQOsFri31iw5ZS','student','345678900985','9654',1,'2026-03-11 17:54:48'),(17,'Ammara','ammara@gamil.com','$2y$10$8U/1NELtfSKH2tDuJ8JIhuYE/Pcr8S6TH7pkPmjs7LAsBqzABvDdK','teacher','345679988763',NULL,1,'2026-03-11 17:56:50'),(18,'Dr. Saira','saira@gamil.com','$2y$10$iNeV5GqI7r3v3hLd8MAKqOo1x0216psbi5UZQv2TwT3lEfcJzJ/qe','senior_teacher','35667890-234','',1,'2026-03-11 17:58:17');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-26 18:50:42
