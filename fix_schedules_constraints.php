<?php
// C:\xampp\htdocs\School_Portal\fix_schedules_constraints.php
require_once 'config.php';

// Drop old foreign key constraints
$sql1 = "ALTER TABLE schedules DROP FOREIGN KEY IF EXISTS schedules_ibfk_1";
$sql2 = "ALTER TABLE schedules DROP FOREIGN KEY IF EXISTS schedules_ibfk_2";
$sql3 = "ALTER TABLE schedules DROP FOREIGN KEY IF EXISTS schedules_ibfk_3";

// Drop old columns we don't need anymore
$sql4 = "ALTER TABLE schedules 
         DROP COLUMN IF EXISTS section_id,
         DROP COLUMN IF EXISTS course_id,
         DROP COLUMN IF EXISTS time_end";

// Make sure we have the right columns
$sql5 = "ALTER TABLE schedules 
         ADD COLUMN IF NOT EXISTS class_year VARCHAR(100) AFTER id,
         ADD COLUMN IF NOT EXISTS day VARCHAR(20) AFTER class_year,
         ADD COLUMN IF NOT EXISTS time_start VARCHAR(50) AFTER day,
         ADD COLUMN IF NOT EXISTS subject VARCHAR(200) AFTER time_start,
         ADD COLUMN IF NOT EXISTS teacher_id INT AFTER subject,
         ADD COLUMN IF NOT EXISTS room VARCHAR(50) AFTER teacher_id";

$queries = [$sql1, $sql2, $sql3, $sql4, $sql5];

foreach ($queries as $index => $query) {
    if ($conn->query($query) === TRUE) {
        echo "✓ Query " . ($index + 1) . " executed successfully.<br>";
    } else {
        echo "✗ Query " . ($index + 1) . " error: " . $conn->error . "<br>";
    }
}

echo "<br><strong>Schedules table fixed!</strong>";
?>
