<?php
// C:\xampp\htdocs\School_Portal\update_schema_payments.php
require_once 'config.php';

// Check if columns exist
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'is_scholar'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE students ADD COLUMN is_scholar BOOLEAN DEFAULT 0";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'is_scholar' added successfully.<br>";
    } else {
        echo "Error adding column 'is_scholar': " . $conn->error . "<br>";
    }
} else {
    echo "Column 'is_sf' already exists.<br>";
}

$result = $conn->query("SHOW COLUMNS FROM students LIKE 'enrollment_details'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE students ADD COLUMN enrollment_details VARCHAR(255) DEFAULT ''";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'enrollment_details' added successfully.<br>";
    } else {
        echo "Error adding column 'enrollment_details': " . $conn->error . "<br>";
    }
} else {
    echo "Column 'enrollment_details' already exists.<br>";
}

echo "Schema update complete.";
?>
