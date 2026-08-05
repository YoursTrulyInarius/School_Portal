<?php
// C:\xampp\htdocs\School_Portal\update_db_for_reset.php
require_once 'config.php';

// Add columns if they don't exist
$sql = "SHOW COLUMNS FROM users LIKE 'reset_token_hash'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alter_sql = "ALTER TABLE users 
                  ADD COLUMN reset_token_hash VARCHAR(64) NULL AFTER role,
                  ADD COLUMN reset_token_expires_at DATETIME NULL AFTER reset_token_hash";
    
    if ($conn->query($alter_sql)) {
        echo "Successfully added reset_token_hash and reset_token_expires_at columns to users table.";
    } else {
        echo "Error adding columns: " . $conn->error;
    }
} else {
    echo "Columns already exist.";
}
?>
