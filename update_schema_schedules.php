<?php
// C:\xampp\htdocs\School_Portal\update_schema_schedules.php
require_once 'config.php';

$columns = [
    'subject' => "VARCHAR(100) DEFAULT ''",
    'room' => "VARCHAR(50) DEFAULT ''"
];

foreach ($columns as $col => $def) {
    $result = $conn->query("SHOW COLUMNS FROM schedules LIKE '$col'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE schedules ADD COLUMN $col $def";
        if ($conn->query($sql) === TRUE) {
            echo "Column '$col' added successfully.<br>";
        } else {
            echo "Error adding column '$col': " . $conn->error . "<br>";
        }
    } else {
        echo "Column '$col' already exists.<br>";
    }
}

echo "Schema update complete.";
?>
