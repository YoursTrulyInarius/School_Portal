<?php
// C:\xampp\htdocs\School_Portal\update_schema_courses.php
require_once 'config.php';

$columns = [
    'subject' => "VARCHAR(100) DEFAULT ''",
    'teacher_id' => "INT DEFAULT NULL",
    'room_time' => "VARCHAR(100) DEFAULT ''"
];

foreach ($columns as $col => $def) {
    $result = $conn->query("SHOW COLUMNS FROM courses LIKE '$col'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE courses ADD COLUMN $col $def";
        if ($conn->query($sql) === TRUE) {
            echo "Column '$col' added successfully.<br>";
        } else {
            echo "Error adding column '$col': " . $conn->error . "<br>";
        }
    } else {
        echo "Column '$col' already exists.<br>";
    }
}

// Add Foreign Key for teacher_id if not exists
// Only add if column was just added or exists, usually safe to try add constraint but let's be careful.
// Simple check:
$sql = "ALTER TABLE courses ADD CONSTRAINT fk_course_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL";
if ($conn->query($sql) === TRUE) {
    echo "Foreign key for teacher_id added.<br>";
} else {
    // If it fails, it might already exist or teacher_id type mismatch, usually fine to ignore in this simple script if it says 'duplicate key'
    echo "Foreign key add result: " . $conn->error . "<br>";
}

echo "Schema update complete.";
?>
