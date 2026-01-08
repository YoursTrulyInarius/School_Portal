<?php
// C:\xampp\htdocs\School_Portal\includes\functions.php

function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    return $conn->real_escape_string($data);
}

function verify_login($username, $password) {
    global $conn;
    $username = clean_input($username);
    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            return $row;
        }
    }
    return false;
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

function check_admin() {
    check_login();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . BASE_URL . "index.php"); // Or access denied page
        exit();
    }
}

function getOrdinalSuffix($num) {
    if ($num == 1) return 'st';
    if ($num == 2) return 'nd';
    if ($num == 3) return 'rd';
    return 'th';
}
?>
