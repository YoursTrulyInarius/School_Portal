<?php
// C:\xampp\htdocs\School_Portal\login.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    $user = verify_login($username, $password);

    if ($user) {
        // Handle Remember Me
        if (isset($_POST['remember'])) {
            // Set cookie for username to pre-fill (30 days)
            setcookie('remember_username', $username, time() + (86400 * 30), "/");
            
            // Extend session duration (30 days) to keep logged in
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + (86400 * 30), $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        } else {
            // Clear cookie if unchecked
            if (isset($_COOKIE['remember_username'])) {
                setcookie('remember_username', '', time() - 3600, "/");
            }
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') { header("Location: admin/dashboard.php"); } 
        elseif ($user['role'] == 'teacher') { header("Location: teacher/dashboard.php"); } 
        elseif ($user['role'] == 'student') { header("Location: student/dashboard.php"); } 
        else { header("Location: index.php"); }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/auth.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-sidebar">
        <div class="auth-sidebar-content">
            <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="auth-sidebar-logo">
            <h1 style="font-size: 3rem; margin-top: 30px; font-weight: 800; letter-spacing: -1px;">Welcome Back!</h1>
            <p style="font-size: 1.2rem; opacity: 0.8; font-weight: 300;">Westprime Horizon Institute Inc.</p>
        </div>
    </div>
    
    <div class="auth-panel">
        <div class="auth-box">
            <div class="mobile-logo-wrapper">
                <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="mobile-logo">
            </div>
            <h2 style="text-align: center; color: var(--royal-blue); margin-bottom: 45px; font-weight: 800; font-size: 2.5rem; letter-spacing: -1px;">Sign In</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="padding: 15px; margin-bottom: 25px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" style="color: #666; font-weight: 600; margin-bottom: 10px; display: block;">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" value="<?php echo isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : ''; ?>" required>
                </div>
                
                <div class="form-group" style="margin-top: 25px;">
                    <label for="password" style="color: #666; font-weight: 600; margin-bottom: 10px; display: block;">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0 30px 0;">
                    <label style="font-size: 0.95rem; color: #555; display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="remember" style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;"> Remember Me
                    </label>
                    <a href="forgot_password.php" style="font-size: 0.95rem; color: var(--royal-blue); font-weight: 600;">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-block" style="width: 100%; font-size: 1.1rem;">Sign In</button>
            </form>
            
            <div style="margin-top: 40px; border-top: 1px solid #edf2f7; padding-top: 30px; text-align: center;">
                <p style="margin-bottom: 15px; color: #666;">
                    New student? <a href="enroll.php" style="font-weight: 700; color: var(--royal-blue);">Enroll here</a>
                </p>
                <p style="color: #666;">
                    No Account? <a href="register.php" style="font-weight: 700; color: #17a2b8;">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>


</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>
