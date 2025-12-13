<?php
require_once 'config.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    echo "Updating schedules table...<br>";

    // Add columns if they don't exist
    $columns = [
        'subject' => "VARCHAR(100) NULL",
        'room' => "VARCHAR(50) NULL",
        'time' => "VARCHAR(100) NULL", // For string range "9:00-10:00"
        'section_name' => "VARCHAR(100) NULL", // For manual block entry
        'strand_id' => "INT NULL" // For SHS Strands
    ];

    foreach ($columns as $col => $def) {
        $check = $conn->query("SHOW COLUMNS FROM schedules LIKE '$col'");
        if ($check->num_rows == 0) {
            $conn->query("ALTER TABLE schedules ADD COLUMN $col $def");
            echo "Added column $col.<br>";
        } else {
            echo "Column $col already exists.<br>";
        }
    }

    // Modify columns to be nullable
    $conn->query("ALTER TABLE schedules MODIFY COLUMN course_id INT NULL");
    $conn->query("ALTER TABLE schedules MODIFY COLUMN section_id INT NULL");
    $conn->query("ALTER TABLE schedules MODIFY COLUMN time_start TIME NULL");
    $conn->query("ALTER TABLE schedules MODIFY COLUMN time_end TIME NULL");
    
    // Add Foreign Key for strand_id if not exists (Simplified check)
    // We won't add constraint strictly to avoid errors if key exists, but good practice.
    // simpler to just leave it as INT NULL for now, or add FK if needed.
    // Let's add FK safely using a try-catch or check query, or just skip FK for speed/flexibility as requested.
    // The user wants "manual", so loose coupling is better.
    
    echo "Modified columns to be nullable.<br>";
    echo "Database update successful!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
