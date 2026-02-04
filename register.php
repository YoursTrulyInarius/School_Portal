<?php
// C:\xampp\htdocs\School_Portal\register.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = clean_input($_POST['role']);
    $firstname = clean_input($_POST['firstname']);
    $lastname = clean_input($_POST['lastname']);
    $address = clean_input($_POST['address']);
    $contact_number = clean_input($_POST['contact_number']);

    // Basic Validation
    if (empty($username) || empty($password) || empty($role) || empty($firstname) || empty($lastname)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^[0-9]{11}$/', $contact_number)) {
        $error = "Contact number must be exactly 11 digits and contain only numbers.";
    } else {
        // Check if username exists
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            // Create User
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role, email) VALUES ('$username', '$hashed_pw', '$role', '$email')";
            
            if ($conn->query($sql) === TRUE) {
                $user_id = $conn->insert_id;
                
                if ($role == 'student') {
                    // Auto-Generate LRN: Year + 6 random digits
                    $lrn = date('Y') . rand(100000, 999999);
                    // Ensure uniqueness (simple check, rare collision)
                    while($conn->query("SELECT id FROM students WHERE lrn='$lrn'")->num_rows > 0) {
                        $lrn = date('Y') . rand(100000, 999999);
                    }

                    $s_sql = "INSERT INTO students (user_id, firstname, lastname, lrn, address, contact_number) 
                              VALUES ('$user_id', '$firstname', '$lastname', '$lrn', '$address', '$contact_number')";
                    $conn->query($s_sql);
                    
                } elseif ($role == 'teacher') {
                    // Auto-Generate Employee ID: T + Year + 4 random digits
                    $emp_id = 'T' . date('Y') . rand(1000, 9999);
                     while($conn->query("SELECT id FROM teachers WHERE employee_id='$emp_id'")->num_rows > 0) {
                         $emp_id = 'T' . date('Y') . rand(1000, 9999);
                    }

                     $t_sql = "INSERT INTO teachers (user_id, firstname, lastname, employee_id, address, contact_number) 
                               VALUES ('$user_id', '$firstname', '$lastname', '$emp_id', '$address', '$contact_number')";
                     $conn->query($t_sql);
                }
                
                // Redirect to login without message
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Westprime Horizon</title>
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

    .auth-wrapper {
        display: flex;
        min-height: 100vh;
        background-color: #fff;
    }

    .auth-sidebar {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light)) !important;
        flex: 1.2 !important;
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
        width: 300px !important;
        max-width: 90%;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: float 4s ease-in-out infinite;
        z-index: 2;
    }

    .auth-sidebar-logo:hover {
        transform: scale(1.08) rotate(3deg);
    }

    .auth-panel {
        background: radial-gradient(circle at top right, #f8f9fa, #e9ecef);
        animation: fadeInUp 0.8s ease-out;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .auth-box {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--shadow-premium);
        padding: 40px !important;
        width: 100%;
        max-width: 650px;
        transition: transform 0.3s ease;
    }

    .auth-box:hover {
        transform: translateY(-5px);
    }

    .auth-box h2 {
        color: var(--royal-blue) !important;
        font-weight: 800;
        font-size: 2.2rem;
        letter-spacing: -1px;
        margin-bottom: 30px;
        text-align: center;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #666;
        font-weight: 600;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .form-group:focus-within label {
        color: var(--royal-blue);
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        height: 50px;
        padding-left: 15px;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: #fff;
    }

    .form-control:hover {
        border-color: var(--royal-blue-light);
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
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0, 35, 102, 0.2);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        width: 100%;
        color: white;
        margin-top: 10px;
    }

    .btn-block:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 35, 102, 0.3);
        background: linear-gradient(135deg, var(--royal-blue-light), var(--royal-blue)) !important;
    }

    .password-wrapper button:hover {
        background: rgba(0, 35, 102, 0.05);
        color: var(--royal-blue);
    }

    .auth-box a {
        color: var(--royal-blue);
        text-decoration: none;
        font-weight: 700;
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

    @media (max-width: 992px) {
        .auth-sidebar { display: none; }
        .auth-box { padding: 30px 20px !important; }
    }
</style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-sidebar">
        <div class="auth-sidebar-content">
            <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="auth-sidebar-logo">
            <h1 style="font-size: 2.8rem; margin-top: 25px; font-weight: 800; color: white;">Join Us</h1>
            <p style="font-size: 1.2rem; opacity: 0.8; font-weight: 300; color: white;">Create your account to get started.</p>
        </div>
    </div>
    
    <div class="auth-panel">
        <div class="auth-box"> 
            <div class="mobile-logo-wrapper">
                <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="mobile-logo">
            </div>
            <h2 style="text-align: center; color: var(--royal-blue); margin-bottom: 35px;">Registration</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 25px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" class="form-control" required placeholder="Sonjeev">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" class="form-control" required placeholder="Cabardo">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" required placeholder="Pagadian">
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Phone Number</label>
                    <input type="text" name="contact_number" class="form-control" maxlength="11" inputmode="numeric" 
                           onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').substr(0, 11)"
                           required placeholder="09123456789" title="Please enter exactly 11 numbers">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="johndoe">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                     <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="form-control" required placeholder="********">
                            <button type="button" class="toggle-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm</label>
                         <div class="password-wrapper">
                            <input type="password" name="confirm_password" class="form-control" required placeholder="********">
                            <button type="button" class="toggle-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-block" style="margin-top: 30px;">Register Account</button>
            </form>
            
            <p style="text-align: center; margin-top: 30px; color: #666; border-top: 1px solid #edf2f7; padding-top: 25px;">
                Already have an account? <a href="login.php" style="font-weight: 700; color: var(--royal-blue);">Log In</a>
            </p>
        </div>
    </div>
</div>


<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>
