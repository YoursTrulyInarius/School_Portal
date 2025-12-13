<?php
// C:\xampp\htdocs\School_Portal\student\dashboard.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') { header("Location: ../index.php"); exit(); }

$student_user_id = $_SESSION['user_id'];

$s_res = $conn->query("SELECT s.*, sec.grade_level, sec.section_name 
                       FROM students s 
                       LEFT JOIN sections sec ON s.section_id = sec.id 
                       WHERE s.user_id='$student_user_id'");
if ($s_res->num_rows == 0) { die("Student profile not found."); }
$student = $s_res->fetch_assoc();
$student_id = $student['id'];
$section_id = $student['section_id'];

$ann_sql = "SELECT * FROM announcements WHERE target_audience IN ('all', 'student') ORDER BY created_at DESC LIMIT 5";
$ann_res = $conn->query($ann_sql);

$class_display = ($student['grade_level'] && $student['section_name']) 
                 ? $student['grade_level'] . ' - ' . $student['section_name'] 
                 : "Not Assigned";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Westprime Horizon</title>
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
        
        /* Cards */
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
            margin: 0 0 10px 0;
        }
        .card p {
            color: #666;
            font-size: 0.9rem;
            margin: 0 0 15px 0;
        }
        
        /* Full width card */
        .card-full {
            grid-column: 1 / -1;
        }
        
        /* Button container */
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Buttons */
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
        .btn-success {
            background: linear-gradient(135deg, #28a745, #34c759);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #1e7e34, #28a745);
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
        }
        .announcement-title {
            color: #0056b3;
            font-size: 1.1rem;
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
            .card {
                padding: 18px;
            }
            .btn-group {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                text-align: center;
                padding: 14px 20px;
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
    <?php include '../includes/student_sidebar.php'; ?>

    <div class="main-content">
        <div class="welcome-header">
            <h2>Welcome, <?php echo htmlspecialchars($student['firstname']); ?>!</h2>
            <p>
                <strong>LRN:</strong> <?php echo $student['lrn']; ?> | <strong>Class:</strong> <?php echo $class_display; ?>
            </p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>My Schedule</h3>
                <p>View your weekly class schedule.</p>
                <a href="my_schedule.php" class="btn">View Schedule</a>
            </div>
            
            <div class="card">
                <h3>My Grades</h3>
                <p>Check your academic performance.</p>
                <div class="btn-group">
                    <a href="grades.php" class="btn">View Grades</a>
                    <a href="attendance.php" class="btn btn-success">Attendance</a>
                </div>
            </div>
            
            <div class="card card-full">
                <h3>Announcements</h3>
                <?php if ($ann_res->num_rows > 0): ?>
                    <ul class="announcement-list">
                    <?php while($ann = $ann_res->fetch_assoc()): ?>
                        <li class="announcement-item">
                            <span class="announcement-title"><?php echo htmlspecialchars($ann['title']); ?></span>
                            <span class="announcement-date"><?php echo date('M d, Y', strtotime($ann['created_at'])); ?></span>
                            <p class="announcement-content"><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No new announcements.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
