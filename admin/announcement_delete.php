<?php
// C:\xampp\htdocs\School_Portal\admin\announcement_delete.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

if (isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    $conn->query("DELETE FROM announcements WHERE id='$id'");
}
header("Location: announcements.php");
exit();
?>
