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
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-sidebar">
        <div class="auth-sidebar-content">
            <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="auth-sidebar-logo">
            <h1>Join Us</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">Create your account to get started.</p>
        </div>
    </div>
    
    <div class="auth-panel">
        <div class="auth-box"> 
            <div class="mobile-logo-wrapper">
                <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="mobile-logo">
            </div>
            <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 20px;">Registration</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>First Name</label>
                        <input type="text" name="firstname" class="form-control" required placeholder="Sonjeev">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Last Name</label>
                        <input type="text" name="lastname" class="form-control" required placeholder="Cabardo">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" required placeholder="Pagadian">
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="contact_number" class="form-control" maxlength="11" inputmode="numeric" 
                           onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').substr(0, 11)"
                           required placeholder="09123456789" title="Please enter exactly 11 numbers">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="johndoe">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 15px;">
                     <div class="form-group" style="flex: 1;">
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
                    <div class="form-group" style="flex: 1;">
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
                
                <button type="submit" class="btn btn-block">Register Account</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; color: #666;">
                Already have an account? <a href="login.php" style="font-weight: 600;">Log In</a>
            </p>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>
