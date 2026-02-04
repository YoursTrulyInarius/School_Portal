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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --royal-blue: #002366;
        --royal-blue-light: #003399;
        --royal-blue-dark: #001a4d;
        --pure-white: #ffffff;
        --soft-white: #f8f9fa;
    }

    body { 
        font-family: 'Poppins', sans-serif; 
        background-color: var(--soft-white);
    }

    .auth-sidebar {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light)) !important;
        flex: 1.5 !important;
    }

    .auth-sidebar-logo {
        width: 400px !important; /* Bigger Logo */
        max-width: 90%;
        transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .auth-sidebar-logo:hover {
        transform: scale(1.05) rotate(2deg);
    }

    .auth-panel {
        background-color: var(--pure-white) !important;
    }

    .auth-box h2 {
        color: var(--royal-blue) !important;
    }

    .form-control {
        transition: all 0.3s ease;
        border: 2px solid #eee;
    }

    .form-control:hover {
        border-color: var(--royal-blue-light);
        box-shadow: 0 0 8px rgba(0, 35, 102, 0.1);
    }

    .form-control:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 12px rgba(0, 35, 102, 0.2);
        transform: translateY(-2px);
    }

    .btn-block {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light)) !important;
        border: none;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .btn-block::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transition: all 0.4s ease;
        z-index: -1;
    }

    .btn-block:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 35, 102, 0.3);
    }

    .btn-block:hover::before {
        width: 100%;
    }

    .btn-block:active {
        transform: translateY(-1px);
    }

    .password-wrapper button {
        transition: color 0.3s ease, transform 0.2s ease;
    }

    .password-wrapper button:hover {
        color: var(--royal-blue);
        transform: translateY(-50%) scale(1.1);
    }

    input[type="checkbox"] {
        accent-color: var(--royal-blue);
    }

    .auth-box a {
        color: var(--royal-blue);
        transition: all 0.3s ease;
        display: inline-block;
    }

    .auth-box a:hover {
        color: var(--royal-blue-light);
        text-decoration: underline;
        transform: translateX(3px);
    }

    .alert-danger {
        border-left: 5px solid var(--royal-blue);
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    @media (max-width: 992px) {
        .mobile-logo-wrapper .mobile-logo {
            width: 120px;
            height: 120px;
        }
    }
</style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-sidebar">
        <div class="auth-sidebar-content">
            <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="auth-sidebar-logo">
            <h1 style="font-size: 2.8rem; margin-top: 20px;">Welcome Back!</h1>
            <p style="font-size: 1.4rem; opacity: 0.9;">Westprime Horizon Institute Inc.</p>
        </div>
    </div>
    
    <div class="auth-panel">
        <div class="auth-box">
            <div class="mobile-logo-wrapper">
                <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="mobile-logo">
            </div>
            <h2 style="text-align: center; color: var(--royal-blue); margin-bottom: 40px; font-weight: 700; font-size: 2.2rem;">Sign In</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" style="color: var(--royal-blue); font-weight: 600;">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" value="<?php echo isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password" style="color: var(--royal-blue); font-weight: 600;">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <label style="font-size: 0.95rem; color: #444; display: flex; align-items: center; cursor: pointer; transition: color 0.3s ease;">
                            <input type="checkbox" name="remember" style="margin-right: 8px;"> Remember Me
                        </label>
                        <a href="forgot_password.php" style="font-size: 0.9rem; color: var(--royal-blue); text-decoration: none; font-weight: 500;">Forgot Password?</a>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-block" style="padding: 15px; font-size: 1.1rem; margin-top: 10px;">Sign In</button>
            </form>
            
            <div style="margin-top: 35px; border-top: 1px solid #eee; padding-top: 25px;">
                <p style="text-align: center; margin-bottom: 12px; color: #555;">
                    New student? <a href="enroll.php" style="font-weight: 700; color: var(--royal-blue);">Enroll here</a>
                </p>
                <p style="text-align: center; color: #555;">
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
