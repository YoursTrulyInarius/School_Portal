<?php
require_once 'config.php';

// SQL to alter the table
$sql_check = "SHOW COLUMNS FROM schedules LIKE 'time_start'";
$result = $conn->query($sql_check);

if ($result->num_rows > 0) {
    echo "Updating schedules table...\n";
    
    // 1. Drop old time columns
    $conn->query("ALTER TABLE schedules DROP COLUMN time_start, DROP COLUMN time_end");
    
    // 2. Add new columns
    $conn->query("ALTER TABLE schedules 
        ADD COLUMN strand_id INT NULL AFTER course_id,
        ADD COLUMN subject VARCHAR(100) NOT NULL AFTER strand_id,
        ADD COLUMN time VARCHAR(50) NOT NULL AFTER day,
        ADD COLUMN room VARCHAR(50) NULL AFTER time");
        
    // 3. Modify course_id to be nullable
    $conn->query("ALTER TABLE schedules MODIFY COLUMN course_id INT NULL");
    
    // 4. Add FK for strand
    $conn->query("ALTER TABLE schedules ADD CONSTRAINT fk_schedules_strand FOREIGN KEY (strand_id) REFERENCES strands(id) ON DELETE CASCADE");
    
    echo "Table updated successfully.\n";
} else {
    echo "Table appears to be already updated.\n";
}
?>
