# School Portal Management System

A comprehensive web-based school management system built with PHP and MySQL. This system facilitates efficient interaction between students, teachers, and administrators.

> [!NOTE]
> **Project Status**: 🚧 This project is currently **Ongoing** and under active development.

## Key Recent Enhancements
- **Professional Schedule Redesign**: Teacher schedules now match a premium "paper form" aesthetic, unified into a single weekly view.
- **Hierarchical Grades System**: Admins can now drill-down from Programs to Sections and Students to view detailed transcripts.
- **Grade Auto-Conversion**: Implement modern grading logic that automatically calculates point-scale grades from percentage inputs.

## Features

### 🔑 Authentication & Security
- **Secure Login & Registration**: Role-based access control (Admin, Teacher, Student).
- **Forgot Password**: OTP-based password reset via email.
- **Admin Setup**: Script to quickly initialize or reset admin access.

### 🛠 Administrative Module
- **User Management**: Manage Students, Teachers, and Admin accounts.
- **Hierarchical Academic Records**: View student grades through a professional multi-level interface.
- **Schedules**: Manage class schedules and subjects by program and section.
- **Announcements**: Post and manage school-wide announcements.
- **Payments**: Track and manage student payments and transactions.

### 👨‍🏫 Teacher Module
- **Unified Weekly Schedule**: Access a professional, grouped view of all weekly classes.
- **Smart Grading**: Input grades with automatic percentage-to-point conversion.
- **Class Implementation**: Manage specific subjects and sections efficiently.
- **Student Data**: View student details and profiles.

### 👨‍🎓 Student Module
- **Dashboard**: View announcements and personal performance stats.
- **Grades Transcript**: Check verified academic performance and teacher feedback.
- **Subject Loads**: View professional class timetables.
- **Attendance**: Track attendance records across subjects.

## 💻 Tech Stack
- **Frontend**: HTML5, CSS3, JavaScript.
- **Backend**: Native PHP.
- **Database**: MySQL (MariaDB).
- **Server**: Apache (via XAMPP).

## 🚀 Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/yourusername/School_Portal.git
    ```
    Move the folder to your web server's root directory (e.g., `C:\xampp\htdocs\School_Portal`).

2.  **Database Configuration**
    - Open **phpMyAdmin**.
    - Create a new database named `westprime_portal`.
    - Import the `database.sql` file included in the root directory.

3.  **Configure Connection**
    - Check `config.php` to ensure database credentials match your local setup:
      ```php
      define('DB_SERVER', 'localhost');
      define('DB_USERNAME', 'root');
      define('DB_PASSWORD', '');
      define('DB_NAME', 'westprime_portal');
      ```

4.  **Initialize Admin Account**
    - Open your browser and navigate to:
      `http://localhost/School_Portal/setup_admin.php`
    - This will create a default admin account.
    - **Default Credentials**:
        - **Username**: `admin`
        - **Password**: `admin123`

5.  **Access the Application**
    - Go to `http://localhost/School_Portal/` to log in.

## 📧 Email Configuration
For the "Forgot Password" OTP functionality to work, ensure your mail server settings (SMTP) are correctly configured in `forgot_password.php` (uses PHPMailer).

## 📂 Project Structure
- `/admin` - Admin dashboard files.
- `/student` - Student dashboard files.
- `/teacher` - Teacher dashboard files.
- `/includes` - Shared components (headers, sidebars, etc.).
- `/uploads` - User uploaded content (profile pics, etc.).
- `/vendor` - Third-party libraries (PHPMailer, etc.).

---
*Created for the School Portal Project.*
