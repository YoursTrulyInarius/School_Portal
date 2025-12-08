<?php
// C:\xampp\htdocs\School_Portal\admin\save_schedule.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = isset($_POST['schedule_id']) ? clean_input($_POST['schedule_id']) : null;
    $class_year = clean_input($_POST['class_year']);
    $day = clean_input($_POST['day']);
    $time = clean_input($_POST['time']);
    $subject = clean_input($_POST['subject']);
    $teacher_id = clean_input($_POST['teacher_id']);
    $room = clean_input($_POST['room']);
    
    if ($schedule_id) {
        // Update existing
        $stmt = $conn->prepare("UPDATE schedules SET time_start=?, subject=?, teacher_id=?, room=? WHERE id=?");
        $stmt->bind_param("ssisi", $time, $subject, $teacher_id, $room, $schedule_id);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO schedules (class_year, day, time_start, subject, teacher_id, room) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $class_year, $day, $time, $subject, $teacher_id, $room);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $schedule_id ? $schedule_id : $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}
?>
