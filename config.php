<?php
// C:\xampp\htdocs\School_Portal\config.php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'westprime_portal');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Base URL for links
define('BASE_URL', 'http://localhost/School_Portal/');
?>
