<?php
// Quick fix: Add grade and schedule_id columns to grades table
require_once 'config.php';

echo "<h2>Quick Database Fix for Grades Table</h2>";

// Add schedule_id column
$sql1 = "ALTER TABLE grades ADD COLUMN schedule_id INT NULL AFTER student_id";
if ($conn->query($sql1) === TRUE) {
    echo "✓ schedule_id column added<br>";
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "✓ schedule_id column already exists<br>";
    } else {
        echo "✗ Error adding schedule_id: " . $conn->error . "<br>";
    }
}

// Add grade column
$sql2 = "ALTER TABLE grades ADD COLUMN grade DECIMAL(3,2) NULL";
if ($conn->query($sql2) === TRUE) {
    echo "✓ grade column added<br>";
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "✓ grade column already exists<br>";
    } else {
        echo "✗ Error adding grade: " . $conn->error . "<br>";
    }
}

// Copy score to grade if score exists
$check = $conn->query("SHOW COLUMNS FROM grades LIKE 'score'");
if ($check && $check->num_rows > 0) {
    $sql3 = "UPDATE grades SET grade = score WHERE grade IS NULL AND score IS NOT NULL";
    if ($conn->query($sql3) === TRUE) {
        echo "✓ Migrated " . $conn->affected_rows . " records from score to grade<br>";
    }
}

echo "<br><h3 style='color: green;'>✓ Database Updated!</h3>";
echo "<p><a href='student/grades.php'>Click here to test the grades page</a></p>";
?>