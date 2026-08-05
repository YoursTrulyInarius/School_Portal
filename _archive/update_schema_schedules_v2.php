<?php
// C:\xampp\htdocs\School_Portal\update_schema_schedules_v2.php
require_once 'config.php';

// Update schedules table structure
$sql = "ALTER TABLE schedules 
        MODIFY COLUMN time_start VARCHAR(20) NULL,
        MODIFY COLUMN time_end VARCHAR(20) NULL,
        MODIFY COLUMN room VARCHAR(50) NULL";

if ($conn->query($sql) === TRUE) {
    echo "✓ Schedules table updated successfully.<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

echo "<br><strong>Schema update complete!</strong>";
?>
