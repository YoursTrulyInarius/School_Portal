<?php
// C:\xampp\htdocs\School_Portal\teacher\dashboard.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'teacher') { header("Location: ../index.php"); exit(); }

$teacher_user_id = $_SESSION['user_id'];
// Get Teacher ID from teachers table
$t_res = $conn->query("SELECT id, firstname FROM teachers WHERE user_id='$teacher_user_id'");
if ($t_res->num_rows == 0) { die("Teacher profile not found."); }
$teacher = $t_res->fetch_assoc();
$teacher_id = $teacher['id'];
$_SESSION['teacher_id'] = $teacher_id;

// Fetch Classes/Schedules assigned to this teacher
// Fetch Classes/Schedules assigned to this teacher
$sql = "SELECT sch.id as schedule_id, 
               c.course_name as subject, 
               c.course_code,
               s.section_name, 
               s.grade_level as class_year,
               sch.day, 
               sch.time
        FROM schedules sch
        LEFT JOIN courses c ON sch.course_id = c.id
        LEFT JOIN sections s ON sch.section_id = s.id
        WHERE sch.teacher_id = '$teacher_id'
        ORDER BY sch.day, sch.time";
$classes_res = $conn->query($sql);

// Fetch Announcements
$ann_sql = "SELECT * FROM announcements WHERE target_audience IN ('all', 'teacher') ORDER BY created_at DESC LIMIT 5";
$ann_res = $conn->query($ann_sql);

// Calculate stats
$total_classes = $classes_res->num_rows;
$classes_res->data_seek(0);
$unique_days = [];
while($row = $classes_res->fetch_assoc()) {
    $unique_days[$row['day']] = true;
}
$days_with_classes = count($unique_days);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; margin: 0; }
        
        /* Welcome Header */
        .welcome-header {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 86, 179, 0.1);
        }
        .welcome-header h2 {
            margin: 0 0 6px 0;
            color: #0056b3;
            font-size: 1.4rem;
        }
        .welcome-header p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        /* Stats Card */
        .stats-card {
            background: linear-gradient(135deg, #0056b3, #0077cc);
            color: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 86, 179, 0.3);
        }
        .stats-card.green {
            background: linear-gradient(135deg, #28a745, #34c759);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .stats-card h3 {
            margin: 0 0 10px 0;
            font-size: 1rem;
            font-weight: 500;
            opacity: 0.9;
        }
        .stats-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        .stats-card .label {
            margin: 8px 0 0 0;
            font-size: 0.85rem;
            opacity: 0.85;
        }
        
        /* Regular Card */
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #0056b3;
        }
        .card h3 {
            color: #0056b3;
            font-size: 1.15rem;
            margin: 0 0 15px 0;
        }
        
        /* Full width card */
        .card-full {
            grid-column: 1 / -1;
        }
        
        /* Announcement list */
        .announcement-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .announcement-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .announcement-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .announcement-title {
            color: #0056b3;
            font-weight: 600;
        }
        .announcement-date {
            font-size: 0.8rem;
            color: #999;
        }
        .announcement-content {
            margin-top: 8px;
            color: #555;
            font-size: 0.9rem;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #0056b3, #0077cc);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: linear-gradient(135deg, #004494, #0056b3);
            transform: translateY(-2px);
            color: white;
        }
        
        /* MOBILE RESPONSIVE */
        @media screen and (max-width: 768px) {
            .dashboard-grid {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .card-full {
                grid-column: auto;
            }
            .welcome-header {
                padding: 16px;
                margin-bottom: 16px;
            }
            .welcome-header h2 {
                font-size: 1.2rem;
            }
            .stats-card {
                padding: 20px;
            }
            .stats-card .number {
                font-size: 2rem;
            }
            .card {
                padding: 18px;
            }
            .quick-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                text-align: center;
            }
            .announcement-title {
                display: block;
            }
            .announcement-date {
                display: block;
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/teacher_sidebar.php'; ?>

    <div class="main-content">
        <div class="welcome-header">
            <h2>Welcome, Teacher <?php echo htmlspecialchars($teacher['firstname']); ?>!</h2>
            <p>Manage your classes and grades efficiently.</p>
        </div>

        <div class="dashboard-grid">
            <!-- Stats Cards -->
            <div class="stats-card">
                <h3>Total Classes</h3>
                <p class="number"><?php echo $total_classes; ?></p>
                <p class="label">Assigned to you</p>
            </div>
            
            <div class="stats-card green">
                <h3>This Week</h3>
                <p class="number"><?php echo $days_with_classes; ?></p>
                <p class="label">Days with classes</p>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="schedule.php" class="btn">View Schedule</a>
                    <a href="grades.php" class="btn">Manage Grades</a>
                </div>
            </div>
            
            <!-- Announcements -->
            <div class="card">
                <h3>Announcements</h3>
                <?php if ($ann_res->num_rows > 0): ?>
                    <ul class="announcement-list">
                    <?php while($ann = $ann_res->fetch_assoc()): ?>
                        <li class="announcement-item">
                            <span class="announcement-title"><?php echo htmlspecialchars($ann['title']); ?></span>
                            <span class="announcement-date"><?php echo date('M d', strtotime($ann['created_at'])); ?></span>
                            <p class="announcement-content"><?php echo nl2br(htmlspecialchars(substr($ann['content'], 0, 100))) . (strlen($ann['content'])>100?'...':''); ?></p>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #888;">No new announcements.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
