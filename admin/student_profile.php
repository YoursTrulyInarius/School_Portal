<?php
// C:\xampp\htdocs\School_Portal\admin\student_profile.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$student_id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

if (empty($student_id)) {
    header("Location: users.php");
    exit();
}

// Fetch student details
$sql = "SELECT s.*, u.username, u.email, sec.section_name, sec.grade_level
        FROM students s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        WHERE s.id = '$student_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: users.php");
    exit();
}

$student = $result->fetch_assoc();

// Fetch student's grades
$grades_sql = "SELECT g.*, c.course_name as subject
               FROM grades g
               LEFT JOIN courses c ON g.course_id = c.id
               WHERE g.student_id = '$student_id'
               ORDER BY g.id DESC
               LIMIT 10";
$grades = $conn->query($grades_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .info-item {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 6px;
        }
        .info-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            word-break: break-word;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);">Student Profile</h2>
            <a href="users.php?role=student" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Users</a>
        </div>

        <div class="profile-header">
            <div style="display: flex; align-items: center; gap: 20px;">
                <?php if ($student['profile_image'] && file_exists('../uploads/profiles/' . $student['profile_image'])): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo htmlspecialchars($student['profile_image']); ?>" 
                         alt="Profile Picture" 
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid white;">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; border: 3px solid white;">
                        <?php echo strtoupper(substr($student['firstname'], 0, 1) . substr($student['lastname'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                
                <div style="flex: 1;">
                    <h2 style="margin: 0 0 5px 0; color: white;">
                        <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
                    </h2>
                    <p style="margin: 0; opacity: 0.9;">LRN: <?php echo htmlspecialchars($student['lrn']); ?></p>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['email'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['contact_number'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Section</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['section_name'] ?: 'Not Assigned'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Grade Level</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['grade_level'] ?: 'Not Assigned'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['username']); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top: 0; color: var(--primary-dark);">Address</h3>
            <p style="color: #666;"><?php echo htmlspecialchars($student['address'] ?: 'No address on file'); ?></p>
        </div>

        <h3 style="margin: 30px 0 15px 0; color: var(--primary-dark);">Recent Grades</h3>
        
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table style="width: 100%; border-collapse: collapse; min-width: 300px;">
                <thead>
                    <tr style="background: var(--primary-color); border-bottom: 2px solid var(--primary-dark);">
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Subject</th>
                        <th style="padding: 15px; text-align: center; color: white; font-weight: 600;">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($grades->num_rows > 0): ?>
                        <?php while($grade = $grades->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; font-weight: 600; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($grade['subject'] ?: 'Unknown Subject'); ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <span style="background: #2ecc71; color: white; padding: 5px 12px; border-radius: 15px; font-weight: 700;">
                                        <?php echo number_format($grade['grade'], 2); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="padding: 30px; text-align: center; color: #888;">No grades recorded yet.</td>
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
