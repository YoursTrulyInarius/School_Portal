<?php
// C:\xampp\htdocs\School_Portal\admin\user_form.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
$is_edit = !empty($id);

$username = '';
$email = '';
$role = 'student'; // Default
$firstname = '';
$lastname = '';
$lrn = '';
$section_id = '';
$employee_id = '';
$address = '';
$contact_number = '';
$error = '';

// Fetch Sections for dropdown
$sections_res = $conn->query("SELECT id, section_name, grade_level FROM sections ORDER BY grade_level, section_name");

if ($is_edit) {
    // Fetch User
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $username = $user['username'];
        $email = $user['email'];
        $role = $user['role'];
        
        // Fetch Details
        if ($role == 'student') {
            $s_sql = "SELECT * FROM students WHERE user_id = '$id'";
            $s_res = $conn->query($s_sql);
            if ($s_res->num_rows > 0) {
                $student = $s_res->fetch_assoc();
                $firstname = $student['firstname'];
                $lastname = $student['lastname'];
                $lrn = $student['lrn'];
                $section_id = $student['section_id'];
                $address = $student['address'];
                $contact_number = $student['contact_number'];
            }
        } elseif ($role == 'teacher') {
            $t_sql = "SELECT * FROM teachers WHERE user_id = '$id'";
            $t_res = $conn->query($t_sql);
            if ($t_res->num_rows > 0) {
                $teacher = $t_res->fetch_assoc();
                $firstname = $teacher['firstname'];
                $lastname = $teacher['lastname'];
                $employee_id = $teacher['employee_id'];
                $address = $teacher['address'];
                $contact_number = $teacher['contact_number'];
            }
        }
    } else {
        header("Location: users.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $role = clean_input($_POST['role']);
    $password = $_POST['password'];
    
    // Additional fields
    $firstname = isset($_POST['firstname']) ? clean_input($_POST['firstname']) : '';
    $lastname = isset($_POST['lastname']) ? clean_input($_POST['lastname']) : '';
    $lrn = isset($_POST['lrn']) ? clean_input($_POST['lrn']) : '';
    $section_id = isset($_POST['section_id']) ? clean_input($_POST['section_id']) : '';
    $employee_id = isset($_POST['employee_id']) ? clean_input($_POST['employee_id']) : '';
    $address = isset($_POST['address']) ? clean_input($_POST['address']) : '';
    $contact_number = isset($_POST['contact_number']) ? clean_input($_POST['contact_number']) : '';
    
    $section_val = empty($section_id) ? "NULL" : "'$section_id'";

    if (empty($username)) { $error = "Username is required."; }
    
    if (!$error) {
        if ($is_edit) {
            $pw_update = "";
            if (!empty($password)) {
                $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
                $pw_update = ", password='$hashed_pw'";
            }
            $sql = "UPDATE users SET username='$username', email='$email', role='$role' $pw_update WHERE id='$id'";
            
            if ($conn->query($sql) === TRUE) {
                if ($role == 'student') {
                    $check = $conn->query("SELECT id FROM students WHERE user_id='$id'");
                    if ($check->num_rows > 0) {
                         $conn->query("UPDATE students SET firstname='$firstname', lastname='$lastname', lrn='$lrn', section_id=$section_val, address='$address', contact_number='$contact_number' WHERE user_id='$id'");
                    } else {
                         $conn->query("INSERT INTO students (user_id, firstname, lastname, lrn, section_id, address, contact_number) VALUES ('$id', '$firstname', '$lastname', '$lrn', $section_val, '$address', '$contact_number')");
                    }
                } elseif ($role == 'teacher') {
                    $check = $conn->query("SELECT id FROM teachers WHERE user_id='$id'");
                    if ($check->num_rows > 0) {
                         $conn->query("UPDATE teachers SET firstname='$firstname', lastname='$lastname', employee_id='$employee_id', address='$address', contact_number='$contact_number' WHERE user_id='$id'");
                    } else {
                         $conn->query("INSERT INTO teachers (user_id, firstname, lastname, employee_id, address, contact_number) VALUES ('$id', '$firstname', '$lastname', '$employee_id', '$address', '$contact_number')");
                    }
                }
                header("Location: users.php?msg=User updated");
                exit();
            } else {
                $error = "Error updating user: " . $conn->error;
            }

        } else {
            if (empty($password)) { $error = "Password is required for new users."; }
            else {
                $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, role, email) VALUES ('$username', '$hashed_pw', '$role', '$email')";
                
                if ($conn->query($sql) === TRUE) {
                    $new_user_id = $conn->insert_id;
                    if ($role == 'student') {
                        $conn->query("INSERT INTO students (user_id, firstname, lastname, lrn, section_id, address, contact_number) VALUES ('$new_user_id', '$firstname', '$lastname', '$lrn', $section_val, '$address', '$contact_number')");
                    } elseif ($role == 'teacher') {
                        $conn->query("INSERT INTO teachers (user_id, firstname, lastname, employee_id, address, contact_number) VALUES ('$new_user_id', '$firstname', '$lastname', '$employee_id', '$address', '$contact_number')");
                    }
                    header("Location: users.php?msg=User created");
                    exit();
                } else {
                    $error = "Error creating user: " . $conn->error;
                }
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
    <title><?php echo $is_edit ? 'Edit User' : 'Add New User'; ?> - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);"><?php echo $is_edit ? 'Edit User' : 'Add New User'; ?></h2>
            <a href="users.php" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Users</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <form method="POST" action="">
                <h3 style="margin-bottom: 20px; color: #444; border-bottom: 1px solid #eee; padding-bottom: 10px;">Account Information</h3>
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Username <span style="color: red;">*</span></label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="form-control">
                    </div>
                </div>
                
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Password <?php echo $is_edit ? '<small>(Leave blank to keep)</small>' : '<span style="color: red;">*</span>'; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo $is_edit ? '' : 'required'; ?>>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Role</label>
                        <select name="role" id="roleSelect" class="form-control" onchange="toggleFields()">
                            <option value="student" <?php echo $role=='student'?'selected':''; ?>>Student</option>
                            <option value="teacher" <?php echo $role=='teacher'?'selected':''; ?>>Teacher</option>
                            <option value="admin" <?php echo $role=='admin'?'selected':''; ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <div id="commonFields">
                    <h3 style="margin: 20px 0; color: #444; border-bottom: 1px solid #eee; padding-bottom: 10px;">Personal Details</h3>
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>First Name</label>
                            <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" class="form-control">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Last Name</label>
                            <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" class="form-control">
                        </div>
                    </div>
                     <div class="form-group">
                        <label>Home Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" class="form-control" placeholder="House No, Street, City">
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" class="form-control" placeholder="09xxxxxxxxx">
                    </div>
                </div>

                <div id="studentFields" style="display:none;">
                    <h3 style="margin: 20px 0; color: #444; border-bottom: 1px solid #eee; padding-bottom: 10px;">Student Information</h3>
                    <div class="form-group">
                        <label>LRN</label>
                        <input type="text" name="lrn" value="<?php echo htmlspecialchars($lrn); ?>" class="form-control" placeholder="Auto-generated if blank">
                    </div>
                    <div class="form-group">
                        <label>Section (Enrollment)</label>
                        <select name="section_id" class="form-control">
                            <option value="">-- No Section --</option>
                            <?php 
                            if ($sections_res->num_rows > 0) {
                                $sections_res->data_seek(0); // Reset pointer
                                while($sec = $sections_res->fetch_assoc()) {
                                    $sel = ($sec['id'] == $section_id) ? 'selected' : '';
                                    echo "<option value='".$sec['id']."' $sel>".$sec['grade_level']." - ".$sec['section_name']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div id="teacherFields" style="display:none;">
                    <h3 style="margin: 20px 0; color: #444; border-bottom: 1px solid #eee; padding-bottom: 10px;">Teacher Information</h3>
                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" value="<?php echo htmlspecialchars($employee_id); ?>" class="form-control">
                    </div>
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex: 1;"><?php echo $is_edit ? 'Update User' : 'Create User'; ?></button>
                    <a href="users.php" class="btn" style="background: #95a5a6; flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFields() {
    var role = document.getElementById('roleSelect').value;
    var common = document.getElementById('commonFields');
    var student = document.getElementById('studentFields');
    var teacher = document.getElementById('teacherFields');
    
    if (role === 'admin') {
        common.style.display = 'none';
        student.style.display = 'none';
        teacher.style.display = 'none';
    } else {
        common.style.display = 'block';
        if (role === 'student') {
            student.style.display = 'block';
            teacher.style.display = 'none';
        } else if (role === 'teacher') {
            student.style.display = 'none';
            teacher.style.display = 'block';
        }
    }
}
toggleFields();
</script>

</body>
</html>
