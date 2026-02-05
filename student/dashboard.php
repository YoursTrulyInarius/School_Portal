<?php
// C:\xampp\htdocs\School_Portal\student\dashboard.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_user_id = $_SESSION['user_id'];

$s_res = $conn->query("SELECT s.*, sec.grade_level, sec.section_name 
                       FROM students s 
                       LEFT JOIN sections sec ON s.section_id = sec.id 
                       WHERE s.user_id='$student_user_id'");
if ($s_res->num_rows == 0) {
    die("Student profile not found.");
}
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
    /* Dashboard Enhancement Styles */
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stat-icon.blue {
            background: #e3f2fd;
            color: #1976d2;
        }

        .stat-icon.green {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .stat-icon.orange {
            background: #fff3e0;
            color: #ef6c00;
        }

        .stat-icon.red {
            background: #ffebee;
            color: #c62828;
        }

        .stat-info h4 {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-info .value {
            margin: 5px 0 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
            border-left: 2px solid #e0e0e0;
            padding-left: 20px;
        }

        .timeline-item:last-child {
            border-left-color: transparent;
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4169E1;
            border: 2px solid white;
        }

        .timeline-time {
            font-size: 0.85rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .timeline-title {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .timeline-desc {
            font-size: 0.9rem;
            color: #666;
        }

        .upcoming-badge {
            font-size: 0.75rem;
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <?php include '../includes/student_sidebar.php'; ?>

        <div class="main-content">
            <div class="welcome-header">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2>Welcome back, <?php echo htmlspecialchars($student['firstname']); ?>! 👋</h2>
                        <p>
                            <strong>LRN:</strong> <?php echo $student['lrn']; ?> &bull;
                            <strong>Class:</strong> <?php echo $class_display; ?>
                        </p>
                    </div>
                    <div
                        style="font-size: 0.9rem; background: rgba(0,86,179,0.05); padding: 8px 15px; border-radius: 20px; color: #4169E1; font-weight: 500;">
                        📅 <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <!-- GPA Card -->
                <?php
                // Calculate GPA
                $gpa_sql = "SELECT AVG(grade) as gpa FROM grades WHERE student_id = '$student_id' AND grade IS NOT NULL";
                $gpa_res = $conn->query($gpa_sql);
                $gpa_row = $gpa_res->fetch_assoc();
                $gpa = $gpa_row['gpa'] ? number_format($gpa_row['gpa'], 2) : 'N/A';
                ?>
                <div class="stat-card">
                    <div class="stat-icon green">📊</div>
                    <div class="stat-info">
                        <h4>Average Grade</h4>
                        <div class="value"><?php echo $gpa; ?></div>
                    </div>
                </div>

                <!-- Balance Card -->
                <?php
                // Calculate Balance
                $total_fee = floatval($student['total_fee']);
                $paid_res = $conn->query("SELECT SUM(amount) as paid FROM payment_transactions WHERE student_id = '$student_id' AND status = 'completed'");
                $paid = floatval($paid_res->fetch_assoc()['paid']);
                $balance = $total_fee - $paid;
                ?>
                <div class="stat-card">
                    <div class="stat-icon <?php echo $balance > 0 ? 'red' : 'green'; ?>">💰</div>
                    <div class="stat-info">
                        <h4>Outstanding Balance</h4>
                        <div class="value">₱<?php echo number_format($balance, 2); ?></div>
                    </div>
                </div>

                <!-- Classes Today -->
                <?php
                $today = date('l');
                $classes_sql = "SELECT count(*) as count FROM schedules WHERE section_id = '$section_id' AND day = '$today'";
                $classes_res = $conn->query($classes_sql);
                $classes_count = $classes_res->fetch_assoc()['count'];
                ?>
                <div class="stat-card">
                    <div class="stat-icon blue">📚</div>
                    <div class="stat-info">
                        <h4>Classes Today</h4>
                        <div class="value"><?php echo $classes_count; ?></div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Today's Schedule -->
                <div class="card">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0;">📅 Today's Schedule</h3>
                        <a href="my_schedule.php" style="font-size: 0.9rem;">View All</a>
                    </div>

                    <?php
                    $today_sched_sql = "SELECT s.*, c.course_name as subject_name, t.lastname 
                                    FROM schedules s
                                    LEFT JOIN courses c ON s.course_id = c.id
                                    LEFT JOIN teachers t ON s.teacher_id = t.id
                                    WHERE s.section_id = '$section_id' AND s.day = '$today'
                                    ORDER BY s.time";
                    $today_sched_res = $conn->query($today_sched_sql);
                    ?>

                    <?php if ($today_sched_res->num_rows > 0): ?>
                        <div class="timeline">
                            <?php while ($sched = $today_sched_res->fetch_assoc()): ?>
                                <div class="timeline-item">
                                    <div class="timeline-time">
                                        <?php echo htmlspecialchars($sched['time']); ?>
                                    </div>
                                    <div class="timeline-title">
                                        <?php echo htmlspecialchars($sched['subject'] ?: $sched['subject_name']); ?>
                                    </div>
                                    <div class="timeline-desc">
                                        Room: <?php echo htmlspecialchars($sched['room'] ?: 'TBA'); ?> •
                                        Teacher: <?php echo htmlspecialchars($sched['lastname']); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 30px; color: #888;">
                            <div style="font-size: 2rem; margin-bottom: 10px;">☕</div>
                            <p>No classes scheduled for today.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Announcements -->
                <div class="card">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0;">📢 Announcements</h3>
                    </div>
                    <?php if ($ann_res->num_rows > 0): ?>
                        <ul class="announcement-list">
                            <?php while ($ann = $ann_res->fetch_assoc()): ?>
                                <li class="announcement-item">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                                        <span class="announcement-title"
                                            style="font-size: 1rem;"><?php echo htmlspecialchars($ann['title']); ?></span>
                                        <span class="announcement-date"
                                            style="font-size: 0.75rem; background: #f0f0f0; padding: 2px 6px; border-radius: 4px;"><?php echo date('M d', strtotime($ann['created_at'])); ?></span>
                                    </div>
                                    <p class="announcement-content"
                                        style="font-size: 0.9rem; color: #555; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo strip_tags($ann['content']); ?>
                                    </p>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p style="text-align: center; color: #888;">No new announcements.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
