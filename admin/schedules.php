<?php
// C:\xampp\htdocs\School_Portal\admin\schedules.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$success = '';
$error = '';

// Get Filter Parameters
$course_id = isset($_GET['course_id']) ? clean_input($_GET['course_id']) : '';
$strand_id = isset($_GET['strand_id']) ? clean_input($_GET['strand_id']) : '';

// Handle Form Submission
// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schedule'])) {
        // Required Fields
        $day = clean_input($_POST['day']);
        $time = clean_input($_POST['time']);
        $subject = clean_input($_POST['subject']);
        $section_id = clean_input($_POST['section_id']);
        $teacher_id = clean_input($_POST['teacher_id']);
        
        // Optional
        $room = clean_input($_POST['room']);
        $p_course_id = isset($_POST['course_id']) && !empty($_POST['course_id']) ? clean_input($_POST['course_id']) : null;
        $p_strand_id = isset($_POST['strand_id']) && !empty($_POST['strand_id']) ? clean_input($_POST['strand_id']) : null;

        if (empty($day) || empty($time) || empty($subject) || empty($section_id) || empty($teacher_id)) {
            $error = "Please fill in all required fields (Block, Subject, Time, Day, Teacher).";
        } else {
            // Insert with proper columns including strand_id and section_id
            $stmt = $conn->prepare("INSERT INTO schedules (course_id, strand_id, section_id, day, time, room, subject, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiissssi", $p_course_id, $p_strand_id, $section_id, $day, $time, $room, $subject, $teacher_id);
            
            if ($stmt->execute()) {
                $redirect_params = [];
                if ($p_course_id) $redirect_params[] = "course_id=$p_course_id";
                if ($p_strand_id) $redirect_params[] = "strand_id=$p_strand_id";
                $redirect_params[] = "success=added";
                
                header("Location: schedules.php?" . implode('&', $redirect_params));
                exit();
            } else {
                $error = "Error saving schedule: " . $conn->error;
            }
        }
    }
    
    if (isset($_POST['delete_schedule'])) {
        $id = clean_input($_POST['schedule_id']);
        $conn->query("DELETE FROM schedules WHERE id = '$id'");
        
        $redirect_params = [];
        if ($course_id) $redirect_params[] = "course_id=$course_id";
        if ($strand_id) $redirect_params[] = "strand_id=$strand_id";
        $redirect_params[] = "success=deleted";
        
        header("Location: schedules.php?" . implode('&', $redirect_params));
        exit();
    }
}

// Fetch Context Details
$program_name = '';
if ($course_id) {
    $res = $conn->query("SELECT course_name, course_code FROM courses WHERE id = '$course_id'");
    if ($r = $res->fetch_assoc()) $program_name = $r['course_code'] . ' - ' . $r['course_name'];
} elseif ($strand_id) {
    $res = $conn->query("SELECT strand_name, strand_code FROM strands WHERE id = '$strand_id'");
    if ($r = $res->fetch_assoc()) $program_name = $r['strand_code'] . ' - ' . $r['strand_name'];
}

// Fetch Schedules if context is set
$schedules = []; // Grouped by section_name
if ($course_id || $strand_id) {
    $where_clause = $course_id ? "s.course_id = '$course_id'" : "s.strand_id = '$strand_id'";
    
    // Updated Query to use section_id and JOIN sections table
    $sql = "SELECT s.*, t.firstname, t.lastname, sec.section_name 
            FROM schedules s 
            LEFT JOIN teachers t ON s.teacher_id = t.id 
            LEFT JOIN sections sec ON s.section_id = sec.id
            WHERE $where_clause 
            ORDER BY sec.section_name, FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), time";
            
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $schedules[$row['section_name']][] = $row;
    }
}

// Fetch Sections for the specific program for dropdown
$available_sections = [];
if ($course_id) {
    $sec_res = $conn->query("SELECT * FROM sections WHERE course_id = '$course_id' ORDER BY year_level, block");
    while($s = $sec_res->fetch_assoc()) $available_sections[] = $s;
} elseif ($strand_id) {
    $sec_res = $conn->query("SELECT * FROM sections WHERE strand_id = '$strand_id' ORDER BY year_level, block");
    while($s = $sec_res->fetch_assoc()) $available_sections[] = $s;
}

// Lists for Dropdowns/Selection
$courses = $conn->query("SELECT * FROM courses ORDER BY course_code");
$strands = $conn->query("SELECT * FROM strands ORDER BY strand_code");
$teachers = $conn->query("SELECT * FROM teachers ORDER BY lastname");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #004aad;
            --primary-dark: #003380;
            --secondary: #5ce1e6;
            --text-dark: #333;
            --bg-light: #f4f6f9;
        }
        body { font-family: 'Poppins', sans-serif; background: var(--bg-light); color: var(--text-dark); }
        .main-content { padding: 30px; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .title { font-size: 1.8rem; font-weight: 700; color: var(--primary); margin: 0; }
        
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card-header { font-size: 1.2rem; font-weight: 600; color: var(--primary); margin-bottom: 20px; text-transform: uppercase; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        /* Grid for Cards */
        .program-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
        .program-card {
            background: white; border: 1px solid #ddd; padding: 15px; border-radius: 10px; text-align: center;
            cursor: pointer; transition: all 0.2s;
        }
        .program-card:hover { transform: translateY(-3px); border-color: var(--primary); box-shadow: 0 5px 15px rgba(0,74,173,0.15); }
        .program-card h4 { margin: 0 0 5px 0; font-size: 1.1rem; color: var(--primary); }
        .program-card p { margin: 0; font-size: 0.85rem; color: #666; }
        .program-card.active { background: var(--primary); border-color: var(--primary); }
        .program-card.active h4, .program-card.active p { color: white; }
        
        /* Form */
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 150px; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #555; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(0,74,173,0.1); }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%; transition: background 0.2s; }
        .btn-submit:hover { background: var(--primary-dark); }
        
        /* Schedule List */
        .schedule-group { margin-bottom: 30px; }
        .group-title { font-size: 1.1rem; font-weight: 700; color: #444; margin-bottom: 10px; padding-left: 10px; border-left: 4px solid var(--secondary); display: flex; align-items: center; justify-content: space-between; }
        
        .schedule-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .schedule-table th { background: #f8f9fa; text-align: left; padding: 12px 15px; font-weight: 600; color: #666; font-size: 0.85rem; border-bottom: 1px solid #ddd; }
        .schedule-table td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; vertical-align: middle; }
        .schedule-table tr:last-child td { border-bottom: none; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-time { background: #e3f2fd; color: #1565c0; }
        .badge-room { background: #fff3e0; color: #e65100; }
        
        .delete-btn { color: #dc3545; border: none; background: none; cursor: pointer; font-size: 1.1rem; opacity: 0.6; transition: opacity 0.2s; }
        .delete-btn:hover { opacity: 1; }
        
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2 class="title">📅 Schedule Management</h2>
            <?php if ($program_name): ?>
                <a href="schedules.php" style="text-decoration: none; color: #666; font-size: 0.9rem; font-weight: 500;">&larr; Return to Selection</a>
            <?php endif; ?>
        </div>

        <?php if (!$course_id && !$strand_id): ?>
            <!-- Selection View -->
            <div class="card">
                <div class="card-header">Select Program to Manage</div>
                
                <h3 style="font-size: 1rem; color: #555; margin-bottom: 15px;">College Courses</h3>
                <div class="program-grid">
                    <?php while ($c = $courses->fetch_assoc()): ?>
                    <a href="?course_id=<?php echo $c['id']; ?>" style="text-decoration: none;">
                        <div class="program-card">
                            <h4><?php echo htmlspecialchars($c['course_code']); ?></h4>
                            <p><?php echo htmlspecialchars($c['course_name']); ?></p>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>

                <h3 style="font-size: 1rem; color: #555; margin: 25px 0 15px 0;">Senior High Strands</h3>
                <div class="program-grid">
                    <?php while ($s = $strands->fetch_assoc()): ?>
                    <a href="?strand_id=<?php echo $s['id']; ?>" style="text-decoration: none;">
                        <div class="program-card">
                            <h4><?php echo htmlspecialchars($s['strand_code']); ?></h4>
                            <p><?php echo htmlspecialchars($s['strand_name']); ?></p>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Management View -->
            
            <?php if($error): ?>
                <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    Add Schedule for <span style="color: var(--secondary);"><?php echo htmlspecialchars($program_name); ?></span>
                </div>
                
                <form method="POST">
                    <?php if($course_id): ?><input type="hidden" name="course_id" value="<?php echo $course_id; ?>"><?php endif; ?>
                    <?php if($strand_id): ?><input type="hidden" name="strand_id" value="<?php echo $strand_id; ?>"><?php endif; ?>
                    <input type="hidden" name="add_schedule" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Block / Section *</label>
                            <select name="section_id" class="form-control" required>
                                <option value="">Select Section</option>
                                <?php foreach($available_sections as $sec): ?>
                                    <option value="<?php echo $sec['id']; ?>"><?php echo htmlspecialchars($sec['section_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Subject *</label>
                            <input type="text" name="subject" class="form-control" placeholder="Subject Name/Code" required>
                        </div>
                        <div class="form-group">
                            <label>Day *</label>
                            <select name="day" class="form-control" required>
                                <option value="">Select Day</option>
                                <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                                    <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div class="form-group">
                            <label>Time *</label>
                            <input type="text" name="time" class="form-control" placeholder="e.g. 9:00 - 10:30 AM" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Teacher *</label>
                            <select name="teacher_id" class="form-control" required>
                                <option value="">Select Teacher</option>
                                <?php 
                                $teachers->data_seek(0);
                                while($t = $teachers->fetch_assoc()): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['lastname'] . ', ' . $t['firstname']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Room (Optional)</label>
                            <input type="text" name="room" class="form-control" placeholder="e.g. Room 305">
                        </div>
                        <div class="form-group" style="flex: 0 0 150px; display: flex; align-items: flex-end;">
                            <button type="submit" class="btn-submit">+ Add Entry</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Existing Schedules -->
            <?php if (empty($schedules)): ?>
                <div style="text-align: center; color: #888; padding: 40px; background: white; border-radius: 12px;">
                    No schedules found for this program yet. Add one above!
                </div>
            <?php else: ?>
                <?php foreach($schedules as $section => $entries): ?>
                    <div class="schedule-group">
                        <div class="group-title">
                            Blocking: <?php echo htmlspecialchars($section); ?>
                        </div>
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th width="15%">Day</th>
                                    <th width="20%">Time</th>
                                    <th width="25%">Subject</th>
                                    <th width="15%">Room</th>
                                    <th width="20%">Teacher</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($entries as $sched): ?>
                                <tr>
                                    <td style="font-weight: 500; color: var(--primary);"><?php echo htmlspecialchars($sched['day']); ?></td>
                                    <td><span class="badge badge-time"><?php echo htmlspecialchars($sched['time']); ?></span></td>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($sched['subject']); ?></td>
                                    <td><?php echo $sched['room'] ? "<span class='badge badge-room'>".$sched['room']."</span>" : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($sched['lastname'] . ', ' . $sched['firstname']); ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Remove this schedule entry?');" style="margin:0;">
                                            <?php if($course_id): ?><input type="hidden" name="course_id" value="<?php echo $course_id; ?>"><?php endif; ?>
                                            <?php if($strand_id): ?><input type="hidden" name="strand_id" value="<?php echo $strand_id; ?>"><?php endif; ?>
                                            <input type="hidden" name="schedule_id" value="<?php echo $sched['id']; ?>">
                                            <button type="submit" name="delete_schedule" class="delete-btn" title="Delete">&times;</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
    // SweetAlert for Success
    <?php if (isset($_GET['success'])): ?>
        const type = '<?php echo $_GET['success']; ?>';
        Swal.fire({
            icon: 'success',
            title: type === 'added' ? 'Schedule Added' : 'Deleted',
            text: type === 'added' ? 'The schedule has been successfully created.' : 'The schedule entry has been removed.',
            timer: 2000,
            showConfirmButton: false
        });
        // Clean URL
        const newUrl = window.location.href.split('&success')[0];
        window.history.replaceState({}, document.title, newUrl);
    <?php endif; ?>
</script>

</body>
</html>
