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
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-premium: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    body { 
        font-family: 'Poppins', sans-serif; 
        background-color: var(--soft-white);
        overflow-x: hidden;
    }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-50px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes pulse-glow {
        0% { box-shadow: 0 0 0 0 rgba(0, 35, 102, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(0, 35, 102, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 35, 102, 0); }
    }

    .auth-wrapper {
        display: flex;
        min-height: 100vh;
        background-color: #fff;
    }

    .auth-sidebar {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light)) !important;
        flex: 1.5 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        animation: slideInLeft 1s ease-out;
    }

    .auth-sidebar::after {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
        top: -50%;
        left: -50%;
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .auth-sidebar-logo {
        width: 380px !important;
        max-width: 90%;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: float 4s ease-in-out infinite;
        z-index: 2;
    }

    .auth-sidebar-logo:hover {
        transform: scale(1.08) rotate(3deg);
        filter: drop-shadow(0 15px 30px rgba(0,0,0,0.3));
    }

    .auth-panel {
        background: radial-gradient(circle at top right, #f8f9fa, #e9ecef);
        animation: fadeInUp 0.8s ease-out;
    }

    .auth-box {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--shadow-premium);
        padding: 50px 40px !important;
        transition: transform 0.3s ease;
    }

    .auth-box:hover {
        transform: translateY(-5px);
    }

    .auth-box h2 {
        color: var(--royal-blue) !important;
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .auth-box h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 4px;
        background: var(--royal-blue);
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .auth-box:hover h2::after {
        width: 80px;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        height: 55px;
        padding-left: 20px;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control:hover {
        border-color: var(--royal-blue-light);
        background-color: #fff;
    }

    .form-control:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 4px rgba(0, 35, 102, 0.1);
        transform: scale(1.01);
    }

    .btn-block {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light)) !important;
        border: none;
        height: 55px;
        border-radius: 12px !important;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0, 35, 102, 0.2);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-block:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 35, 102, 0.3);
        background: linear-gradient(135deg, var(--royal-blue-light), var(--royal-blue)) !important;
    }

    .btn-block:active {
        transform: translateY(-1px);
    }

    /* Floating input label effect style */
    .form-group label {
        transition: color 0.3s ease;
    }
    .form-group:focus-within label {
        color: var(--royal-blue) !important;
    }

    .password-wrapper button {
        transition: all 0.3s ease;
        padding: 5px;
        border-radius: 50%;
    }

    .password-wrapper button:hover {
        background: rgba(0, 35, 102, 0.05);
        color: var(--royal-blue);
        transform: translateY(-50%) scale(1.1);
    }

    .auth-box a {
        text-decoration: none;
        position: relative;
        transition: all 0.3s ease;
    }

    .auth-box a::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: currentColor;
        transition: width 0.3s ease;
    }

    .auth-box a:hover::after {
        width: 100%;
    }

    .alert-danger {
        border: none;
        border-left: 4px solid var(--error-color);
        background: rgba(220, 53, 69, 0.05);
        border-radius: 8px;
        animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
    }

    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }

    @media (max-width: 992px) {
        .auth-box {
            padding: 40px 25px !important;
            margin: 20px;
        }
    }
</style>
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
