<?php
// C:\xampp\htdocs\School_Portal\index.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Redirect to appropriate dashboard if they hit index.php directly
if ($role == 'admin') {
    header("Location: admin/dashboard.php");
} elseif ($role == 'teacher') {
    header("Location: teacher/dashboard.php");
} elseif ($role == 'student') {
    header("Location: student/dashboard.php");
} else {
    // Fallback
    echo "Unknown role.";
    echo '<br><a href="logout.php">Logout</a>';
}
?>
