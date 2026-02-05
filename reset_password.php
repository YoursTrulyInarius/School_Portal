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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #f4f7f6; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        
        .reset-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .reset-box { 
            background: white; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            text-align: center;
        }
        
        .logo-container {
            margin-bottom: 25px;
        }
        
        .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        h2 {
            color: #333;
            font-weight: 600;
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }
        
        p.subtitle {
            color: #777;
            font-size: 0.9rem;
            margin: 0 0 30px 0;
            line-height: 1.5;
        }
        
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }
        
        .form-control { 
            width: 100%; 
            padding: 12px 15px; 
            border: 1px solid #e1e1e1; 
            border-radius: 8px; 
            font-size: 0.95rem;
            box-sizing: border-box; 
            transition: all 0.2s;
            background: #f9f9f9;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2ecc71; /* Green for reset */
            background: white;
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
        }
        
        .btn-primary { 
            background: #28a745; 
            color: white; 
            border: none; 
            padding: 14px; 
            border-radius: 8px; 
            width: 100%; 
            font-size: 1rem; 
            cursor: pointer; 
            font-weight: 600; 
            transition: transform 0.1s, box-shadow 0.2s;
        }
        
        .btn-primary:hover { 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .alert { 
            padding: 12px; 
            margin-bottom: 20px; 
            border-radius: 8px; 
            font-size: 0.9rem; 
            text-align: left;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <p class="subtitle">Enter your new password below.</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
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
