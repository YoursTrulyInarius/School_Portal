<?php
// C:\xampp\htdocs\School_Portal\admin\section_delete.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

if (isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    $conn->query("DELETE FROM sections WHERE id='$id'");
}
header("Location: academics.php");
exit();
?>
