<?php
// C:\xampp\htdocs\School_Portal\update_grades_table.php
require_once 'config.php';

// Add schedule_id column to grades table if it doesn't exist
$sql = "ALTER TABLE grades ADD COLUMN IF NOT EXISTS schedule_id INT NULL AFTER student_id";

if ($conn->query($sql) === TRUE) {
    echo "✓ Grades table updated successfully - schedule_id column added.<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

echo "<br><strong>Update complete!</strong>";
?>
