<?php
// C:\xampp\htdocs\School_Portal\verify_otp.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = '';
$email = $_SESSION['reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = clean_input($_POST['otp']);
    
    // Hash input to compare
    $otp_hash = hash('sha256', $otp_input);
    
    // Verify against DB
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND reset_token_hash = ? AND reset_token_expires_at > NOW()");
    $stmt->bind_param("ss", $email, $otp_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid or expired verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .auth-box { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; width: 100%; max-width: 400px; }
        .form-control { text-align: center; letter-spacing: 5px; font-size: 1.5rem; }
    </style>
</head>
<body>
    <div class="auth-box">
        <h2 style="color: #333; margin-bottom: 10px;">Verification Code</h2>
        <p style="color: #666; margin-bottom: 30px;">Enter the 6-digit code sent to<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="otp" class="form-control" placeholder="000000" maxlength="6" required pattern="[0-9]{6}" inputmode="numeric">
            </div>
            
            <button type="submit" class="btn btn-block">Verify</button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="forgot_password.php" style="color: #0056b3; text-decoration: none; font-size: 0.9rem;">Resend Code</a>
        </div>
    </div>
</body>
</html>
