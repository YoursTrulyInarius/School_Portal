<?php
require_once 'config.php';

// Add profile_image column to students table
$sql1 = "ALTER TABLE students ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL";

if ($conn->query($sql1) === TRUE) {
    echo "Students table updated successfully (profile_image).<br>";
} else {
    echo "Error updating students table: " . $conn->error . "<br>";
}

// Add profile_image column to teachers table
$sql2 = "ALTER TABLE teachers ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL";

if ($conn->query($sql2) === TRUE) {
    echo "Teachers table updated successfully (profile_image).<br>";
} else {
    echo "Error updating teachers table: " . $conn->error . "<br>";
}

echo "Database update process complete. You can delete this file.";
?>
