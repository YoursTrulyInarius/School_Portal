# Westprime Horizon School Portal

Westprime Horizon School Portal is a comprehensive, web-based management system designed to streamline academic operations for administrators, teachers, and students.

> [!NOTE]
> **Project Status**: This system is currently **ongoing** and under active development.
> **On going**: Need to update/change the "Schoolarship" function, need to update the responsiveness.

---
> **SOME PARTS ARE MISSING BECAUSE THIS IS A SCHOOL PROJECT ONLY, SOME OF MY CLASSMATES TOOK SOME PARTS SUCH AS 'ATTENDANCE', 'LIBRARY', 'VOTING' and other more.**


---

## 🚀 Features

### For Administrators
- **User Management**: Create and manage accounts for teachers and students.
- **Academic Setup**: Manage courses, strands, sections, and subjects.
- **Enrollment Monitoring**: Process and approve enrollment requests.
- **Announcements**: Broadcast important updates to the entire school community.

### For Teachers
- **Schedule Management**: View assigned teaching schedules and rooms.
- **Grade Entry**: Input and manage student grades for various terms.
- **Student Tracking**: Monitor student lists per section.

### For Students
- **Personal Dashboard**: View enrollment status and personal details.
- **Academic Records**: Check grades and academic progress.
- **Schedules**: Access class schedules and classroom assignments.
- **Payments**: Track tuition and miscellaneous fees.

---

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+), SweetAlert2 for notifications
- **Backend**: PHP (7.4+), vanilla PHP pages and server-side routing
- **Database**: MySQL / MariaDB
- **Mail & Utilities**: PHPMailer via `vendor/`
- **Server**: XAMPP / Apache
- **Assets**: Shared JS/CSS under `assets/js/` and `assets/css/`

---

## 📦 Dependencies

- **PHPMailer**: email sending library under `vendor/PHPMailer/`
- **SweetAlert2**: used for user notifications across auth, admin, and profile flows
- **fpdf**: PDF generation library found in `vendor/setasign/fpdf/` (if used by reports or exports)

---

## 📦 Installation & Setup

### 1. Prerequisites
- Install [XAMPP](https://www.apachefriends.org/) or any local PHP/MySQL environment.
- Git installed on your system.

### 2. Clone the Repository
```bash
git clone https://github.com/YoursTrulyInarius/School_Portal.git
cd School_Portal
```

### 3. Database Configuration
1. Open **phpMyAdmin**.
2. Create a new database named `school_portal`.
3. Import the `database.sql` file located in the project root.

### 4. Application Setup
1. Rename `config.example.php` to `config.php`.
2. Open `config.php` and update your database credentials if necessary:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', '');
   define('DB_NAME', 'school_portal');
   ```

### 5. Access the Portal
1. Move the project folder to `C:\xampp\htdocs\`.
2. Open your browser and navigate to `http://localhost/School_Portal/`.





---

## 🔑 Default Credentials

| Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` |

---

## 📁 Reorganization & Re-factoring (Recent Changes)

The codebase has been refactored to optimize performance, clean up redundant/unused files, and establish a DRY (Don't Repeat Yourself) file structure:

* **Consolidated Vendor Libraries**: Extraneous vendor configurations and multiple duplicate copies of PHPMailer have been removed. The application now uses a single, unified dependency folder at `/vendor/`.
* **Centralized Sidebar Assets**: Duplicated inline styles (~200 lines) and scripts (~55 lines) from the admin, teacher, and student sidebars were extracted into shared assets:
  * [`assets/css/sidebar.css`](file:///c:/xampp/htdocs/School_Portal/assets/css/sidebar.css)
  * [`assets/js/sidebar.js`](file:///c:/xampp/htdocs/School_Portal/assets/js/sidebar.js)
* **Centralized Auth Assets**: Shared styles for credentials screens (Login, Registration, OTP verification, Reset Password, Enrollment) were extracted into:
  * [`assets/css/auth.css`](file:///c:/xampp/htdocs/School_Portal/assets/css/auth.css)
* **Cleanup & Archiving**: Removed debug output text files and archived over 28 developer-only seeding, debug, and manual migration files in the [`_archive/`](file:///c:/xampp/htdocs/School_Portal/_archive) directory to clean the root environment.

---

## 📜 Version History & Roadmap

### `v1.1.0` - Current Release (Codebase Reorganization)
* Archived 28 debug/migration/seed files from project root to `_archive/`.
* Consolidated vendor/PHPMailer modules, deleting redundant file trees.
* Consolidated duplicate assets into shared stylesheets and scripts: `sidebar.css`, `sidebar.js`, and `auth.css`.
* Cleaned inline styles from authentication pages (`login.php`, `register.php`, etc.).

### 🔮 Future Roadmap (Upcoming Development)
We plan to introduce further structural enhancements and new components in subsequent versions:
* **Deep Refactoring**: Streamline backend routing, introduce unified middleware checks, and refine MVC architectures.
* **Precise & Robust Validation**: Add robust input validators and secure CSRF middleware to all user inputs.
* **Scholarship Portal Re-design**: Introduce a revamped, responsive Scholarship Management screen.
* **Component Extensions**: Extend functionalities for Attendance, Voting, and grade calculation engines.



