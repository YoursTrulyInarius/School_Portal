<?php
// C:\xampp\htdocs\School_Portal\student\my_schedule.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_user_id = $_SESSION['user_id'];

// Fetch student info including section
$student_sql = "SELECT s.*, sec.section_name, sec.grade_level 
                FROM students s
                LEFT JOIN sections sec ON s.section_id = sec.id
                WHERE s.user_id = '$student_user_id'";
$student_result = $conn->query($student_sql);
$student = $student_result->fetch_assoc();

$section_id = $student['section_id'];
$class_year = $student['grade_level']; // For display purposes

$section_name = $conn->real_escape_string($student['section_name']);

// Normalize section name for comparison (remove spaces and hyphens)
$normalized_section_name = preg_replace('/[^a-zA-Z0-9]/', '', $section_name);

// Fetch schedules that match the student's section (fuzzy match)
$schedules_sql = "SELECT sch.*, 
                         c.course_name as course_name_legacy,
                         c.course_code,
                         t.firstname, 
                         t.lastname,
                         sch.room
                  FROM schedules sch
                  LEFT JOIN courses c ON sch.course_id = c.id
                  LEFT JOIN teachers t ON sch.teacher_id = t.id
                  WHERE sch.section_id = '$section_id'
                  ORDER BY FIELD(sch.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sch.time";
$schedules_res = $conn->query($schedules_sql);

// Group schedules by Day
$grouped_schedules = [];
while ($row = $schedules_res->fetch_assoc()) {
    $grouped_schedules[$row['day']][] = $row;
}

$valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Loads - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Using specialized CSS for the "Form" look -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #eef1f5;
            margin: 0;
        }

        .paper-container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-top: 5px solid #4169E1;
        }

        /* Header Section */
        .doc-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }

        .header-logo {
            width: 80px;
            height: 80px;
            position: absolute;
            left: 20px;
            top: 0;
        }

        .header-text {
            line-height: 1.4;
        }

        .header-text h2 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
            font-weight: 700;
            text-transform: uppercase;
        }

        .header-text p {
            margin: 0;
            font-size: 0.8rem;
            color: #555;
        }

        /* Colored Bars */
        .bar-title {
            text-align: center;
            font-weight: 800;
            padding: 5px;
            text-transform: uppercase;
            font-size: 0.95rem;
            border: 1px solid #000;
            margin-top: -1px;
            /* collapse borders */
        }

        .bar-yellow {
            background: #FFFF00;
            color: black;
            margin-top: 20px;
            border: 1px solid black;
        }

        .bar-red {
            background: #FF0000;
            color: white;
        }

        .bar-cyan {
            background: #00FFFF;
            color: black;
        }

        /* The Grid Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            margin-top: -1px;
            /* Connect to bars */
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        .schedule-table th {
            font-weight: 700;
            text-transform: uppercase;
            background: white;
            color: black;
            padding: 10px;
        }

        .day-cell {
            font-weight: 700;
            background: white;
            vertical-align: middle;
            text-transform: uppercase;
            width: 120px;
        }

        .subject-cell {
            font-weight: 600;
            color: #4169E1;
        }

        .time-cell {
            font-family: monospace;
            font-size: 0.9rem;
        }

</head>
<style>
    /* ... existing styles ... */
    
    .desktop-only { display: block; }
    .mobile-only { display: none; }
    
    .mobile-day-group {
        margin-bottom: 20px;
    }
    
    .mobile-day-header {
        background: #4169E1;
        color: white;
        padding: 10px 15px;
        font-weight: 700;
        border-radius: 8px 8px 0 0;
        font-size: 0.9rem;
    }
    
    .mobile-sched-card {
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        padding: 15px;
        margin-bottom: 0px;
    }
    
    .mobile-sched-card:last-child {
        border-radius: 0 0 8px 8px;
    }
    
    .mobile-time {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 5px;
        font-family: monospace;
    }
    
    .mobile-subject {
        font-size: 1rem;
        font-weight: 600;
        color: #4169E1;
        margin-bottom: 8px;
    }
    
    .mobile-details {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: #555;
    }

    @media screen and (max-width: 768px) {
        .paper-container {
            padding: 15px;
            margin: 10px;
        }
        
        .desktop-only { display: none; }
        .mobile-only { display: block; }
        
        .header-logo { width: 50px; height: 50px; display: block; margin: 0 auto 10px; position: static; }
        .doc-header { text-align: center; }
        .header-text h2 { font-size: 1rem; }
        .header-text p { font-size: 0.8rem; }
        
        .bar-title { font-size: 0.9rem; padding: 6px; }
    }
</style>

<body>

    <div class="dashboard-container">
        <?php include '../includes/student_sidebar.php'; ?>

        <div class="main-content">

            <div class="paper-container">
                <!-- Header -->
                <div class="doc-header">
                    <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="header-logo">
                    <div class="header-text">
                        <h2>West Prime Horizon Institute, Inc.</h2>
                        <p>West Prime Horizon Institute Building<br>
                            V. Sagun cor. M. Roxas St.<br>
                            San Francisco Dist. Pagadian City</p>
                    </div>
                </div>

                <!-- Title Bars -->
                <div class="bar-title bar-yellow">SUBJECT LOADS</div>
                <div class="bar-title bar-red">2ND SEMESTER AY 2025-2026</div>
                <!-- User might want dynamic sem/year later -->
                <div class="bar-title bar-cyan">
                    <?php echo htmlspecialchars($class_year ? $class_year : 'NO CLASS ASSIGNED'); ?>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th style="width: 12%;">DAY</th>
                                <th style="width: 18%;">TIME</th>
                                <th style="width: 35%;">SUBJECT</th>
                                <th style="width: 20%;">TEACHER</th>
                                <th style="width: 15%;">ROOM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($valid_days as $day):
                                $day_items = isset($grouped_schedules[$day]) ? $grouped_schedules[$day] : [];
                                $row_count = max(1, count($day_items)); // At least 1 row per day
                                ?>
                                <?php for ($i = 0; $i < $row_count; $i++): ?>
                                    <tr>
                                        <!-- Day Column (Rowspan) -->
                                        <?php if ($i === 0): ?>
                                            <td class="day-cell" rowspan="<?php echo $row_count; ?>">
                                                <?php echo strtoupper($day); ?>
                                            </td>
                                        <?php endif; ?>

                                        <!-- Schedule Details -->
                                        <?php if (isset($day_items[$i])):
                                            $item = $day_items[$i];
                                            ?>
                                            <td class="time-cell">
                                                <?php echo htmlspecialchars($item['time']); ?>
                                            </td>
                                            <td class="subject-cell">
                                                <?php echo htmlspecialchars($item['subject'] ? $item['subject'] : ($item['course_name_legacy'] ?: 'N/A')); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['firstname'] . ' ' . $item['lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($item['room'] ?: 'N/A'); ?></td>
                                        <?php else: ?>
                                            <!-- Empty Row if no schedule -->
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endfor; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                    <?php if (!$class_year): ?>
                        <div style="text-align: center; padding: 20px; color: red; font-weight: bold;">
                            You are not assigned to any class year yet.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

</body>

</html>
