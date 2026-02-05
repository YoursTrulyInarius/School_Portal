<?php
// C:\xampp\htdocs\School_Portal\admin\users.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$success = '';
$error = '';
$student_password = null;

// Handle password reset for students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = clean_input($_POST['user_id']);
    
    // Generate new random password
    $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update user password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        $student_password = $new_password;
        $success = "Password reset successfully!";
    } else {
        $error = "Error resetting password.";
    }
}

$role_filter = isset($_GET['role']) ? clean_input($_GET['role']) : '';
$where_clause = "";
if ($role_filter) {
    $where_clause = "WHERE u.role = '$role_filter'";
}

$sql = "SELECT u.*, 
               t.profile_image as teacher_pic, 
               s.profile_image as student_pic
        FROM users u
        LEFT JOIN teachers t ON u.id = t.user_id AND u.role = 'teacher'
        LEFT JOIN students s ON u.id = s.user_id AND u.role = 'student'
        $where_clause 
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; margin: 0; }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h2 {
            margin: 0;
            color: #4169E1;
            font-size: 1.5rem;
        }
        
        .filter-bar {
            margin-bottom: 20px;
        }
        .filter-btns {
            display: inline-flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            color: white;
            transition: all 0.2s;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }
        .data-table th {
            padding: 15px;
            text-align: left;
            background: #4169E1; /* Darker background for visibility */
            color: white; /* White text for contrast */
            border-bottom: 2px solid #4169E1;
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
        }
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }
        .data-table tr:hover td {
            background: #f9f9f9;
        }
        
        .action-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%; /* Circle shape */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4169E1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
        }
        
        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .page-header h2 {
                font-size: 1.3rem;
            }
            
            .filter-btns {
                gap: 4px;
            }
            .filter-btn {
                padding: 5px 10px;
                font-size: 0.75rem;
            }
            
            .data-table {
                min-width: 480px;
            }
            .data-table th,
            .data-table td {
                padding: 8px 6px;
                font-size: 0.75rem;
            }
            
            .action-btns {
                gap: 3px;
            }
            .action-btn {
                padding: 5px 8px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);">User Management <?php echo $role_filter ? '('.ucfirst($role_filter).')' : ''; ?></h2>
            <a href="user_form.php" class="btn">Add New User</a>
        </div>

        <?php if ($success): ?>
            <div class="alert success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($student_password): ?>
            <div style="background: #e3f2fd; border: 2px solid #2196f3; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="color: #1976d2; margin: 0 0 10px 0;">🔑 New Password Generated</h3>
                <p style="margin: 5px 0; font-size: 1.2rem;">
                    <strong>Password:</strong> 
                    <code style="background: white; padding: 8px 15px; border-radius: 4px; color: #1976d2; font-weight: 700; font-size: 1.3rem;"><?php echo htmlspecialchars($student_password); ?></code>
                </p>
                <p style="margin: 10px 0 0 0; color: #555; font-size: 0.9rem;">Share this password with the student.</p>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 25px;">
            <span style="font-weight: 600; margin-right: 10px; color: #555;">Filter by Role:</span>
            <div style="display: inline-flex; gap: 5px;">
                <a href="users.php" class="btn" style="background: <?php echo !$role_filter ? 'var(--primary-color)' : '#95a5a6'; ?>; font-size: 0.85rem; padding: 5px 12px;">All</a>
                <a href="users.php?role=teacher" class="btn" style="background: <?php echo $role_filter == 'teacher' ? 'var(--primary-color)' : '#95a5a6'; ?>; font-size: 0.85rem; padding: 5px 12px;">Teachers</a>
                <a href="users.php?role=student" class="btn" style="background: <?php echo $role_filter == 'student' ? 'var(--primary-color)' : '#95a5a6'; ?>; font-size: 0.85rem; padding: 5px 12px;">Students</a>
                <a href="users.php?role=admin" class="btn" style="background: <?php echo $role_filter == 'admin' ? 'var(--primary-color)' : '#95a5a6'; ?>; font-size: 0.85rem; padding: 5px 12px;">Admins</a>
            </div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table style="width: 100%; border-collapse: collapse; min-width: 480px;">
                <thead>
                    <tr style="background: #4169E1; border-bottom: 2px solid #4169E1;">
                        <th style="padding: 12px 10px; text-align: left; color: white; font-weight: 600;">User</th>
                        <th style="padding: 12px 10px; text-align: left; color: white; font-weight: 600;">Role</th>
                        <th style="padding: 12px 10px; text-align: left; color: white; font-weight: 600;">Email</th>
                        <th style="padding: 12px 10px; text-align: left; color: white; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee; transition: background 0.1s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='white'">
                                <td style="padding: 10px 8px; font-weight: 500; color: #333;">
                                    <?php
                                    // Get the related teacher or student ID for profile link
                                    $profile_link = '#';
                                    $profile_pic = null;
                                    
                                    if ($row['role'] == 'teacher') {
                                        $teacher_query = $conn->query("SELECT id FROM teachers WHERE user_id = " . $row['id']);
                                        if ($teacher_query->num_rows > 0) {
                                            $teacher_data = $teacher_query->fetch_assoc();
                                            $profile_link = "teacher_profile.php?id=" . $teacher_data['id'];
                                        }
                                        $profile_pic = $row['teacher_pic'];
                                    } elseif ($row['role'] == 'student') {
                                        $student_query = $conn->query("SELECT id FROM students WHERE user_id = " . $row['id']);
                                        if ($student_query->num_rows > 0) {
                                            $student_data = $student_query->fetch_assoc();
                                            $profile_link = "student_profile.php?id=" . $student_data['id'];
                                        }
                                        $profile_pic = $row['student_pic'];
                                    }
                                    
                                    // Get initials for fallback
                                    $initials = strtoupper(substr($row['username'], 0, 2));
                                    ?>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php if ($profile_pic && file_exists('../uploads/profiles/' . $profile_pic)): ?>
                                            <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo htmlspecialchars($profile_pic); ?>" 
                                                 alt="Profile" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0e0e0; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; color: #666; font-weight: 600;">
                                                <?php echo $initials; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($profile_link != '#'): ?>
                                            <a href="<?php echo $profile_link; ?>" style="color: #333; text-decoration: none; font-weight: 500;">
                                                <?php echo htmlspecialchars($row['username']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($row['username']); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 15px;">
                                    <span style="padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; background: 
                                        <?php 
                                            if($row['role'] == 'admin') echo '#e74c3c; color: white;';
                                            elseif($row['role'] == 'teacher') echo '#3498db; color: white;';
                                            else echo '#2ecc71; color: white;';
                                        ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #666; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($row['email']); ?>">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </td>
                                <td style="padding: 15px;">
                                    <div class="action-btns">
                                        <a href="user_form.php?id=<?php echo $row['id']; ?>" class="action-btn" style="background: var(--primary-color);" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($row['role'] == 'student'): ?>

                                        <?php endif; ?>
                                        
                                        <a href="user_delete.php?id=<?php echo $row['id']; ?>" class="action-btn" style="background: #e74c3c;" title="Delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 30px; text-align: center; color: #888;">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
