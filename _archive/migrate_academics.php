<?php
require_once 'config.php';

// 1. Create strands table
$sql_strands = "CREATE TABLE IF NOT EXISTS strands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    strand_name VARCHAR(100) NOT NULL,
    strand_code VARCHAR(20) UNIQUE NOT NULL,
    description VARCHAR(255) DEFAULT 'Senior High School Strand'
)";

if ($conn->query($sql_strands) === TRUE) {
    echo "✓ Table 'strands' created successfully.<br>";
} else {
    echo "✗ Error creating 'strands' table: " . $conn->error . "<br>";
}

// 2. Add strand_id to sections table
// Check if column exists first
$check_col = $conn->query("SHOW COLUMNS FROM sections LIKE 'strand_id'");
if ($check_col->num_rows == 0) {
    $sql_alter = "ALTER TABLE sections ADD COLUMN strand_id INT NULL AFTER course_id, ADD CONSTRAINT fk_sections_strand FOREIGN KEY (strand_id) REFERENCES strands(id) ON DELETE SET NULL";
    if ($conn->query($sql_alter) === TRUE) {
        echo "✓ Column 'strand_id' added to 'sections' table.<br>";
    } else {
        echo "✗ Error altering 'sections' table: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Column 'strand_id' already exists in 'sections' table.<br>";
}

// 3. Migrate SHS courses to strands table
$shs_courses = $conn->query("SELECT * FROM courses WHERE description = 'Senior High'");
if ($shs_courses->num_rows > 0) {
    echo "<br>Migrating " . $shs_courses->num_rows . " SHS courses to strands...<br>";
    while ($course = $shs_courses->fetch_assoc()) {
        $name = $conn->real_escape_string($course['course_name']);
        $code = $conn->real_escape_string($course['course_code']);
        $desc = "Senior High School Strand"; // Standardize description
        
        // Insert into strands
        $insert = "INSERT IGNORE INTO strands (strand_name, strand_code, description) VALUES ('$name', '$code', '$desc')";
        if ($conn->query($insert) === TRUE) {
            $new_strand_id = $conn->insert_id;
            echo "  - Migrated '$code' (New ID: $new_strand_id)<br>";
            
            // Update sections linked to this old course_id
            $old_course_id = $course['id'];
            $update_sections = "UPDATE sections SET strand_id = $new_strand_id, course_id = NULL WHERE course_id = $old_course_id";
            if ($conn->query($update_sections)) {
                echo "    - Updated linked sections.<br>";
            } else {
                echo "    - ✗ Error updating sections: " . $conn->error . "<br>";
            }
            
            // Delete from courses table
             $conn->query("DELETE FROM courses WHERE id = $old_course_id");
             echo "    - Removed from courses table.<br>";
             
        } else {
             // If duplicate key error (maybe ran before), try to get ID
             if ($conn->errno == 1062) {
                 $existing = $conn->query("SELECT id FROM strands WHERE strand_code = '$code'")->fetch_assoc();
                 echo "  - '$code' already in strands (ID: {$existing['id']}). Skipping insertion.<br>";
             } else {
                echo "  - ✗ Error migrating '$code': " . $conn->error . "<br>";
             }
        }
    }
} else {
    echo "<br>No 'Senior High' courses found to migrate in 'courses' table.<br>";
}

echo "<br><strong>Database migration complete!</strong>";
?>
