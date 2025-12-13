<?php
require_once 'config.php';

$output = "--- Debugging Student Schedules ---\n";

// 1. List all students and their assigned section
$output .= "\n1. Students and their Sections:\n";
$sql = "SELECT s.id, s.firstname, s.lastname, sec.section_name 
        FROM students s 
        LEFT JOIN sections sec ON s.section_id = sec.id";
$res = $conn->query($sql);
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $output .= "Student: {$row['firstname']} {$row['lastname']} | Section: '{$row['section_name']}'\n";
    }
} else {
    $output .= "No students found.\n";
}

// 2. List all distinct section names in schedules
$output .= "\n2. Sections in Schedules Table:\n";
$sql2 = "SELECT DISTINCT section_name, count(*) as count FROM schedules GROUP BY section_name";
$res2 = $conn->query($sql2);
if ($res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        $output .= "Section in Schedule: '{$row['section_name']}' (Count: {$row['count']})\n";
    }
} else {
    $output .= "No schedules found.\n";
}

// 3. Check for exact matches
$output .= "\n3. Checking for matches:\n";
$res->data_seek(0); // Reset student pointer
while ($student = $res->fetch_assoc()) {
    $sec = $conn->real_escape_string($student['section_name']);
    $check = $conn->query("SELECT count(*) as c FROM schedules WHERE section_name = '$sec'");
    $count = $check->fetch_assoc()['c'];
    $output .= "Student {$student['firstname']} (Section: '{$student['section_name']}') -> Matching Schedules: $count\n";
}

file_put_contents('debug_schedule_data_out.txt', $output);
echo "Done.";
?>
