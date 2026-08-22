<?php
// C:\xampp\htdocs\School_Portal\config.php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'school_portal');

// SMTP configuration
// Update these values in your local config before using email features.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
define('SMTP_FROM_NAME', 'Westprime Horizon Institute');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Base URL for links
define('BASE_URL', 'http://localhost/School_Portal/');

// Timezone
date_default_timezone_set('Asia/Manila');

function get_current_school_year() {
    $current_year = (int) date('Y');
    return ($current_year - 1) . '-' . $current_year;
}

function get_current_semester_label() {
    $month = (int) date('n');
    return ($month >= 1 && $month <= 6) ? '2nd Semester' : '1st Semester';
}
?>