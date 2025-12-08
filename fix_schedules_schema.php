<?php
// C:\xampp\htdocs\School_Portal\fix_schedules_schema.php
require_once 'config.php';

// Make section_id nullable since we're not using sections anymore
$sql = "ALTER TABLE schedules MODIFY COLUMN section_id INT NULL";

if ($conn->query($sql) === TRUE) {
    echo "✓ Updated schedules table: section_id is now nullable.<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

echo "<br><strong>Schema fix complete!</strong>";
?>
