<?php
require_once 'config.php';

// Add columns to students table
$sql1 = "ALTER TABLE students 
         ADD COLUMN IF NOT EXISTS address TEXT, 
         ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20)";

if ($conn->query($sql1) === TRUE) {
    echo "Students table updated successfully.<br>";
} else {
    echo "Error updating students table: " . $conn->error . "<br>";
}

// Add columns to teachers table
$sql2 = "ALTER TABLE teachers 
         ADD COLUMN IF NOT EXISTS address TEXT, 
         ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20)";

if ($conn->query($sql2) === TRUE) {
    echo "Teachers table updated successfully.<br>";
} else {
    echo "Error updating teachers table: " . $conn->error . "<br>";
}

// Add columns to teachers table
$sql3 = "ALTER TABLE teachers 
         ADD COLUMN IF NOT EXISTS employee_id VARCHAR(20)";

if ($conn->query($sql3) === TRUE) {
    echo "Teachers table updated successfully (employee_id check).<br>";
} else {
    echo "Error updating teachers table: " . $conn->error . "<br>";
}

echo "Database update process complete. You can delete this file.";
?>
