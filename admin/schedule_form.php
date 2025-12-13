<?php
// C:\xampp\htdocs\School_Portal\admin\schedule_form.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
$is_edit = !empty($id);

$course_id = '';
$class_year = '';
$subject = '';
$room = '';
$time_schedule = '';
$teacher_id = '';
$error = '';

// Data for dropdowns
$course_res = $conn->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code");
$teach_res = $conn->query("SELECT id, firstname, lastname FROM teachers ORDER BY lastname");

if ($is_edit) {
    $sql = "SELECT * FROM schedules WHERE id = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $course_id = $row['course_id'];
        $class_year = $row['class_year'];
        $subject = $row['subject'];
        $room = $row['room'];
        $time_schedule = $row['day'];
        $teacher_id = $row['teacher_id'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = clean_input($_POST['course_id']);
    $class_year = clean_input($_POST['class_year']);
    $subject = clean_input($_POST['subject']);
    $room = clean_input($_POST['room']);
    $time_schedule = clean_input($_POST['time_schedule']);
    $teacher_id = clean_input($_POST['teacher_id']);
    
    if (empty($course_id) || empty($subject) || empty($teacher_id)) {
        $error = "Course/Strand, Subject, and Teacher are required.";
    }
    
    if (!$error) {
        if ($is_edit) {
            $sql = "UPDATE schedules SET 
                    course_id='$course_id',
                    class_year='$class_year',
                    subject='$subject', 
                    room='$room', 
                    day='$time_schedule',
                    teacher_id='$teacher_id',
                    section_id=NULL,
                    time_start='00:00:00',
                    time_end='00:00:00'
                    WHERE id='$id'";
        } else {
            $sql = "INSERT INTO schedules (course_id, class_year, subject, room, day, teacher_id, section_id, time_start, time_end) 
                    VALUES ('$course_id', '$class_year', '$subject', '$room', '$time_schedule', '$teacher_id', NULL, '00:00:00', '00:00:00')";
        }
        
        if ($conn->query($sql) === TRUE) {
            header("Location: schedules.php?msg=Saved");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Schedule' : 'Add New Schedule'; ?> - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);"><?php echo $is_edit ? 'Edit Schedule' : 'Add New Schedule'; ?></h2>
            <a href="schedules.php" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Schedules</a>
        </div>
        
        <?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="">
                
                <div class="form-group">
                    <label>Course/Strand</label>
                    <select name="course_id" class="form-control" required>
                        <option value="">Select Course/Strand</option>
                        <?php while($c = $course_res->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $c['id'] == $course_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Class/Year</label>
                    <input type="text" name="class_year" value="<?php echo htmlspecialchars($class_year); ?>" class="form-control" placeholder="e.g. BSIT 2-C, Grade 11-A">
                    <small style="color: #666; font-size: 0.85rem;">Specify the year level and section (e.g., "BSIT 2-C" or "Grade 11-A")</small>
                </div>
                
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($subject); ?>" class="form-control" required placeholder="e.g. Programming 1, Mathematics">
                </div>
                
                <div class="form-group">
                    <label>Room</label>
                    <input type="text" name="room" value="<?php echo htmlspecialchars($room); ?>" class="form-control" placeholder="e.g. Room 305">
                </div>
                
                <div class="form-group">
                    <label>Time Schedule</label>
                    <input type="text" name="time_schedule" value="<?php echo htmlspecialchars($time_schedule); ?>" class="form-control" placeholder="e.g. MWF 9:00-10:00 AM">
                    <small style="color: #666; font-size: 0.85rem;">Enter day and time (e.g., "MWF 9:00-10:00 AM" or "Monday 1:00-2:30 PM")</small>
                </div>
                
                <div class="form-group">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Select Teacher</option>
                        <?php while($t = $teach_res->fetch_assoc()): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $teacher_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['lastname'] . ', ' . $t['firstname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex: 1;"><?php echo $is_edit ? 'Update Schedule' : 'Create Schedule'; ?></button>
                    <a href="schedules.php" class="btn" style="background: #95a5a6; flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
