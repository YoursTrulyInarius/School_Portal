<?php
// C:\xampp\htdocs\School_Portal\update_schema_profile_pics.php
require_once 'config.php';

$tables = ['teachers', 'students'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW COLUMNS FROM $table LIKE 'profile_picture'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE $table ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL";
        if ($conn->query($sql) === TRUE) {
            echo "✓ Column 'profile_picture' added to $table table.<br>";
        } else {
            echo "✗ Error adding column to $table: " . $conn->error . "<br>";
        }
    } else {
        echo "○ Column 'profile_picture' already exists in $table.<br>";
    }
}

echo "<br><strong>Schema update complete!</strong>";
?>
