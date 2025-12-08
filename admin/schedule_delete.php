<?php
// C:\xampp\htdocs\School_Portal\admin\schedule_delete.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

if (isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    $conn->query("DELETE FROM schedules WHERE id='$id'");
}
header("Location: schedules.php");
exit();
?>
