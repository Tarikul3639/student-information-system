-- /home/tarikul3639/Desktop/student-information-system/database/sis.sql
-- Student Information System Database
-- Cyber Security Lab Assignment

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `sis_db`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `sis_db`;

-- Drop tables in dependency order
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `departments`;

-- Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Departments Table
CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_name` VARCHAR(100) NOT NULL,
    `department_code` VARCHAR(20) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Students Table
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `department_id` INT NULL,
    `gender` ENUM('Male','Female','Other') NOT NULL,
    `dob` DATE,
    `email` VARCHAR(100),
    `phone` VARCHAR(20),
    `address` TEXT,
    `photo` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_students_department`
        FOREIGN KEY (`department_id`)
        REFERENCES `departments`(`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Departments
INSERT INTO `departments`
(`department_name`, `department_code`, `description`)
VALUES
('Computer Science and Engineering', 'CSE',
 'Department of Computer Science and Engineering - Focuses on programming, algorithms, and software development'),

('Electrical and Electronic Engineering', 'EEE',
 'Department of Electrical and Electronic Engineering - Focuses on electrical systems and electronics'),

('Business Administration', 'BBA',
 'Department of Business Administration - Focuses on management and business studies'),

('Civil Engineering', 'CE',
 'Department of Civil Engineering - Focuses on infrastructure and construction'),

('Mechanical Engineering', 'ME',
 'Department of Mechanical Engineering - Focuses on mechanical systems and design');

-- Default Admin User
INSERT INTO `users`
(`full_name`, `username`, `email`, `password`)
VALUES
(
    'Administrator',
    'admin',
    'admin@sis.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);

-- Sample Students
INSERT INTO `students`
(`student_id`, `full_name`, `department_id`, `gender`, `dob`, `email`, `phone`, `address`)
VALUES
('SIS-2024-001', 'John Smith', 1, 'Male', '2000-05-15', 'john@example.com', '01712345678', '123 Main St, Dhaka'),
('SIS-2024-002', 'Sarah Johnson', 2, 'Female', '2001-03-22', 'sarah@example.com', '01898765432', '456 Oak Ave, Chittagong'),
('SIS-2024-003', 'Michael Rahman', 1, 'Male', '2000-11-08', 'michael@example.com', '01556789012', '789 Pine Rd, Sylhet'),
('SIS-2024-004', 'Emily Chen', 3, 'Female', '2001-07-30', 'emily@example.com', '01612345678', '321 Elm St, Rajshahi'),
('SIS-2024-005', 'David Wilson', 4, 'Male', '2000-01-12', 'david@example.com', '01787654321', '654 Maple Ave, Khulna');