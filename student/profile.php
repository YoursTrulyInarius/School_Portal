<?php
// C:\xampp\htdocs\School_Portal\student\profile.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') { header("Location: ../index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Update Personal Info
    if (isset($_POST['update_profile'])) {
        $address = clean_input($_POST['address']);
        $contact_number = clean_input($_POST['contact_number']);
        
        // Handle File Upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_image']['name'];
            $filesize = $_FILES['profile_image']['size'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            } elseif ($filesize > 5000000) { 
                $error = "File size too large. Max 5MB.";
            } else {
                $upload_dir = '../uploads/profiles/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $new_filename = "student_" . $user_id . "_" . time() . "." . $ext;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $new_filename)) {
                    $profile_image = $new_filename;
                } else {
                    $error = "Failed to upload file.";
                }
            }
        }

        if (empty($error)) {
            if ($profile_image) {
                $stmt = $conn->prepare("UPDATE students SET address=?, contact_number=?, profile_image=? WHERE user_id=?");
                $stmt->bind_param("ssss", $address, $contact_number, $profile_image, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE students SET address=?, contact_number=? WHERE user_id=?");
                $stmt->bind_param("sss", $address, $contact_number, $user_id);
            }
            if ($stmt->execute()) {
                $success = "Personal profile updated successfully!";
            } else {
                $error = "Error updating profile: " . $conn->error;
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
            $stmt->bind_param("si", $hashed_password, $user_id); // users.id is same as user_id session
            if ($stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error changing password.";
            }
        }
    }
}

// Fetch Current Data (Join users to get username/email, and sections for grade info)
$student = $conn->query("SELECT s.*, u.username, u.email, sec.grade_level, sec.section_name 
                         FROM students s 
                         JOIN users u ON s.user_id = u.id 
                         LEFT JOIN sections sec ON s.section_id = sec.id
                         WHERE s.user_id='$user_id'")->fetch_assoc();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .profile-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            flex: 1;
            min-width: 300px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-img-display {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--primary-color);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/student_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; color: var(--primary-color);">My Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
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
                        $img_src = $student['profile_image'] ? BASE_URL . "uploads/profiles/" . $student['profile_image'] : "https://via.placeholder.com/150";
                        ?>
                        <img src="<?php echo $img_src; ?>" alt="Profile Picture" class="profile-img-display">
                        <h3><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></h3>
                        <p style="color: #666; font-weight: 600;">
                            <?php echo htmlspecialchars($student['grade_level'] ? $student['grade_level'] . ' - ' . $student['section_name'] : 'Not Assigned'); ?>
                        </p>
                        <p style="color: #888; font-size: 0.9rem;">LRN: <?php echo htmlspecialchars($student['lrn']); ?></p>
                    </div>

                    <h4 style="margin-bottom: 20px; color: var(--primary-dark); border-bottom: 2px solid #eee; padding-bottom: 10px;">Personal Details</h4>

                    <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                        <small style="color: #999;">Max 5MB. JPG, PNG, GIF.</small>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($student['contact_number']); ?>" required>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-block">Update Personal Info</button>
                </form>
            </div>

            <!-- Account Security Card -->
            <div class="profile-card">
                <h3 style="margin-bottom: 20px; color: var(--primary-dark);">Account Security</h3>
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['username']); ?>" readonly style="background: #f9f9f9; color: #555;">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" readonly style="background: #f9f9f9; color: #555;">
                </div>

                <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">
                
                <h4 style="margin-bottom: 15px; color: var(--primary-dark);">Change Password</h4>
                <form method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <div class="password-wrapper" style="position: relative;">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password" minlength="6">
                            <button type="button" class="toggle-password-btn" onclick="togglePassword('new_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">
                                👁️
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="password-wrapper" style="position: relative;">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" minlength="6">
                            <button type="button" class="toggle-password-btn" onclick="togglePassword('confirm_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">
                                👁️
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-block" style="background: var(--secondary-color);">Update Password</button>
                </form>
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
</div>

</body>
</html>
