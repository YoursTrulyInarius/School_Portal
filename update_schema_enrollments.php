<?php
// C:\xampp\htdocs\School_Portal\update_schema_enrollments.php
require_once 'config.php';

// Create enrollment_requests table
$sql = "CREATE TABLE IF NOT EXISTS enrollment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    course_strand VARCHAR(100) NOT NULL,
    year_level VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    generated_username VARCHAR(100) NULL,
    generated_password VARCHAR(100) NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'enrollment_requests' created successfully.<br>";
} else {
    echo "✗ Error creating table: " . $conn->error . "<br>";
}

echo "<br><strong>Enrollment schema update complete!</strong>";
?>
