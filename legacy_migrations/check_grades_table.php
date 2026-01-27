<?php
// Check and fix grades table structure
require_once 'config.php';

echo "<h2>Checking Grades Table Structure</h2>";

// Get current table structure
$result = $conn->query("DESCRIBE grades");
echo "<h3>Current Columns:</h3><ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li><strong>{$row['Field']}</strong> - {$row['Type']}</li>";
}
echo "</ul>";

// Add grade column if it doesn't exist
echo "<h3>Adding Missing Columns:</h3>";
$sql1 = "ALTER TABLE grades ADD COLUMN IF NOT EXISTS grade DECIMAL(3,2) NULL";
if ($conn->query($sql1) === TRUE) {
    echo "✓ Grade column added/verified.<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

// Add schedule_id if it doesn't exist
$sql2 = "ALTER TABLE grades ADD COLUMN IF NOT EXISTS schedule_id INT NULL AFTER student_id";
if ($conn->query($sql2) === TRUE) {
    echo "✓ Schedule_id column added/verified.<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

echo "<h3>Updated Table Structure:</h3><ul>";
$result2 = $conn->query("DESCRIBE grades");
while ($row = $result2->fetch_assoc()) {
    echo "<li><strong>{$row['Field']}</strong> - {$row['Type']}</li>";
}
echo "</ul>";

echo "<br><strong>Done! Please refresh the grades page.</strong>";
?>