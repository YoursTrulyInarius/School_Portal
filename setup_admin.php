<?php
// C:\xampp\htdocs\School_Portal\setup_admin.php
require_once 'config.php';

// Password: admin123
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$username = 'admin';
$role = 'admin';

// Check if admin exists
$check = $conn->query("SELECT id FROM users WHERE username = '$username'");

if ($check->num_rows > 0) {
    // Update
    $sql = "UPDATE users SET password = '$hashed_password' WHERE username = '$username'";
    if ($conn->query($sql)) {
        echo "<h1>Success</h1>";
        echo "<p>Admin password has been reset to: <strong>$password</strong></p>";
    } else {
        echo "Error updating admin: " . $conn->error;
    }
} else {
    // Create
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
    if ($conn->query($sql)) {
        echo "<h1>Success</h1>";
        echo "<p>Admin user created with password: <strong>$password</strong></p>";
    } else {
        echo "Error creating admin: " . $conn->error;
    }
}

echo "<br><a href='login.php'>Go to Login</a>";
?>
