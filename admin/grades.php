<?php
// C:\xampp\htdocs\School_Portal\admin\grades.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

// Get Navigation Parameters
$course_id = isset($_GET['course_id']) ? clean_input($_GET['course_id']) : '';
$strand_id = isset($_GET['strand_id']) ? clean_input($_GET['strand_id']) : '';
$section_id = isset($_GET['section_id']) ? clean_input($_GET['section_id']) : '';
$student_id = isset($_GET['student_id']) ? clean_input($_GET['student_id']) : '';

// 1. Breadcrumb / Context Logic
$breadcrumb = [['link' => 'grades.php', 'label' => 'Academic Records']];

if ($course_id) {
    $res = $conn->query("SELECT course_name, course_code FROM courses WHERE id = '$course_id'");
    if ($r = $res->fetch_assoc()) {
        $breadcrumb[] = ['link' => "grades.php?course_id=$course_id", 'label' => $r['course_code']];
    }
} elseif ($strand_id) {
    $res = $conn->query("SELECT strand_name, strand_code FROM strands WHERE id = '$strand_id'");
    if ($r = $res->fetch_assoc()) {
        $breadcrumb[] = ['link' => "grades.php?strand_id=$strand_id", 'label' => $r['strand_code']];
    }
}

if ($section_id) {
    $res = $conn->query("SELECT section_name FROM sections WHERE id = '$section_id'");
    if ($r = $res->fetch_assoc()) {
        $breadcrumb[] = ['link' => "grades.php?section_id=$section_id&" . ($course_id ? "course_id=$course_id" : "strand_id=$strand_id"), 'label' => $r['section_name']];
    }
}

if ($student_id) {
    $res = $conn->query("SELECT firstname, lastname FROM students WHERE id = '$student_id'");
    if ($r = $res->fetch_assoc()) {
        $breadcrumb[] = ['link' => '#', 'label' => $r['lastname'] . ', ' . $r['firstname']];
    }
}

// 2. Fetch Data based on View Level
$view = "programs";
if ($student_id) $view = "student_details";
elseif ($section_id) $view = "student_list";
elseif ($course_id || $strand_id) $view = "section_list";

if ($view == "programs") {
    $courses = $conn->query("SELECT * FROM courses ORDER BY course_code");
    $strands = $conn->query("SELECT * FROM strands ORDER BY strand_code");
} elseif ($view == "section_list") {
    $where = $course_id ? "course_id = '$course_id'" : "strand_id = '$strand_id'";
    $sections = $conn->query("SELECT * FROM sections WHERE $where ORDER BY year_level, block");
} elseif ($view == "student_list") {
    $students = $conn->query("SELECT * FROM students WHERE section_id = '$section_id' ORDER BY lastname, firstname");
} elseif ($view == "student_details") {
    // Transcript query with subject names from schedules
    $grades_res = $conn->query("SELECT g.*, sch.subject, t.firstname as t_first, t.lastname as t_last
                                FROM grades g
                                LEFT JOIN schedules sch ON g.schedule_id = sch.id
                                LEFT JOIN teachers t ON g.teacher_id = t.id
                                WHERE g.student_id = '$student_id'
                                ORDER BY sch.subject, g.date_recorded DESC");
                                
    $student_info = $conn->query("SELECT s.*, sec.section_name, sec.grade_level 
                                  FROM students s 
                                  LEFT JOIN sections sec ON s.section_id = sec.id 
                                  WHERE s.id = '$student_id'")->fetch_assoc();
}

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
    <title>Academic Records - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #004aad;
            --primary-dark: #003380;
            --secondary: #5ce1e6;
            --bg-light: #f8fafc;
        }
        body { font-family: 'Poppins', sans-serif; background: var(--bg-light); margin: 0; }
        .main-content { padding: 30px; max-width: 1200px; margin: 0 auto; }
        
        /* Breadcrumbs */
        .breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 25px; list-style: none; padding: 0; }
        .breadcrumb li { color: #64748b; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; }
        .breadcrumb li:not(:last-child):after { content: '/'; margin-left: 8px; color: #cbd5e1; }
        .breadcrumb a { color: var(--primary); text-decoration: none; transition: color 0.2s; }
        .breadcrumb a:hover { color: var(--primary-dark); text-decoration: underline; }
        
        .page-title { font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 30px; display: flex; align-items: center; gap: 12px; }

        /* Grid Layouts */
        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .data-card {
            background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px;
            text-decoration: none; color: inherit; transition: all 0.2s; position: relative; overflow: hidden;
            display: flex; flex-direction: column; align-items: center; text-align: center;
        }
        .data-card:hover { transform: translateY(-4px); border-color: var(--primary); box-shadow: 0 10px 25px rgba(0,74,173,0.1); }
        .data-card .icon { font-size: 2.5rem; margin-bottom: 15px; }
        .data-card h3 { margin: 0; color: #1e293b; font-size: 1.1rem; font-weight: 700; }
        .data-card p { margin: 5px 0 0 0; color: #64748b; font-size: 0.85rem; }
        
        /* Table Styles */
        .card-table { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .pro-table { width: 100%; border-collapse: collapse; }
        .pro-table th { background: #f8fafc; text-align: left; padding: 16px 20px; font-weight: 600; color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
        .pro-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
        .pro-table tbody tr:hover { background: #f8fafc; cursor: pointer; }
        .pro-table a { color: inherit; text-decoration: none; display: block; }
        
        .student-avatar { width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748b; font-size: 0.9rem; }
        .grade-badge { padding: 5px 12px; border-radius: 20px; color: white; font-weight: 700; font-size: 0.85rem; display: inline-block; min-width: 45px; text-align: center; }
        
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 12px; border: 2px dashed #e2e8f0; }
        .empty-state h3 { color: #64748b; margin-bottom: 10px; }
        
        @media (max-width: 768px) { .grid-container { grid-template-columns: 1fr; } .main-content { padding: 15px; } }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <!-- Breadcrumbs -->
        <nav>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumb as $i => $item): ?>
                    <li>
                        <?php if ($i < count($breadcrumb) - 1): ?>
                            <a href="<?php echo $item['link']; ?>"><?php echo htmlspecialchars($item['label']); ?></a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($item['label']); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <?php if ($view == "programs"): ?>
            <h2 class="page-title">📊 Choose a Program</h2>
            
            <h3 style="font-size: 1rem; color: #64748b; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.1em;">College Departments</h3>
            <div class="grid-container">
                <?php while ($c = $courses->fetch_assoc()): ?>
                <a href="?course_id=<?php echo $c['id']; ?>" class="data-card">
                    <div class="icon">🎓</div>
                    <h3><?php echo htmlspecialchars($c['course_code']); ?></h3>
                    <p><?php echo htmlspecialchars($c['course_name']); ?></p>
                </a>
                <?php endwhile; ?>
            </div>

            <h3 style="font-size: 1rem; color: #64748b; margin: 40px 0 15px 0; text-transform: uppercase; letter-spacing: 0.1em;">Senior High Tracks</h3>
            <div class="grid-container">
                <?php while ($s = $strands->fetch_assoc()): ?>
                <a href="?strand_id=<?php echo $s['id']; ?>" class="data-card">
                    <div class="icon">🏫</div>
                    <h3><?php echo htmlspecialchars($s['strand_code']); ?></h3>
                    <p><?php echo htmlspecialchars($s['strand_name']); ?></p>
                </a>
                <?php endwhile; ?>
            </div>

        <?php elseif ($view == "section_list"): ?>
            <h2 class="page-title">📁 Select Section</h2>
            <div class="grid-container">
                <?php if ($sections->num_rows > 0): ?>
                    <?php while ($sec = $sections->fetch_assoc()): ?>
                    <a href="?section_id=<?php echo $sec['id']; ?>&<?php echo $course_id ? "course_id=$course_id" : "strand_id=$strand_id"; ?>" class="data-card">
                        <div class="icon">👥</div>
                        <h3><?php echo htmlspecialchars($sec['section_name']); ?></h3>
                        <p><?php echo htmlspecialchars($sec['grade_level']); ?></p>
                    </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <h3>No sections found</h3>
                        <p>This program doesn't have any sections created yet.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($view == "student_list"): ?>
            <h2 class="page-title">📝 Student List</h2>
            <div class="card-table">
                <table class="pro-table">
                    <thead>
                        <tr>
                            <th width="10%"></th>
                            <th>Student Name</th>
                            <th>LRN Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students->num_rows > 0): ?>
                            <?php while ($stu = $students->fetch_assoc()): ?>
                            <tr onclick="window.location.href='?student_id=<?php echo $stu['id']; ?>&section_id=<?php echo $section_id; ?>&<?php echo $course_id ? "course_id=$course_id" : "strand_id=$strand_id"; ?>'">
                                <td>
                                    <div class="student-avatar"><?php echo strtoupper(substr($stu['firstname'], 0, 1) . substr($stu['lastname'], 0, 1)); ?></div>
                                </td>
                                <td><div style="font-weight: 600;"><?php echo htmlspecialchars($stu['lastname'] . ', ' . $stu['firstname']); ?></div></td>
                                <td style="font-family: monospace; color: #64748b;"><?php echo htmlspecialchars($stu['lrn']); ?></td>
                                <td><span style="color: var(--primary); font-weight: 600; font-size: 0.85rem;">View Transcript &rarr;</span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">No students enrolled in this section.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($view == "student_details"): ?>
            <h2 class="page-title">📜 Academic Transcript</h2>
            
            <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin:0; font-size: 1.4rem;"><?php echo htmlspecialchars($student_info['firstname'] . ' ' . $student_info['lastname']); ?></h3>
                    <p style="margin:5px 0 0 0; color: #64748b;"><strong><?php echo htmlspecialchars($student_info['grade_level']); ?></strong> - <?php echo htmlspecialchars($student_info['section_name']); ?></p>
                </div>
                <div style="text-align: right;">
                    <p style="margin:0; color: #64748b; font-size: 0.85rem;">LRN Tracking</p>
                    <p style="margin:0; font-weight: 700; font-family: monospace;"><?php echo htmlspecialchars($student_info['lrn']); ?></p>
                </div>
            </div>

            <div class="card-table">
                <table class="pro-table">
                    <thead>
                        <tr>
                            <th>Subject / Course</th>
                            <th>Assessment Type</th>
                            <th style="text-align: center;">Grade</th>
                            <th>Instructor</th>
                            <th>Term</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($grades_res->num_rows > 0): ?>
                            <?php while ($g = $grades_res->fetch_assoc()): ?>
                            <tr>
                                <td><div style="font-weight: 600;"><?php echo htmlspecialchars($g['subject'] ?: 'Unknown Subject'); ?></div></td>
                                <td><span style="font-size: 0.8rem; text-transform: uppercase; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;"><?php echo htmlspecialchars($g['grade_type'] ?: 'N/A'); ?></span></td>
                                <td style="text-align: center;">
                                    <span class="grade-badge" style="background: <?php echo getGradeColor($g['grade']); ?>;">
                                        <?php echo number_format($g['grade'], 2); ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.9rem;"><?php echo htmlspecialchars($g['t_last'] . ', ' . $g['t_first']); ?></td>
                                <td style="font-size: 0.85rem; color: #64748b;"><?php echo htmlspecialchars($g['term'] ?: 'N/A'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 60px; color: #64748b;"><div style="font-size: 2rem;">📭</div>No grades have been recorded for this student yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
