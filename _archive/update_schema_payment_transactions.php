<?php
// C:\xampp\htdocs\School_Portal\update_schema_payment_transactions.php
require_once 'config.php';

// Create payment_transactions table
$sql = "CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes VARCHAR(255) DEFAULT '',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'payment_transactions' created/verified successfully.<br>";
} else {
    echo "✗ Error creating table: " . $conn->error . "<br>";
}

// Add total_fee column to students table
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'total_fee'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE students ADD COLUMN total_fee DECIMAL(10,2) DEFAULT 9000.00";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Column 'total_fee' added to students table.<br>";
    } else {
        echo "✗ Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "○ Column 'total_fee' already exists.<br>";
}

echo "<br><strong>Payment schema update complete!</strong>";
?>
