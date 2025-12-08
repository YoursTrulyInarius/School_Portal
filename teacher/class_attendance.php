<?php
// C:\xampp\htdocs\School_Portal\teacher\class_attendance.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'teacher') { header("Location: ../index.php"); exit(); }
$teacher_id = $_SESSION['teacher_id'];

$schedule_id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
if (empty($schedule_id)) { header("Location: my_classes.php"); exit(); }

$sch_sql = "SELECT sch.*, sec.section_name, sec.grade_level, c.course_name 
            FROM schedules sch
            JOIN sections sec ON sch.section_id = sec.id
            JOIN courses c ON sch.course_id = c.id
            WHERE sch.id = '$schedule_id' AND sch.teacher_id = '$teacher_id'";
$sch_res = $conn->query($sch_sql);
if ($sch_res->num_rows == 0) { die("Access Denied."); }
$class = $sch_res->fetch_assoc();
$sec_id = $class['section_id'];
$course_id = $class['course_id'];

// Handle Attendance Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = clean_input($_POST['date']);
    $status_arr = $_POST['status']; // [student_id => status]

    foreach ($status_arr as $s_id => $status) {
        // Check duplicate
        $check = $conn->query("SELECT id FROM attendance WHERE student_id='$s_id' AND course_id='$course_id' AND date='$date'");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE attendance SET status='$status' WHERE student_id='$s_id' AND course_id='$course_id' AND date='$date'");
        } else {
            $conn->query("INSERT INTO attendance (student_id, course_id, date, status) VALUES ('$s_id', '$course_id', '$date', '$status')");
        }
    }
    header("Location: class_attendance.php?id=$schedule_id&msg=Attendance Saved");
    exit();
}

$students_res = $conn->query("SELECT * FROM students WHERE section_id = '$sec_id' ORDER BY lastname ASC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Westprime Horizon</title>
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
                     <strong>Section:</strong> <?php echo htmlspecialchars($class['grade_level'].' - '.$class['section_name']); ?>
                </p>
            </div>
            <a href="my_classes.php" class="btn" style="background: #6c757d; padding: 8px 15px; font-size: 0.9rem;">&larr; Back to Classes</a>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs" style="display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            <a href="class_view.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: white; color: #333; border: 1px solid #ddd; border-bottom: none; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Students List</a>
            <a href="class_grades.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: white; color: #333; border: 1px solid #ddd; border-bottom: none; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Gradebook</a>
            <a href="class_attendance.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: var(--primary-color); border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Attendance</a>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px;">Mark Attendance</h3>
            <form method="POST" action="">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600;">Date</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" style="max-width: 200px; display: inline-block; margin-left: 10px;" required>
                </div>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee;">
                            <th style="text-align: left; padding: 12px; color: #555;">Student</th>
                            <th style="text-align: center; padding: 12px; color: #555;">Present</th>
                            <th style="text-align: center; padding: 12px; color: #555;">Absent</th>
                            <th style="text-align: center; padding: 12px; color: #555;">Late</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($students_res->num_rows > 0) {
                            while($s = $students_res->fetch_assoc()) {
                                echo "<tr style='border-bottom: 1px solid #f9f9f9;'>
                                        <td style='padding: 12px;'><strong>{$s['lastname']}</strong>, {$s['firstname']}</td>
                                        <td style='text-align: center; padding: 12px;'><input type='radio' name='status[{$s['id']}]' value='present' checked style='width: 18px; height: 18px; accent-color: var(--primary-color);'></td>
                                        <td style='text-align: center; padding: 12px;'><input type='radio' name='status[{$s['id']}]' value='absent' style='width: 18px; height: 18px; accent-color: #dc3545;'></td>
                                        <td style='text-align: center; padding: 12px;'><input type='radio' name='status[{$s['id']}]' value='late' style='width: 18px; height: 18px; accent-color: #ffc107;'></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='padding: 20px; text-align: center;'>No students found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <button type="submit" class="btn" style="margin-top: 25px; padding: 10px 25px;">Save Attendance</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
