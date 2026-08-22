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
3. Configure SMTP in the same `config.php` file if password reset and enrollment emails are needed:
  ```php
  define('SMTP_HOST', 'smtp.gmail.com');
  define('SMTP_PORT', 587);
  define('SMTP_USERNAME', 'your_email@gmail.com');
  define('SMTP_PASSWORD', 'your_app_password');
  define('SMTP_ENCRYPTION', 'tls');
  define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
  define('SMTP_FROM_NAME', 'Westprime Horizon Institute');
  ```
  For Gmail, use an App Password rather than your regular account password. SMTP credentials are used by both the password reset and enrollment approval email flows.

### 5. Access the Portal
1. Move the project folder to `C:\xampp\htdocs\`.
2. Open your browser and navigate to `http://localhost/School_Portal/`.





---

## 🔑 Default Credentials

| Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` |

---

## 📁 Implemented Changes

### UI and shared assets
- Standardized the primary application color to Royal Blue (`#4169E1`) across the global styles, authentication screens, sidebars, dashboards, schedules, payments, and administrative pages.
- Centralized shared sidebar styles and behavior in `assets/css/sidebar.css` and `assets/js/sidebar.js`.
- Centralized authentication styles in `assets/css/auth.css` for login, registration, enrollment, OTP verification, and password reset pages.
- Consolidated the project dependencies under the single `vendor/` directory, including PHPMailer and FPDF.

### Academic year and scheduling
- Added automatic academic-year detection through `get_current_school_year()` in `config.php`.
- Added automatic current-semester detection through `get_current_semester_label()`.
- Updated schedule creation so administrators select either `1st Semester` or `2nd Semester` when assigning a teacher to a class.
- Removed the quarter field from schedules. College uses two semesters, while senior high school follows the same two-semester schedule model, with quarters handled separately where needed for grading.
- Updated teacher and student schedule printouts to display the current academic year and semester dynamically instead of using hardcoded values.

### Database and email configuration
- Rebuilt `database.sql` as the consolidated schema and setup script for users, teachers, students, courses, strands, sections, schedules, grades, attendance, announcements, enrollment requests, and payment transactions.
- Added schedule `school_year` and `semester` fields to the canonical database schema.
- Centralized all SMTP settings in `config.php` and `config.example.php`. The password reset and enrollment approval flows now use the shared `SMTP_*` configuration constants instead of hardcoded credentials.
- Archived legacy migrations, seed scripts, debug utilities, and the duplicate database setup document under `_archive/`.

---

## 📜 Version History & Roadmap

### `v1.1.0` - Current Release (Portal Reorganization and Academic Updates)
* Standardized the application color scheme using Royal Blue (`#4169E1`).
* Centralized sidebar and authentication assets.
* Consolidated the database schema in `database.sql`.
* Added automatic academic-year and semester display logic.
* Added semester selection to administrator schedule assignment and removed schedule quarters.
* Centralized PHPMailer SMTP configuration in `config.php`.
* Archived developer-only migrations, seeders, debug scripts, and duplicate setup documentation.

### 🔮 Future Roadmap (Upcoming Development)
We plan to introduce further structural enhancements and new components in subsequent versions:
* **Deep Refactoring**: Streamline backend routing, introduce unified middleware checks, and refine MVC architectures.
* **Precise & Robust Validation**: Add robust input validators and secure CSRF middleware to all user inputs.
* **Scholarship Portal Re-design**: Introduce a revamped, responsive Scholarship Management screen.
* **Component Extensions**: Extend functionalities for Attendance, Voting, and grade calculation engines.



