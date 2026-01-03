<?php
// C:\xampp\htdocs\School_Portal\teacher\class_view.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'teacher') { header("Location: ../index.php"); exit(); }
$teacher_id = $_SESSION['teacher_id'];

$schedule_id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
if (empty($schedule_id)) { header("Location: my_classes.php"); exit(); }

// Verify this schedule belongs to teacher
$sch_sql = "SELECT sch.*, sec.section_name, sec.grade_level, c.course_name, c.course_code 
            FROM schedules sch
            JOIN sections sec ON sch.section_id = sec.id
            JOIN courses c ON sch.course_id = c.id
            WHERE sch.id = '$schedule_id' AND sch.teacher_id = '$teacher_id'";
$sch_res = $conn->query($sch_sql);
if ($sch_res->num_rows == 0) { die("Access Denied or Class Not Found."); }
$class = $sch_res->fetch_assoc();

// Fetch Enrolled Students
$sec_id = $class['section_id'];
$stu_sql = "SELECT * FROM students WHERE section_id = '$sec_id' ORDER BY lastname ASC";
$students_res = $conn->query($stu_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Class - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/teacher_sidebar.php'; ?>

    <div class="main-content">
        <!-- Class Header Info -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; color: var(--primary-color);"><?php echo htmlspecialchars($class['course_name']); ?></h2>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.95rem;">
                    <strong>Code:</strong> <?php echo htmlspecialchars($class['course_code']); ?> | 
                    <strong>Section:</strong> <?php echo htmlspecialchars($class['grade_level'].' - '.$class['section_name']); ?>
                </p>
                <p style="margin: 0; color: #888; font-size: 0.85rem;">
                    <i style="color: var(--secondary-color);"><?php echo $class['day']; ?></i> &bull; <?php echo $class['time']; ?>
                </p>
            </div>
            <a href="my_classes.php" class="btn" style="background: #6c757d; padding: 8px 15px; font-size: 0.9rem;">&larr; Back to Classes</a>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs" style="display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            <a href="class_view.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: var(--primary-color); border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Students List</a>
            <a href="class_grades.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: white; color: #333; border: 1px solid #ddd; border-bottom: none; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Gradebook</a>
            <a href="class_attendance.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: white; color: #333; border: 1px solid #ddd; border-bottom: none; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Attendance</a>
        </div>

        <div class="card">
            <h3>Enrolled Students</h3>
            <?php if ($students_res->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee; text-align: left;">
                            <th style="padding: 12px; color: #555;">LRN</th>
                            <th style="padding: 12px; color: #555;">Name</th>
                            <th style="padding: 12px; color: #555;">Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $students_res->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                <td style="padding: 12px; color: var(--primary-color); font-weight: 600;"><?php echo htmlspecialchars($s['lrn']); ?></td>
                                <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($s['lastname'] . ', ' . $s['firstname']); ?></td>
                                <td style="padding: 12px; color: #666;"><?php echo htmlspecialchars(substr($s['address'], 0, 30)) . '...'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding: 20px; text-align: center; color: #777;">No students enrolled in this section yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
