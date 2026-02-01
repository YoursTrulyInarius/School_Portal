# Westprime Horizon School Portal

Westprime Horizon School Portal is a comprehensive, web-based management system designed to streamline academic operations for administrators, teachers, and students.

> [!NOTE]
> **Project Status**: This system is currently **ongoing** and under active development.
> **On going**: Need to update/change the "Schoolarship" function, need to update the responsiveness.

## 👨‍💻 Developer
Developed by **Sonjeev C. Cabardo**

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

- **Frontend**: HTML5, Vanilla CSS3, JavaScript (ES6+)
- **Backend**: PHP (7.4+)
- **Database**: MySQL / MariaDB
- **Server**: XAMPP / Apache

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

## 📄 License
This project is for educational purposes. All rights reserved.
