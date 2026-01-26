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
        $stmt = $conn->prepare("INSERT INTO grades (student_id, schedule_id, grade, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidi", $student_id, $schedule_id, $grade, $teacher_id);
    }
    
    if ($stmt->execute()) {
        $success = "Grade saved successfully!";
    } else {
        $error = "Error saving grade.";
    }
}

// Get selected section and schedule
$selected_section = isset($_GET['section']) ? clean_input($_GET['section']) : '';
$selected_schedule = isset($_GET['schedule']) ? clean_input($_GET['schedule']) : '';

// Fetch teacher's distinct sections
$sections_sql = "SELECT DISTINCT s.id, s.grade_level, s.section_name, st.strand_code
                 FROM schedules sch
                 JOIN sections s ON sch.section_id = s.id
                 LEFT JOIN strands st ON s.strand_id = st.id
                 WHERE sch.teacher_id = '$teacher_id' 
                 ORDER BY s.grade_level, s.section_name";
$sections_result = $conn->query($sections_sql);

// Fetch subjects for the selected section
$subjects_result = null;
if ($selected_section) {
    $subjects_sql = "SELECT sch.id, sch.subject, c.course_name as course_legacy
                     FROM schedules sch
                     LEFT JOIN courses c ON sch.course_id = c.id
                     WHERE sch.teacher_id = '$teacher_id' AND sch.section_id = '$selected_section'
                     ORDER BY sch.subject";
    $subjects_result = $conn->query($subjects_sql);
}

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


        <!-- Class/Subject Selector -->
        <div class="card" style="margin-bottom: 25px;">
            <h3 style="margin: 0 0 15px 0;">Select Class & Subject</h3>
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label style="font-weight: 600; margin-bottom: 5px; display: block;">Step 1: Select Course/Section</label>
                    <select name="section" class="form-control" onchange="this.form.schedule.value=''; this.form.submit()">
                        <option value="">-- Select Section --</option>
                        <?php while($sec = $sections_result->fetch_assoc()): ?>
                            <option value="<?php echo $sec['id']; ?>" <?php echo $selected_section == $sec['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sec['grade_level'] . ' - ' . $sec['section_name'] . ($sec['strand_code'] ? ' (' . $sec['strand_code'] . ')' : '')); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label style="font-weight: 600; margin-bottom: 5px; display: block;">Step 2: Select Subject</label>
                    <select name="schedule" class="form-control" onchange="this.form.submit()" <?php echo !$selected_section ? 'disabled' : ''; ?>>
                        <option value="">-- Select Subject --</option>
                        <?php if ($subjects_result): ?>
                            <?php while($subj = $subjects_result->fetch_assoc()): ?>
                                <option value="<?php echo $subj['id']; ?>" <?php echo $selected_schedule == $subj['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subj['subject'] ? $subj['subject'] : $subj['course_legacy']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
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
                <input type="number" id="percentage_input" class="form-control" placeholder="Enter Grade" oninput="convertToPointScale(this.value)">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; margin-bottom: 5px; display: block;">Point Grade:</label>
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
function convertToPointScale(val) {
    const score = parseFloat(val);
    const gradeSelect = document.getElementById('modal_grade');
    
    if (isNaN(score) || score < 0) {
        gradeSelect.value = "";
        return;
    }

    let pointGrade = "";
    if (score >= 98 && score <= 100) pointGrade = "1.00";
    else if (score >= 95 && score <= 97) pointGrade = "1.25";
    else if (score >= 92 && score <= 94) pointGrade = "1.50";
    else if (score >= 89 && score <= 91) pointGrade = "1.75";
    else if (score >= 86 && score <= 88) pointGrade = "2.00";
    else if (score >= 83 && score <= 85) pointGrade = "2.25";
    else if (score >= 80 && score <= 82) pointGrade = "2.50";
    else if (score >= 75 && score <= 79) pointGrade = "3.00";
    else if (score >= 70 && score <= 74) pointGrade = "4.00";
    else if (score < 75) pointGrade = "5.00";

    gradeSelect.value = pointGrade;
}

function openGradeModal(studentId, studentName, currentGrade) {
    document.getElementById('modal_student_id').value = studentId;
    document.getElementById('modal_student_name').textContent = studentName;
    document.getElementById('modal_grade').value = currentGrade || '';
    document.getElementById('percentage_input').value = ''; // Reset percentage input
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
