<?php
// config.example.php - Template for database configuration
// Copy this file to config.php and update with your database credentials

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'school_portal');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Base URL for links (adjust if your project is in a subdirectory)
define('BASE_URL', 'http://localhost/School_Portal/');

// Timezone
date_default_timezone_set('Asia/Manila');
?>