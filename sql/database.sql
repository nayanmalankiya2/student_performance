CREATE DATABASE IF NOT EXISTS student_performance;
USE student_performance;

CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'faculty', 'student') DEFAULT 'faculty',
    student_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    enrollment_no VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    date_of_birth DATE NOT NULL,
    address TEXT,
    semester INT(2) NOT NULL,
    course VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    created_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subjects (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_name VARCHAR(100) NOT NULL,
    semester INT(2) NOT NULL,
    max_marks INT(11) DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS marks (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) NOT NULL,
    subject_id INT(11) NOT NULL,
    semester INT(2) NOT NULL,
    internal_marks DECIMAL(5,2) DEFAULT 0.00,
    external_marks DECIMAL(5,2) DEFAULT 0.00,
    total_marks DECIMAL(5,2) DEFAULT 0.00,
    grade VARCHAR(2),
    exam_year YEAR NOT NULL,
    created_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

