<?php
// C:\xampp\htdocs\School_Portal\update_schema_student_class.php
require_once 'config.php';

// Add class_year column to students table
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'class_year'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE students ADD COLUMN class_year VARCHAR(50) DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Column 'class_year' added to students table.<br>";
    } else {
        echo "✗ Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "○ Column 'class_year' already exists.<br>";
}

echo "<br><strong>Schema update complete!</strong>";
?>
