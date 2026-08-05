<?php
// C:\xampp\htdocs\School_Portal\reset_password.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear token
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE email = ?");
        $update_stmt->bind_param("ss", $hashed_password, $email);
        
        if ($update_stmt->execute()) {
            // Clear session
            unset($_SESSION['otp_verified']);
            unset($_SESSION['reset_email']);
            
            $success = "Password reset successfully! You can now <a href='login.php' style='font-weight: bold;'>login</a>.";
        } else {
            $error = "Error resetting password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/auth.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/sweetalert.js"></script>
    <style>
        .reset-box { margin: auto; max-width: 420px; }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="reset-box">
        <div class="logo-container">
            <img src="logo.jpg" alt="Westprime Horizon Logo">
        </div>
        
        <h2>Reset Password</h2>
        
        <?php if ($success): ?>
            <script>
                window.SWEETALERT_FLASH = {
                    type: 'success',
                    title: 'Password Reset',
                    html: 'Password reset successfully! You can now <a href="login.php" style="font-weight:bold;color:#4169E1;">login</a>.',
                    showConfirmButton: true,
                    allowOutsideClick: false
                };
            </script>
            <p class="subtitle">Password updated successfully. Use the link above to login.</p>
        <?php else: ?>
            <p class="subtitle">Enter your new password below.</p>
            
            <?php if ($error): ?>
                <script>
                    window.SWEETALERT_FLASH = {
                        type: 'error',
                        title: 'Reset Failed',
                        text: <?php echo json_encode($error); ?>
                    };
                </script>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required minlength="6">
                </div>
                
                <button type="submit" class="btn-primary">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
