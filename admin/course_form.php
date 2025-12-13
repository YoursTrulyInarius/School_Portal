<?php
// C:\xampp\htdocs\School_Portal\admin\course_form.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
$is_edit = !empty($id);
$course_name = '';
$course_code = '';
$description = '';
$subject = '';
$teacher_id = '';
$room_time = '';
$error = '';

// Fetch Teachers for dropdown
$teachers_res = $conn->query("SELECT id, firstname, lastname FROM teachers ORDER BY lastname ASC");

if ($is_edit) {
    $sql = "SELECT * FROM courses WHERE id = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $course_name = $row['course_name'];
        $course_code = $row['course_code'];
        $description = $row['description'];
        $subject = $row['subject'];
        $teacher_id = $row['teacher_id'];
        $room_time = $row['room_time'];
    } else {
        header("Location: academics.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = clean_input($_POST['course_name']);
    $course_code = clean_input($_POST['course_code']);
    $description = clean_input($_POST['description']);
    $subject = clean_input($_POST['subject']);
    $teacher_id = clean_input($_POST['teacher_id']);
    $room_time = clean_input($_POST['room_time']);
    
    // Teacher ID might be empty
    $teacher_val = empty($teacher_id) ? "NULL" : "'$teacher_id'";
    
    if (empty($course_name) || empty($course_code)) {
        $error = "Name and Code are required.";
    }
    
    if (!$error) {
        if ($is_edit) {
            $sql = "UPDATE courses SET 
                    course_name='$course_name', 
                    course_code='$course_code', 
                    description='$description',
                    subject='$subject',
                    teacher_id=$teacher_val,
                    room_time='$room_time'
                    WHERE id='$id'";
        } else {
            $sql = "INSERT INTO courses (course_name, course_code, description, subject, teacher_id, room_time) 
                    VALUES ('$course_name', '$course_code', '$description', '$subject', $teacher_val, '$room_time')";
        }
        
        if ($conn->query($sql) === TRUE) {
            header("Location: academics.php?msg=Saved");
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
    <title><?php echo $is_edit ? 'Edit Course' : 'Add New Course'; ?> - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);"><?php echo $is_edit ? 'Edit Course' : 'Add New Course'; ?></h2>
            <a href="academics.php" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Academics</a>
        </div>
        
        <?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Course Code</label>
                    <input type="text" name="course_code" value="<?php echo htmlspecialchars($course_code); ?>" class="form-control" required placeholder="e.g. BSIT 101">
                </div>
                <div class="form-group">
                    <label>Course Name (Descriptive)</label>
                    <input type="text" name="course_name" value="<?php echo htmlspecialchars($course_name); ?>" class="form-control" required placeholder="e.g. Intro to Computing">
                </div>
                
                <!-- New Fields -->
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($subject); ?>" class="form-control" placeholder="e.g. Programming 1">
                </div>
                
                <div class="form-group">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-control">
                        <option value="">-- Select Teacher --</option>
                        <?php while($t = $teachers_res->fetch_assoc()): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $teacher_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['lastname'] . ', ' . $t['firstname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Room & Time</label>
                    <input type="text" name="room_time" value="<?php echo htmlspecialchars($room_time); ?>" class="form-control" placeholder="e.g. Room 305 MWF 9-10AM">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex: 1;"><?php echo $is_edit ? 'Update Course' : 'Create Course'; ?></button>
                    <a href="academics.php" class="btn" style="background: #95a5a6; flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
