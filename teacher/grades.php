<?php
// C:\xampp\htdocs\School_Portal\teacher\grades.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'teacher') { header("Location: ../index.php"); exit(); }
$teacher_id = $_SESSION['teacher_id'];

$success = '';
$error = '';

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grade'])) {
    $student_id = clean_input($_POST['student_id']);
    $grade = clean_input($_POST['grade']);
    $schedule_id = clean_input($_POST['schedule_id']);
    
    // Check if grade exists
    $check_sql = "SELECT id FROM grades WHERE student_id = '$student_id' AND schedule_id = '$schedule_id'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        // Update existing grade
        $stmt = $conn->prepare("UPDATE grades SET grade = ? WHERE student_id = ? AND schedule_id = ?");
        $stmt->bind_param("dii", $grade, $student_id, $schedule_id);
    } else {
        // Insert new grade
        $stmt = $conn->prepare("INSERT INTO grades (student_id, schedule_id, grade) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $student_id, $schedule_id, $grade);
    }
    
    if ($stmt->execute()) {
        $success = "Grade saved successfully!";
    } else {
        $error = "Error saving grade.";
    }
}

// Get selected schedule
$selected_schedule = isset($_GET['schedule']) ? clean_input($_GET['schedule']) : '';

// Fetch teacher's schedules
$schedules_sql = "SELECT sch.id, 
                         s.grade_level as class_year, 
                         c.course_name as subject, 
                         sch.day, 
                         sch.time,
                         sch.section_id
                  FROM schedules sch
                  LEFT JOIN courses c ON sch.course_id = c.id
                  LEFT JOIN sections s ON sch.section_id = s.id
                  WHERE sch.teacher_id = '$teacher_id' 
                  ORDER BY s.grade_level, c.course_name";
$schedules_result = $conn->query($schedules_sql);

// Fetch students and grades for selected schedule
$students = [];
if ($selected_schedule) {
    // Get section_id from selected schedule
    $sched_info = $conn->query("SELECT sch.section_id, c.course_name as subject 
                                 FROM schedules sch 
                                 LEFT JOIN courses c ON sch.course_id = c.id 
                                 WHERE sch.id = '$selected_schedule'")->fetch_assoc();
    
    if ($sched_info && $sched_info['section_id']) {
        $section_id = $sched_info['section_id'];
        
        // Get all students in this section
        $students_sql = "SELECT s.id, s.firstname, s.lastname, s.lrn, 
                               g.grade, g.id as grade_id
                        FROM students s
                        LEFT JOIN grades g ON s.id = g.student_id AND g.schedule_id = '$selected_schedule'
                        WHERE s.section_id = '$section_id'
                        ORDER BY s.lastname, s.firstname";
        $students_result = $conn->query($students_sql);
        
        while ($row = $students_result->fetch_assoc()) {
            $students[] = $row;
        }
    }
}

// Grade options
$grade_options = [
    '1.00' => '1.00 - Excellent',
    '1.25' => '1.25 - Superior/Outstanding',
    '1.50' => '1.50 - Very Good/Superior',
    '1.75' => '1.75 - Very Good/Good',
    '2.00' => '2.00 - Satisfactory/Good',
    '2.25' => '2.25 - Satisfactory',
    '2.50' => '2.50 - Fairly Satisfactory',
    '3.00' => '3.00 - Pass',
    '4.00' => '4.00 - Conditional/Remedial',
    '5.00' => '5.00 - Failed'
];

function getGradeColor($grade) {
    $g = floatval($grade);
    if ($g <= 1.50) return '#2ecc71';
    if ($g <= 2.50) return '#3498db';
    if ($g <= 3.00) return '#f39c12';
    return '#e74c3c';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grades - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .grade-badge {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-block;
        }
        .grade-scale {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .grade-scale ul {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
        }
        .grade-scale li {
            font-size: 0.8rem;
            color: #666;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .grade-scale ul {
                grid-template-columns: 1fr;
            }
            
            .card {
                padding: 15px !important;
            }
            
            table {
                min-width: 500px;
            }
            
            th, td {
                padding: 10px 8px !important;
                font-size: 0.85rem !important;
            }
            
            .btn {
                padding: 8px 12px !important;
                font-size: 0.8rem !important;
            }
            
            .modal-content {
                padding: 20px;
                margin: 10px;
            }
        }
        
        @media (max-width: 480px) {
            table {
                min-width: 400px;
            }
            
            th, td {
                padding: 8px 5px !important;
                font-size: 0.8rem !important;
            }
            
            .grade-badge {
                padding: 4px 8px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/teacher_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; color: var(--primary-color);">Manage Grades</h2>

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

        <!-- Grade Scale Reference -->
        <div class="grade-scale">
            <h4 style="margin: 0 0 10px 0;">📊 Grading Scale (1.00 - 5.00)</h4>
            <ul>
                <?php foreach ($grade_options as $grade => $description): ?>
                    <li>
                        <span class="grade-badge" style="background: <?php echo getGradeColor($grade); ?>; padding: 2px 8px; font-size: 0.7rem;">
                            <?php echo $grade; ?>
                        </span>
                        <?php echo $description; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Class/Subject Selector -->
        <div class="card" style="margin-bottom: 25px;">
            <h3 style="margin: 0 0 15px 0;">Select Class/Subject</h3>
            <form method="GET">
                <select name="schedule" class="form-control" style="max-width: 500px;" onchange="this.form.submit()">
                    <option value="">-- Select Class/Subject --</option>
                    <?php while($sched = $schedules_result->fetch_assoc()): ?>
                        <option value="<?php echo $sched['id']; ?>" <?php echo $selected_schedule == $sched['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sched['class_year'] . ' - ' . $sched['subject']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_schedule && count($students) > 0): ?>
            <!-- Students Table -->
            <div class="card" style="padding: 0; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #3498db, #2980b9);">
                            <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">LRN</th>
                            <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Student Name</th>
                            <th style="padding: 15px; text-align: center; color: white; font-weight: 600;">Current Grade</th>
                            <th style="padding: 15px; text-align: center; color: white; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $student): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; color: #666; font-family: monospace;">
                                    <?php echo htmlspecialchars($student['lrn']); ?>
                                </td>
                                <td style="padding: 15px; font-weight: 600; color: #333;">
                                    <?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <?php if ($student['grade']): ?>
                                        <span class="grade-badge" style="background: <?php echo getGradeColor($student['grade']); ?>;">
                                            <?php echo number_format($student['grade'], 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #999;">No grade</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <button onclick="openGradeModal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>', <?php echo $student['grade'] ? $student['grade'] : 'null'; ?>)" 
                                            class="btn" 
                                            style="padding: 8px 15px; font-size: 0.85rem; background: #2ecc71;">
                                        <?php echo $student['grade'] ? 'Edit Grade' : 'Add Grade'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($selected_schedule): ?>
            <div class="card">
                <p style="text-align: center; color: #888; padding: 30px;">No students found in this class.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <p style="text-align: center; color: #888; padding: 30px;">Please select a class/subject to manage grades.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Grade Modal -->
<div id="gradeModal" class="modal">
    <div class="modal-content">
        <h3 style="margin: 0 0 20px 0; color: var(--primary-color);">Enter Grade</h3>
        <form method="POST">
            <input type="hidden" name="student_id" id="modal_student_id">
            <input type="hidden" name="schedule_id" value="<?php echo $selected_schedule; ?>">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; margin-bottom: 5px; display: block;">Student:</label>
                <p id="modal_student_name" style="margin: 0; color: #666;"></p>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; margin-bottom: 5px; display: block;">Grade:</label>
                <select name="grade" id="modal_grade" class="form-control" required>
                    <option value="">-- Select Grade --</option>
                    <?php foreach ($grade_options as $grade => $description): ?>
                        <option value="<?php echo $grade; ?>"><?php echo $description; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeGradeModal()" class="btn" style="background: #95a5a6;">Cancel</button>
                <button type="submit" name="save_grade" class="btn" style="background: #2ecc71;">Save Grade</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGradeModal(studentId, studentName, currentGrade) {
    document.getElementById('modal_student_id').value = studentId;
    document.getElementById('modal_student_name').textContent = studentName;
    document.getElementById('modal_grade').value = currentGrade || '';
    document.getElementById('gradeModal').classList.add('active');
}

function closeGradeModal() {
    document.getElementById('gradeModal').classList.remove('active');
}

// Close modal when clicking outside
document.getElementById('gradeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGradeModal();
    }
});
</script>

</body>
</html>
