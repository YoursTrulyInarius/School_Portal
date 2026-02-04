<?php
// C:\xampp\htdocs\School_Portal\forgot_password.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

// PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Manual require for PHPMailer since autoload might be failing or missing mapping
require 'admin/vendor/PHPMailer/src/Exception.php';
require 'admin/vendor/PHPMailer/src/PHPMailer.php';
require 'admin/vendor/PHPMailer/src/SMTP.php';

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
                        <div style='text-align: center; padding: 20px; background: linear-gradient(135deg, #0056b3 0%, #004494 100%);'>
                            <img src='cid:school_logo' alt='Westprime Logo' style='width: 80px; height: 80px; border-radius: 50%; border: 3px solid white;'>
                            <h2 style='color: white; margin: 10px 0 0 0;'>Westprime Horizon Institute</h2>
                        </div>
                        <div style='padding: 30px; background: white; border: 1px solid #eee;'>
                            <h3 style='color: #333; margin-top: 0;'>Verification Code</h3>
                            <p style='color: #555;'>Hello,</p>
                            <p style='color: #555;'>We received a request to reset your password. Please use the following code to verify your identity:</p>
                            
                            <div style='background: #e3f2fd; padding: 15px; border-radius: 8px; text-align: center; margin: 25px 0;'>
                                <span style='font-size: 32px; font-weight: bold; color: #0056b3; letter-spacing: 5px;'>$otp</span>
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
        :root {
            --royal-blue: #002366;
            --royal-blue-light: #003399;
            --royal-blue-dark: #001a4d;
            --pure-white: #ffffff;
            --soft-white: #f8f9fa;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-premium: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: radial-gradient(circle at top right, #f8f9fa, #e9ecef);
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0; 
            overflow-x: hidden;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .reset-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            animation: fadeInUp 0.8s ease-out;
        }

        .reset-box { 
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--shadow-premium);
            padding: 50px 40px; 
            text-align: center;
            transition: transform 0.3s ease;
        }

        .reset-box:hover {
            transform: translateY(-5px);
        }
        
        .logo-container {
            margin-bottom: 30px;
        }
        
        .logo-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--royal-blue);
            padding: 5px;
            background: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            animation: float 4s ease-in-out infinite;
        }
        
        h2 {
            color: var(--royal-blue);
            font-weight: 800;
            margin: 0 0 15px 0;
            font-size: 1.8rem;
            letter-spacing: -1px;
        }
        
        p.subtitle {
            color: #666;
            font-size: 1rem;
            margin: 0 0 35px 0;
            line-height: 1.6;
            font-weight: 300;
        }
        
        .form-group {
            text-align: left;
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #555;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .form-group:focus-within .form-label {
            color: var(--royal-blue);
        }
        
        .form-control { 
            width: 100%; 
            padding: 14px 18px; 
            border: 2px solid #e9ecef; 
            border-radius: 12px; 
            font-size: 1rem;
            box-sizing: border-box; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }
        
        .form-control:hover {
            border-color: var(--royal-blue-light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--royal-blue);
            background: white;
            box-shadow: 0 0 0 4px rgba(0, 35, 102, 0.1);
            transform: scale(1.02);
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light)); 
            color: white; 
            border: none; 
            padding: 16px; 
            border-radius: 12px; 
            width: 100%; 
            font-size: 1.1rem; 
            cursor: pointer; 
            font-weight: 700; 
            box-shadow: 0 8px 20px rgba(0, 35, 102, 0.2);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        
        .btn-primary:hover { 
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 35, 102, 0.3);
            background: linear-gradient(135deg, var(--royal-blue-light), var(--royal-blue));
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 35px;
            color: var(--royal-blue);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            position: relative;
            transition: all 0.3s ease;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
            width: 100%;
        }
        
        .back-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--royal-blue);
            transition: width 0.3s ease;
        }

        .back-link:hover::after {
            width: 30%;
        }
        
        .alert { 
            padding: 15px; 
            margin-bottom: 25px; 
            border-radius: 10px; 
            font-size: 0.95rem; 
            text-align: left;
            border: none;
        }
        .alert-success { background: rgba(40, 167, 69, 0.1); color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: rgba(220, 53, 69, 0.1); color: #721c24; border-left: 4px solid #dc3545; }
    </style>
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
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
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
