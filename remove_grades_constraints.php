<?php
// C:\xampp\htdocs\School_Portal\remove_grades_constraints.php
require_once 'config.php';

// Remove old foreign key constraints from grades table
$sql1 = "ALTER TABLE grades DROP FOREIGN KEY IF EXISTS grades_ibfk_1";
$sql2 = "ALTER TABLE grades DROP FOREIGN KEY IF EXISTS grades_ibfk_2";
$sql3 = "ALTER TABLE grades DROP FOREIGN KEY IF EXISTS grades_ibfk_3";

// Drop old columns we don't need
$sql4 = "ALTER TABLE grades DROP COLUMN IF EXISTS course_id";
$sql5 = "ALTER TABLE grades DROP COLUMN IF EXISTS section_id";

$queries = [$sql1, $sql2, $sql3, $sql4, $sql5];

foreach ($queries as $index => $query) {
    if ($conn->query($query) === TRUE) {
        echo "✓ Query " . ($index + 1) . " executed successfully.<br>";
    } else {
        echo "✗ Query " . ($index + 1) . " error: " . $conn->error . "<br>";
    }
}

echo "<br><strong>Grades table constraints removed!</strong>";
?>
