<?php
// C:\xampp\htdocs\School_Portal\admin\teacher_profile.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$teacher_id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

if (empty($teacher_id)) {
    header("Location: schedules.php");
    exit();
}

// Fetch teacher details
$sql = "SELECT t.*, u.username, u.email 
        FROM teachers t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = '$teacher_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: schedules.php");
    exit();
}

$teacher = $result->fetch_assoc();

// Fetch teacher's assigned schedules
$schedules_sql = "SELECT sch.*, 
                         c.course_name as subject,
                         c.course_code,
                         s.section_name,
                         s.grade_level as class_year
                  FROM schedules sch
                  LEFT JOIN courses c ON sch.course_id = c.id
                  LEFT JOIN sections s ON sch.section_id = s.id
                  WHERE sch.teacher_id = '$teacher_id'
                  ORDER BY s.grade_level, c.course_name";
$schedules = $conn->query($schedules_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            <h2 style="margin: 0; color: var(--primary-color);">Teacher Profile</h2>
            <a href="users.php" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Users</a>
        </div> 

        <div class="profile-header">
            <div style="display: flex; align-items: center; gap: 20px;">
                <?php if ($teacher['profile_image'] && file_exists('../uploads/profiles/' . $teacher['profile_image'])): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo htmlspecialchars($teacher['profile_image']); ?>" 
                         alt="Profile Picture" 
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid white;">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; border: 3px solid white;">
                        <?php echo strtoupper(substr($teacher['firstname'], 0, 1) . substr($teacher['lastname'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                
                <div style="flex: 1;">
                    <h2 style="margin: 0 0 5px 0; color: white;">
                        <?php echo htmlspecialchars($teacher['firstname'] . ' ' . $teacher['lastname']); ?>
                    </h2>
                    <p style="margin: 0; opacity: 0.9;">Employee ID: <?php echo htmlspecialchars($teacher['employee_id']); ?></p>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($teacher['email'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($teacher['contact_number'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($teacher['username']); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top: 0; color: var(--primary-dark);">Address</h3>
            <p style="color: #666;"><?php echo htmlspecialchars($teacher['address'] ?: 'No address on file'); ?></p>
        </div>

        <h3 style="margin: 30px 0 15px 0; color: var(--primary-dark);">Assigned Classes</h3>
        
        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--primary-color); border-bottom: 2px solid var(--primary-dark);">
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Class/Year</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Section</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Subject</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Day</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($schedules->num_rows > 0): ?>
                        <?php while($sch = $schedules->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; font-weight: 600; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($sch['class_year'] ?: '-'); ?>
                                </td>
                                <td style="padding: 15px; color: #666;"><?php echo htmlspecialchars($sch['section_name'] ?: '-'); ?></td>
                                <td style="padding: 15px; color: #333;"><?php echo htmlspecialchars($sch['subject'] ?: 'N/A'); ?></td>
                                <td style="padding: 15px;">
                                    <span style="background: #eef2f7; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; color: #555;">
                                        <?php echo htmlspecialchars($sch['day']); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #666;"><?php echo htmlspecialchars($sch['time'] ?: '-'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #888;">No classes assigned yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
