<?php
// C:\xampp\htdocs\School_Portal\update_schema_class_year.php
require_once 'config.php';

// Add class_year column to schedules table
$result = $conn->query("SHOW COLUMNS FROM schedules LIKE 'class_year'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE schedules ADD COLUMN class_year VARCHAR(50) DEFAULT ''";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Column 'class_year' added successfully.<br>";
    } else {
        echo "✗ Error adding column 'class_year': " . $conn->error . "<br>";
    }
} else {
    echo "○ Column 'class_year' already exists.<br>";
}

echo "<br><strong>Schema update complete!</strong>";
?>
