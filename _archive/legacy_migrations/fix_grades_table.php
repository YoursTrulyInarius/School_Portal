<?php
// C:\xampp\htdocs\School_Portal\fix_grades_table.php
require_once 'config.php';

// Add grade column if it doesn't exist
$sql1 = "ALTER TABLE grades ADD COLUMN IF NOT EXISTS grade DECIMAL(3,2) NOT NULL DEFAULT 1.00";

if ($conn->query($sql1) === TRUE) {
    echo "✓ Grade column added/verified.<br>";
} else {
    echo "✗ Error adding grade column: " . $conn->error . "<br>";
}

echo "<br><strong>Grades table fixed!</strong>";
?>
