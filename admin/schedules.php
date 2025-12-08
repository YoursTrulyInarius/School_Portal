<?php
// C:\xampp\htdocs\School_Portal\admin\schedules.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$success = '';
$error = '';

// Handle adding/editing schedule entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schedule'])) {
        $class_year = clean_input($_POST['class_year']);
        $day = clean_input($_POST['day']);
        $time_start = clean_input($_POST['time_start']);
        $subject = clean_input($_POST['subject']);
        $teacher_id = clean_input($_POST['teacher_id']);
        $room = clean_input($_POST['room']);
        
        $stmt = $conn->prepare("INSERT INTO schedules (class_year, day, time_start, subject, teacher_id, room) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $class_year, $day, $time_start, $subject, $teacher_id, $room);
        
        if ($stmt->execute()) {
            $success = "Schedule added successfully!";
        } else {
            $error = "Error adding schedule.";
        }
    }
    
    if (isset($_POST['delete_schedule'])) {
        $schedule_id = clean_input($_POST['schedule_id']);
        $conn->query("DELETE FROM schedules WHERE id = '$schedule_id'");
        $success = "Schedule deleted successfully!";
    }
}

// Get selected class
$selected_class = isset($_GET['class']) ? clean_input($_GET['class']) : '';

// Fetch all unique classes from students
$classes_sql = "SELECT DISTINCT class_year FROM students WHERE class_year IS NOT NULL ORDER BY class_year";
$classes_result = $conn->query($classes_sql);

// Fetch schedules for selected class
$schedules = [];
if ($selected_class) {
    $sched_sql = "SELECT s.*, t.firstname, t.lastname 
                  FROM schedules s
                  LEFT JOIN teachers t ON s.teacher_id = t.id
                  WHERE s.class_year = '$selected_class'
                  ORDER BY 
                    FIELD(s.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                    s.time_start";
    $schedules_result = $conn->query($sched_sql);
    
    while ($row = $schedules_result->fetch_assoc()) {
        $schedules[$row['day']][] = $row;
    }
}

// Fetch all teachers for dropdown
$teachers_sql = "SELECT id, firstname, lastname FROM teachers ORDER BY lastname";
$teachers = $conn->query($teachers_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; margin: 0; }
        
        .page-title {
            color: #0056b3;
            font-size: 1.5rem;
            margin: 0 0 25px 0;
        }
        
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 600px;
        }
        .schedule-table th {
            background: #0056b3;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #004494;
        }
        .schedule-table td {
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .day-cell {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            width: 100px;
        }
        .schedule-entry {
            background: #e3f2fd;
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 4px;
            border-left: 3px solid #2196f3;
        }
        .schedule-entry:last-child {
            margin-bottom: 0;
        }
        .time-badge {
            background: #2196f3;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .page-title {
                font-size: 1.3rem;
            }
            
            .card {
                padding: 16px;
            }
            
            .form-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 12px !important;
            }
            
            .schedule-table {
                min-width: 550px;
            }
            
            .schedule-table th,
            .schedule-table td {
                padding: 8px;
                font-size: 0.85rem;
            }
            
            .day-cell {
                width: 80px;
            }
            
            .time-badge {
                font-size: 0.7rem;
                padding: 2px 6px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; color: var(--primary-color);">Schedule Management</h2>

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

        <!-- Class Selector -->
        <div class="card" style="margin-bottom: 25px;">
            <h3 style="margin: 0 0 15px 0;">Select Class/Strand</h3>
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <select name="class" class="form-control" style="max-width: 300px;" onchange="this.form.submit()">
                    <option value="">-- Select Class --</option>
                    <?php while($class = $classes_result->fetch_assoc()): ?>
                        <option value="<?php echo $class['class_year']; ?>" <?php echo $selected_class == $class['class_year'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_year']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_class): ?>
            <!-- Add Schedule Form -->
            <div class="card" style="margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0;">Add Schedule Entry for <?php echo htmlspecialchars($selected_class); ?></h3>
                <form method="POST">
                    <input type="hidden" name="class_year" value="<?php echo $selected_class; ?>">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <div class="form-group">
                            <label>Day *</label>
                            <select name="day" class="form-control" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Time *</label>
                            <input type="text" name="time_start" class="form-control" placeholder="e.g. 7:00 AM - 10:00 AM" required>
                        </div>
                        <div class="form-group">
                            <label>Subject *</label>
                            <input type="text" name="subject" class="form-control" placeholder="e.g. Web Development" required>
                        </div>
                        <div class="form-group">
                            <label>Teacher *</label>
                            <select name="teacher_id" class="form-control" required>
                                <option value="">Select Teacher</option>
                                <?php 
                                $teachers->data_seek(0);
                                while($teacher = $teachers->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $teacher['id']; ?>">
                                        <?php echo htmlspecialchars($teacher['firstname'] . ' ' . $teacher['lastname']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Room</label>
                            <input type="text" name="room" class="form-control" placeholder="e.g. 201">
                        </div>
                    </div>
                    <button type="submit" name="add_schedule" class="btn" style="margin-top: 15px;">Add Schedule</button>
                </form>
            </div>

            <!-- Weekly Schedule Table -->
            <div class="card" style="padding: 0; overflow-x: auto;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Room</th>
                            <th style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $day): 
                        ?>
                            <?php if (isset($schedules[$day]) && count($schedules[$day]) > 0): ?>
                                <?php foreach ($schedules[$day] as $index => $sched): ?>
                                    <tr>
                                        <?php if ($index == 0): ?>
                                            <td class="day-cell" rowspan="<?php echo count($schedules[$day]); ?>">
                                                <?php echo $day; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="time-badge">
                                                <?php echo htmlspecialchars($sched['time_start']); ?>
                                            </span>
                                        </td>
                                        <td style="font-weight: 600; color: #2196f3;">
                                            <?php echo htmlspecialchars($sched['subject']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($sched['firstname'] . ' ' . $sched['lastname']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($sched['room'] ?: '-'); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this schedule?');">
                                                <input type="hidden" name="schedule_id" value="<?php echo $sched['id']; ?>">
                                                <button type="submit" name="delete_schedule" class="btn-delete">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="day-cell"><?php echo $day; ?></td>
                                    <td colspan="5" style="color: #999; font-style: italic; text-align: center;">No schedule</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <p style="text-align: center; color: #888; padding: 30px;">Please select a class to view and manage schedules.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
