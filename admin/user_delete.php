<?php
// C:\xampp\htdocs\School_Portal\admin\user_delete.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

if (isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    
    // Prevent deleting self (admin)
    if ($id == $_SESSION['user_id']) {
        // ideally show error
        header("Location: users.php?error=Cannot delete yourself");
        exit();
    }

    // Since we used ON DELETE CASCADE in schema, deleting from users will remove from students/teachers automatically
    $sql = "DELETE FROM users WHERE id = '$id'";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: users.php?msg=User deleted");
    } else {
        header("Location: users.php?error=Error deleting record: " . $conn->error);
    }
} else {
    header("Location: users.php");
}
?>
