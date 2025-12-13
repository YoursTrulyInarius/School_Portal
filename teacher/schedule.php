<?php
// C:\xampp\htdocs\School_Portal\teacher\schedule.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'teacher') { header("Location: ../index.php"); exit(); }
$teacher_id = $_SESSION['teacher_id'];

// Get current day of the week
$current_day = date('l'); // Returns: Monday, Tuesday, etc.

// Get selected day from URL or default to current day
$selected_day = isset($_GET['day']) ? clean_input($_GET['day']) : $current_day;

// Define days of the week
$days_of_week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Fetch schedule for selected day
$sql = "SELECT sch.id as schedule_id, 
               c.course_name as course_name_legacy,
               c.course_code,
               s.section_name,
               s.grade_level as class_year,
               s.grade_level as class_year,
               st.strand_code,
                sch.day,
                sch.time
        FROM schedules sch
        LEFT JOIN courses c ON sch.course_id = c.id
        LEFT JOIN sections s ON sch.section_id = s.id
        LEFT JOIN strands st ON s.strand_id = st.id
        WHERE sch.teacher_id = '$teacher_id' AND sch.day = '$selected_day'
        ORDER BY sch.time, s.grade_level";
$result = $conn->query($sql);

// Fetch all schedules for counting badges
$all_sql = "SELECT day, COUNT(*) as count FROM schedules WHERE teacher_id = '$teacher_id' GROUP BY day";
$all_result = $conn->query($all_sql);
$day_counts = [];
while($row = $all_result->fetch_assoc()) {
    $day_counts[$row['day']] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        
        .day-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .day-tab {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            background: white;
            color: #666;
            border: 2px solid #e0e0e0;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .day-tab:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .day-tab.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .day-tab.today:not(.active) {
            border-color: #2ecc71;
            background: #f0fff4;
        }
        
        .day-tab .badge {
            background: rgba(0,0,0,0.15);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        
        .day-tab.active .badge {
            background: rgba(255,255,255,0.3);
        }
        
        .today-indicator {
            display: inline-block;
            background: #2ecc71;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .schedule-table th {
            padding: 15px;
            text-align: left;
            color: white;
            font-weight: 600;
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        .schedule-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .schedule-table tr:hover {
            background: #f9f9f9;
        }
        
        .subject-name {
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }
        
        .class-badge {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .room-badge {
            display: inline-block;
            background: #fff3e0;
            color: #e65100;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .time-display {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.95rem;
        }
        
        .empty-state {
            padding: 60px 40px;
            text-align: center;
            color: #888;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #666;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .day-tabs {
                gap: 5px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 10px;
            }
            
            .day-tab {
                padding: 8px 12px;
                font-size: 0.8rem;
                flex-shrink: 0;
            }
            
            .schedule-table {
                min-width: 400px;
            }
            
            .schedule-table th,
            .schedule-table td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
            
            .hide-mobile {
                display: none;
            }
            
            .subject-name {
                font-size: 0.9rem;
            }
            
            .class-badge,
            .room-badge {
                font-size: 0.75rem;
                padding: 3px 8px;
            }
            
            .empty-state {
                padding: 40px 20px;
            }
            
            .empty-state-icon {
                font-size: 3rem;
            }
        }
        
        @media (max-width: 480px) {
            .day-tab {
                padding: 6px 10px;
                font-size: 0.75rem;
            }
            
            .day-tab .badge {
                padding: 1px 5px;
                font-size: 0.65rem;
            }
            
            .schedule-table th,
            .schedule-table td {
                padding: 8px 5px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/teacher_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0; color: var(--primary-color);">
                My Schedule
                <?php if ($selected_day == $current_day): ?>
                    <span class="today-indicator">📅 Today</span>
                <?php endif; ?>
            </h2>
            <span style="color: #666; font-size: 0.9rem;">
                <?php echo date('l, F j, Y'); ?>
            </span>
        </div>
        
        <!-- Day Tabs -->
        <div class="day-tabs">
            <?php foreach ($days_of_week as $day): ?>
                <?php 
                $is_active = ($selected_day == $day);
                $is_today = ($current_day == $day);
                $count = isset($day_counts[$day]) ? $day_counts[$day] : 0;
                ?>
                <a href="?day=<?php echo $day; ?>" 
                   class="day-tab <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_today ? 'today' : ''; ?>">
                    <?php echo substr($day, 0, 3); ?>
                    <?php if ($count > 0): ?>
                        <span class="badge"><?php echo $count; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Schedule Table -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <?php if ($result->num_rows > 0): ?>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Time</th>
                            <th style="width: 40%;">Subject</th>
                            <th style="width: 20%;">Section</th>
                            <th style="width: 20%;">Class/Year</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="time-display">
                                    <?php echo htmlspecialchars($row['time']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="subject-name">
                                    <?php echo htmlspecialchars($row['subject'] ? $row['subject'] : ($row['course_name_legacy'] ?: 'N/A')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="class-badge">
                                    <?php echo htmlspecialchars($row['section_name'] . ($row['strand_code'] ? ' (' . $row['strand_code'] . ')' : '')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="class-badge"><?php echo htmlspecialchars($row['class_year'] ?: 'N/A'); ?></span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No Classes on <?php echo $selected_day; ?></h3>
                    <p>You don't have any scheduled classes for this day.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Summary -->
        <div style="margin-top: 20px; padding: 15px 20px; background: white; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <span style="color: #666; font-size: 0.9rem;">
                <strong style="color: var(--primary-color);"><?php echo $result->num_rows; ?></strong> class<?php echo $result->num_rows != 1 ? 'es' : ''; ?> scheduled for <?php echo $selected_day; ?>
            </span>
            <a href="schedule.php?day=<?php echo $current_day; ?>" class="btn" style="background: var(--primary-color); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                Go to Today
            </a>
        </div>
    </div>
</div>

</body>
</html>
