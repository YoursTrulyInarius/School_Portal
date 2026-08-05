<?php
// C:\xampp\htdocs\School_Portal\forgot_password.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

// PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';

$success = '';
$error = '';
$test_link = ''; // For localhost testing

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT users.id, 
                           COALESCE(students.firstname, teachers.firstname) as firstname, 
                           COALESCE(students.lastname, teachers.lastname) as lastname 
                           FROM users 
                           LEFT JOIN students ON users.id = students.user_id 
                           LEFT JOIN teachers ON users.id = teachers.user_id 
                           WHERE users.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Also check simple users table if name not found in joins (admin/unlinked)
    // Actually the join might return null names if not linked, which is fine.
    // Let's stick to the join to get names if possible, or fallback.
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generate 6-Digit OTP
        $otp = rand(100000, 999999);
        $otp_hash = hash('sha256', $otp);
        
        // Update DB with OTP Hash (using reset_token_hash column)
        $update_stmt = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?");
        $update_stmt->bind_param("si", $otp_hash, $user['id']);
        
        if ($update_stmt->execute()) {
            
            // Send Email using PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'limvic2019@gmail.com';
                $mail->Password   = 'egkxivercawyaqqp';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('limvic2019@gmail.com', 'Westprime Horizon Institute');
                $mail->addAddress($email);
                
                // Embed logo
                $logo_path = 'logo.jpg';
                if(file_exists($logo_path)) {
                    $mail->addEmbeddedImage($logo_path, 'school_logo');
                }

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Verification Code - Westprime Horizon';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <div style='text-align: center; padding: 20px; background: #4169E1;'>
                            <img src='cid:school_logo' alt='Westprime Logo' style='width: 80px; height: 80px; border-radius: 50%; border: 3px solid white;'>
                            <h2 style='color: white; margin: 10px 0 0 0;'>Westprime Horizon Institute</h2>
                        </div>
                        <div style='padding: 30px; background: white; border: 1px solid #eee;'>
                            <h3 style='color: #333; margin-top: 0;'>Verification Code</h3>
                            <p style='color: #555;'>Hello,</p>
                            <p style='color: #555;'>We received a request to reset your password. Please use the following code to verify your identity:</p>
                            
                            <div style='background: #e3f2fd; padding: 15px; border-radius: 8px; text-align: center; margin: 25px 0;'>
                                <span style='font-size: 32px; font-weight: bold; color: #4169E1; letter-spacing: 5px;'>$otp</span>
                            </div>
                            
                            <p style='color: #555;'>This code will expire in 15 minutes.</p>
                            <p style='color: #999; font-size: 0.9rem;'>If you did not request this, please ignore this email.</p>
                        </div>
                    </div>
                ";

                $mail->send();
                
                // Set session and redirect
                $_SESSION['reset_email'] = $email;
                header("Location: verify_otp.php");
                exit();
                
            } catch (Exception $e) {
                // For localhost testing/demos if SMTP fails, we might want to know the code? 
                // But for security, let's just error. Or for user convenience during dev:
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}. (Dev Code: $otp)";
            }
            
        } else {
            $error = "Database error. Please try again.";
        }
    } else {
        // Uniform response
        $error = "We couldn't find an account with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/auth.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/sweetalert.js"></script>
</head>
<body>

<div class="reset-container">
    <div class="reset-box">
        <div class="logo-container">
            <img src="logo.jpg" alt="Westprime Horizon Logo">
        </div>
        
        <h2>Forgot Password?</h2>
        <p class="subtitle">Enter your registered email and we'll send you a new password.</p>
        
        <?php if ($success): ?>
            <script>
                window.SWEETALERT_FLASH = {
                    type: 'success',
                    title: 'Email Sent',
                    text: 'A verification code was sent to your email address.'
                };
            </script>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <script>
                window.SWEETALERT_FLASH = {
                    type: 'error',
                    title: 'Request Failed',
                    text: <?php echo json_encode($error); ?>
                };
            </script>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            
            <button type="submit" class="btn-primary">Send Verification</button>
        </form>
        
        <a href="login.php" class="back-link">&larr; Back to Login</a>
    </div>
</div>

</body>
</html>
