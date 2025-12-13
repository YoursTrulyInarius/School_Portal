<?php
// C:\xampp\htdocs\School_Portal\teacher\profile.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'teacher') { header("Location: ../index.php"); exit(); }
$teacher_user_id = $_SESSION['user_id'];

// Fetch Teacher Info
$t_res = $conn->query("SELECT * FROM teachers WHERE user_id='$teacher_user_id'");
$teacher = $t_res->fetch_assoc();
$teacher_id = $teacher['id'];
$user_res = $conn->query("SELECT * FROM users WHERE id='$teacher_user_id'");
$user = $user_res->fetch_assoc();

$msg = '';
$error = '';

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Update Personal Profile
    if (isset($_POST['update_profile'])) {
        $address = clean_input($_POST['address']);
        $contact = clean_input($_POST['contact']);

        // Handle Image Upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_image']['name'];
            $filesize = $_FILES['profile_image']['size'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "Invalid file format. JPG, PNG, GIF only.";
            } elseif ($filesize > 5242880) { // 5MB
                $error = "File too large. Max 5MB.";
            } else {
                $new_filename = "teacher_" . $teacher_user_id . "_" . time() . "." . $ext;
                $upload_path = "../uploads/profiles/" . $new_filename;
                
                // Create dir if needed
                if (!file_exists("../uploads/profiles/")) mkdir("../uploads/profiles/", 0777, true);

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $conn->query("UPDATE teachers SET profile_image='$new_filename' WHERE id='$teacher_id'");
                    $teacher['profile_image'] = $new_filename; 
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }

        if (empty($error)) {
            $update_sql = "UPDATE teachers SET address='$address', contact_number='$contact' WHERE id='$teacher_id'";
            if ($conn->query($update_sql)) {
                $msg = "Profile updated successfully!";
                $teacher['address'] = $address;
                $teacher['contact_number'] = $contact;
            } else {
                $error = "Error updating profile.";
            }
        }
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        
        if (empty($new_pass) || empty($confirm_pass)) {
            $error = "Please enter and confirm your new password.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "Passwords do not match.";
        } elseif (strlen($new_pass) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed_password, $teacher_user_id); 
            if ($stmt->execute()) {
                $msg = "Password changed successfully!";
            } else {
                $error = "Error changing password.";
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
    <title>My Profile - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; margin: 0; }
        .profile-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* Consistent shadow */
            flex: 1;
            min-width: 320px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .profile-img-display {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .readonly-field {
            background-color: #f9f9f9;
            color: #555;
            cursor: not-allowed;
        }
        .password-wrapper { position: relative; }
        .toggle-password-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/teacher_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; color: var(--primary-color);">My Profile</h2>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-container">
            <!-- Personal Info Card -->
            <div class="profile-card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="profile-header">
                        <?php 
                        $img_src = !empty($teacher['profile_image']) ? BASE_URL . "uploads/profiles/" . $teacher['profile_image'] : "https://via.placeholder.com/150?text=Teacher";
                        ?>
                        <div style="position: relative; display: inline-block;">
                            <img src="<?php echo $img_src; ?>" alt="Profile Picture" class="profile-img-display">
                            <label for="profile_image_input" style="position: absolute; bottom: 15px; right: 0; background: var(--secondary-color); color: white; padding: 8px; border-radius: 50%; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2);" title="Change Photo">
                                📷
                            </label>
                            <input type="file" name="profile_image" id="profile_image_input" style="display: none;" accept="image/*" onchange="this.form.submit()">
                        </div>
                        <h3><?php echo htmlspecialchars($teacher['firstname'] . ' ' . $teacher['lastname']); ?></h3>
                        <p style="color: #666; font-weight: 500;">Faculty / Teacher</p>
                        <p style="color: #888; font-size: 0.85rem;">ID: <?php echo htmlspecialchars($teacher['employee_id']); ?></p>
                    </div>

                    <h4 style="margin-bottom: 20px; color: var(--primary-dark); border-bottom: 2px solid #eee; padding-bottom: 10px;">Personal Details</h4>

                    <div class="form-group">
                        <label>Home Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Update your address"><?php echo htmlspecialchars($teacher['address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact" value="<?php echo htmlspecialchars($teacher['contact_number']); ?>" class="form-control" placeholder="Update contact number">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-block">Update Personal Info</button>
                </form>
            </div>

            <!-- Account Security Card -->
            <div class="profile-card">
                <h3 style="margin-bottom: 20px; color: var(--primary-dark);">Account Security</h3>
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control readonly-field" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control readonly-field" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>

                <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">
                
                <h4 style="margin-bottom: 15px; color: var(--primary-dark);">Change Password</h4>
                <form method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password" minlength="6">
                            <button type="button" class="toggle-password-btn" onclick="togglePassword('new_password')">👁️</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" minlength="6">
                            <button type="button" class="toggle-password-btn" onclick="togglePassword('confirm_password')">👁️</button>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-block" style="background: var(--secondary-color);">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>

</body>
</html>
