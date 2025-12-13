<?php
require_once 'config.php';

// Add columns if they don't exist
$alter_sql = "ALTER TABLE enrollment_requests 
              ADD COLUMN course_id INT NULL AFTER address,
              ADD COLUMN strand_id INT NULL AFTER course_id,
              ADD COLUMN year_level VARCHAR(20) NULL AFTER strand_id,
              ADD COLUMN block VARCHAR(5) NULL AFTER year_level";

if ($conn->query($alter_sql)) {
    echo "Successfully enrolled columns.\n";
} else {
    echo "Error or columns might already exist: " . $conn->error . "\n";
}

// Add Foreign Keys explicitly if needed, but for requests simple INT is fine usually. 
// However, good practice to ensure integrity if we wanted, but requests are transient. 
// We will skip FK constraints for requests to keep it flexible/simple, 
// or valid IDs will be enforced by the form logic.

echo "Migration completed.";
?>
