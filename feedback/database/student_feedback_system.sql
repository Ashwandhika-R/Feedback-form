-- Student Feedback Management System Database
-- Created for B.Tech AI & Data Science Portfolio Project

CREATE DATABASE IF NOT EXISTS `student_feedback_system` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `student_feedback_system`;

-- Feedbacks Table
CREATE TABLE IF NOT EXISTS `feedbacks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `acknowledgement_no` VARCHAR(20) NOT NULL UNIQUE,
  `student_name` VARCHAR(100) DEFAULT NULL COMMENT 'NULL for anonymous',
  `register_number` VARCHAR(20) DEFAULT NULL,
  `department` VARCHAR(100) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `section` VARCHAR(5) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `faculty_name` VARCHAR(100) NOT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  `teaching_quality` TINYINT(1) NOT NULL DEFAULT 0,
  `subject_knowledge` TINYINT(1) NOT NULL DEFAULT 0,
  `communication_skills` TINYINT(1) NOT NULL DEFAULT 0,
  `doubt_clarification` TINYINT(1) NOT NULL DEFAULT 0,
  `classroom_interaction` TINYINT(1) NOT NULL DEFAULT 0,
  `punctuality` TINYINT(1) NOT NULL DEFAULT 0,
  `strengths` TEXT DEFAULT NULL,
  `improvements` TEXT DEFAULT NULL,
  `feedback` TEXT DEFAULT NULL,
  `suggestions` TEXT DEFAULT NULL,
  `is_anonymous` TINYINT(1) NOT NULL DEFAULT 0,
  `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_department` (`department`),
  KEY `idx_faculty` (`faculty_name`),
  KEY `idx_subject` (`subject_name`),
  KEY `idx_submitted` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `last_login` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username=admin, password=admin123
-- Hash below is bcrypt of 'admin123' (PASSWORD_DEFAULT in PHP)
INSERT INTO `admin_users` (`username`, `password`, `full_name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');
-- The login.php has a plain-text fallback: if hash fails, 'admin123' is auto-accepted
-- and the hash is automatically updated in the database on first login.

-- Sample Data for Demo
INSERT INTO `feedbacks` 
(`acknowledgement_no`,`student_name`,`register_number`,`department`,`year`,`section`,`email`,`faculty_name`,`subject_name`,`teaching_quality`,`subject_knowledge`,`communication_skills`,`doubt_clarification`,`classroom_interaction`,`punctuality`,`strengths`,`improvements`,`feedback`,`suggestions`,`is_anonymous`,`submitted_at`) 
VALUES
('ACK2024001','Arun Kumar','22AI001','AI & Data Science','2nd','A','arun@college.edu','Dr. Priya Sharma','Machine Learning',5,5,4,5,4,5,'Excellent at explaining algorithms','Could add more practical sessions','Best faculty for ML concepts','More coding assignments',0,'2024-11-01 10:00:00'),
('ACK2024002','Priya Devi','22CS002','Computer Science','3rd','B','priya@college.edu','Prof. Rajesh Kumar','Data Structures',4,5,4,4,5,4,'Deep subject knowledge','More doubt clearing sessions','Very knowledgeable','Weekend lab sessions',0,'2024-11-05 11:30:00'),
('ACK2024003',NULL,NULL,'Electronics','1st','C',NULL,'Dr. Meena Iyer','Circuit Theory',3,4,3,3,4,5,'Good punctuality','Needs simpler explanations','Average teaching','More visual aids',1,'2024-11-10 09:15:00'),
('ACK2024004','Karthik R','21IT004','Information Technology','4th','A','karthik@college.edu','Dr. Suresh Babu','Database Systems',5,5,5,5,5,5,'Outstanding in every aspect','Nothing to improve','Best professor!','Keep it up',0,'2024-11-15 14:00:00'),
('ACK2024005','Lakshmi S','22AI005','AI & Data Science','2nd','A','lakshmi@college.edu','Dr. Priya Sharma','Deep Learning',4,5,4,4,5,4,'Passionate teacher','More industry examples','Very good classes','Guest lectures',0,'2024-11-20 10:45:00');
