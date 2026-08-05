<?php
require_once 'config.php';

// Make columns nullable and set defaults where appropriate to prevent INSERT crashes
$queries = [
    "ALTER TABLE grades MODIFY COLUMN teacher_id INT NULL",
    "ALTER TABLE grades MODIFY COLUMN grade_type ENUM('quiz', 'assignment', 'exam', 'final') NULL",
    "ALTER TABLE grades MODIFY COLUMN score DECIMAL(5,2) NULL",
    "ALTER TABLE grades MODIFY COLUMN term VARCHAR(20) NULL",
    "ALTER TABLE grades MODIFY COLUMN schedule_id INT NULL"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "✓ Success: $query<br>";
    } else {
        echo "✗ Error: " . $conn->error . "<br>";
    }
}
?>
