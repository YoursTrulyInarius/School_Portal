-- Database Creation
CREATE DATABASE IF NOT EXISTS school_portal;
USE school_portal;

-- 1. Users Table (Core Authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    email VARCHAR(100),
    reset_token_hash VARCHAR(64) NULL,
    reset_token_expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Teachers Table (Linked to Users)
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20),
    profile_image VARCHAR(255) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Courses/Subjects Table (College)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT
);

-- 3.1 Strands Table (Senior High)
CREATE TABLE IF NOT EXISTS strands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    strand_name VARCHAR(100) NOT NULL,
    strand_code VARCHAR(20) UNIQUE NOT NULL,
    description VARCHAR(255) DEFAULT 'Senior High School Strand'
);

-- 4. Sections Table (Academic Structure)
CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(50) NOT NULL,
    grade_level VARCHAR(20) NOT NULL, -- e.g., 'Grade 7', 'Grade 12', '1st Year', '2nd Year'
    year_level VARCHAR(20) NULL, -- e.g., '1', '2', '3', '4', '11', '12'
    block ENUM('A', 'B', 'C', 'D', 'E') NULL, -- Block assignment
    course_id INT NULL, -- Link to College Course
    strand_id INT NULL, -- Link to SHS Strand
    adviser_id INT,
    FOREIGN KEY (adviser_id) REFERENCES teachers(id) ON DELETE SET NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (strand_id) REFERENCES strands(id) ON DELETE SET NULL
);

-- 5. Students Table (Linked to Users and Sections)
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lrn VARCHAR(20) UNIQUE NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20),
    section_id INT,
    is_scholar BOOLEAN DEFAULT 0,
    enrollment_details VARCHAR(255) DEFAULT '',
    profile_image VARCHAR(255) NULL,
    total_fee DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
);

-- 6. Class Schedules (Linking Sections, Courses, and Teachers)
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT NOT NULL,
    teacher_id INT NOT NULL,
    course_id INT NULL,
    strand_id INT NULL,
    subject VARCHAR(100) NOT NULL,
    day VARCHAR(20) NOT NULL, -- e.g., 'Monday', 'MWF'
    time VARCHAR(50) NOT NULL, -- e.g., '9:00 AM - 10:30 AM'
    room VARCHAR(50) NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (strand_id) REFERENCES strands(id) ON DELETE CASCADE
);

-- 7. Grades Table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    schedule_id INT NULL,
    teacher_id INT NULL,
    grade DECIMAL(3,2) NULL,
    term VARCHAR(20) NULL,
    date_recorded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

-- 8. Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- 9. Announcements Table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- The author (usually admin)
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    target_audience ENUM('all', 'teacher', 'student') DEFAULT 'all',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 10. Enrollment Requests Table
CREATE TABLE IF NOT EXISTS enrollment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address TEXT,
    grade_level VARCHAR(100) NOT NULL,
    course_id INT NULL,
    strand_id INT NULL,
    year_level INT NULL,
    block VARCHAR(5) NULL,
    previous_school VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT NULL,
    notes TEXT,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (strand_id) REFERENCES strands(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 11. Payment Transactions Table
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_type VARCHAR(50) NOT NULL, -- e.g., 'Tuition', 'Miscellaneous', 'Books'
    payment_method VARCHAR(50) NOT NULL, -- e.g., 'Cash', 'Bank Transfer', 'Online'
    reference_number VARCHAR(100) NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    notes TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 12. Seed Data

-- 12.1 Default Users
-- Admin: admin / admin123

INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$IKfc069Ajf6kgzEV2v3XbOslfLJtcXfRywovSyD2.sEFdbWhfiIKa', 'admin')
ON DUPLICATE KEY UPDATE username=username;
