<?php
// Migration script to update grades table structure
require_once 'config.php';

echo "<h2>Migrating Grades Table</h2>";
echo "<p>This will modify the grades table to use the new schema.</p>";

// Step 1: Check current structure
echo "<h3>Step 1: Current Table Structure</h3>";
$result = $conn->query("DESCRIBE grades");
echo "<table border='1' cellpadding='5'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table><br>";

// Step 2: Drop foreign key constraints that might exist
echo "<h3>Step 2: Removing Old Constraints</h3>";
$constraints = [
    "ALTER TABLE grades DROP FOREIGN KEY IF EXISTS grades_ibfk_1",
    "ALTER TABLE grades DROP FOREIGN KEY IF EXISTS grades_ibfk_2",
    "ALTER TABLE grades DROP FOREIGN KEY IF EXISTS grades_ibfk_3"
];

foreach ($constraints as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✓ Constraint removed<br>";
    } else {
        echo "⚠ " . $conn->error . "<br>";
    }
}

// Step 3: Add schedule_id column if it doesn't exist
echo "<h3>Step 3: Adding schedule_id Column</h3>";
$sql = "ALTER TABLE grades ADD COLUMN IF NOT EXISTS schedule_id INT NULL AFTER student_id";
if ($conn->query($sql) === TRUE) {
    echo "✓ schedule_id column added<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

// Step 4: Add grade column if it doesn't exist
echo "<h3>Step 4: Adding grade Column</h3>";
$sql = "ALTER TABLE grades ADD COLUMN IF NOT EXISTS grade DECIMAL(3,2) NULL";
if ($conn->query($sql) === TRUE) {
    echo "✓ grade column added<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

// Step 5: Copy score to grade if score exists and grade is null
echo "<h3>Step 5: Migrating Data from score to grade</h3>";
$check = $conn->query("SHOW COLUMNS FROM grades LIKE 'score'");
if ($check->num_rows > 0) {
    $sql = "UPDATE grades SET grade = score WHERE grade IS NULL AND score IS NOT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Data migrated from score to grade (affected rows: " . $conn->affected_rows . ")<br>";
    } else {
        echo "✗ Error: " . $conn->error . "<br>";
    }
}

// Step 6: Drop old columns
echo "<h3>Step 6: Removing Old Columns</h3>";
$dropColumns = [
    "ALTER TABLE grades DROP COLUMN IF EXISTS course_id",
    "ALTER TABLE grades DROP COLUMN IF EXISTS grade_type",
    "ALTER TABLE grades DROP COLUMN IF EXISTS score"
];

foreach ($dropColumns as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✓ Column dropped<br>";
    } else {
        echo "⚠ " . $conn->error . "<br>";
    }
}

// Step 7: Modify teacher_id to allow NULL
echo "<h3>Step 7: Modifying teacher_id to Allow NULL</h3>";
$sql = "ALTER TABLE grades MODIFY COLUMN teacher_id INT NULL";
if ($conn->query($sql) === TRUE) {
    echo "✓ teacher_id modified<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

// Step 8: Modify term to allow NULL
echo "<h3>Step 8: Modifying term to Allow NULL</h3>";
$sql = "ALTER TABLE grades MODIFY COLUMN term VARCHAR(20) NULL";
if ($conn->query($sql) === TRUE) {
    echo "✓ term modified<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

// Step 9: Add new foreign keys
echo "<h3>Step 9: Adding New Foreign Keys</h3>";
$foreignKeys = [
    "ALTER TABLE grades ADD CONSTRAINT fk_grades_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE",
    "ALTER TABLE grades ADD CONSTRAINT fk_grades_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE",
    "ALTER TABLE grades ADD CONSTRAINT fk_grades_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL"
];

foreach ($foreignKeys as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✓ Foreign key added<br>";
    } else {
        echo "⚠ " . $conn->error . "<br>";
    }
}

// Step 10: Show final structure
echo "<h3>Step 10: Final Table Structure</h3>";
$result = $conn->query("DESCRIBE grades");
echo "<table border='1' cellpadding='5'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table><br>";

echo "<h2 style='color: green;'>✓ Migration Complete!</h2>";
echo "<p><a href='student/grades.php'>Test Grades Page</a></p>";
?>