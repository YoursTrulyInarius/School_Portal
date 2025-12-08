<?php
// C:\xampp\htdocs\School_Portal\admin\grades.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

// Get filter
$filter_class = isset($_GET['class']) ? clean_input($_GET['class']) : '';

// Fetch all unique classes
$classes_sql = "SELECT DISTINCT class_year FROM students WHERE class_year IS NOT NULL ORDER BY class_year";
$classes_result = $conn->query($classes_sql);

// Fetch grades with filters
$where_clause = "";
if ($filter_class) {
    $where_clause = "WHERE s.class_year = '$filter_class'";
}

$grades_sql = "SELECT g.*, 
                      s.firstname, s.lastname, s.lrn, s.class_year
               FROM grades g
               JOIN students s ON g.student_id = s.id
               $where_clause
               ORDER BY s.class_year, s.lastname, s.firstname";
$grades_result = $conn->query($grades_sql);

// Grade scale reference
$grade_scale = [
    '1.00' => 'Excellent (97-100%)',
    '1.25' => 'Superior/Outstanding (94-97%)',
    '1.50' => 'Very Good/Superior (91-94%)',
    '1.75' => 'Very Good/Good (88-91%)',
    '2.00' => 'Satisfactory/Good (85-88%)',
    '2.25' => 'Satisfactory (82-84%)',
    '2.50' => 'Fairly Satisfactory (79-81%)',
    '3.00' => 'Pass (75-78%)',
    '4.00' => 'Conditional/Remedial',
    '5.00' => 'Failed (Below 75%)'
];

function getGradeColor($grade) {
    $g = floatval($grade);
    if ($g <= 1.50) return '#2ecc71'; // Green - Excellent/Superior
    if ($g <= 2.50) return '#3498db'; // Blue - Good/Satisfactory
    if ($g <= 3.00) return '#f39c12'; // Orange - Pass
    return '#e74c3c'; // Red - Failed/Conditional
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades Management - Westprime Horizon</title>
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
        .grade-scale h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .grade-scale ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 8px;
        }
        .grade-scale li {
            font-size: 0.85rem;
            color: #666;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; color: var(--primary-color);">Grades Management</h2>

        <!-- Grade Scale Reference -->
        <div class="grade-scale">
            <h4>📊 Grading Scale (1.00 - 5.00)</h4>
            <ul>
                <?php foreach ($grade_scale as $grade => $description): ?>
                    <li>
                        <span class="grade-badge" style="background: <?php echo getGradeColor($grade); ?>; padding: 2px 8px; font-size: 0.75rem;">
                            <?php echo $grade; ?>
                        </span>
                        <?php echo $description; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Filter -->
        <div class="card" style="margin-bottom: 25px;">
            <h3 style="margin: 0 0 15px 0;">Filter by Class</h3>
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <select name="class" class="form-control" style="max-width: 300px;" onchange="this.form.submit()">
                    <option value="">-- All Classes --</option>
                    <?php while($class = $classes_result->fetch_assoc()): ?>
                        <option value="<?php echo $class['class_year']; ?>" <?php echo $filter_class == $class['class_year'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_year']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if ($filter_class): ?>
                    <a href="grades.php" class="btn" style="background: #95a5a6; padding: 8px 15px;">Clear Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Grades Table -->
        <div class="card" style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #0056b3; border-bottom: 2px solid #004494;">
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">LRN</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Student Name</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Class</th>
                        <th style="padding: 15px; text-align: center; color: white; font-weight: 600;">Grade</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($grades_result->num_rows > 0): ?>
                        <?php while($grade = $grades_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; color: #666; font-family: monospace;">
                                    <?php echo htmlspecialchars($grade['lrn']); ?>
                                </td>
                                <td style="padding: 15px; font-weight: 600; color: #333;">
                                    <?php echo htmlspecialchars($grade['lastname'] . ', ' . $grade['firstname']); ?>
                                </td>
                                <td style="padding: 15px; color: #666;">
                                    <?php echo htmlspecialchars($grade['class_year']); ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <span class="grade-badge" style="background: <?php echo getGradeColor($grade['grade']); ?>;">
                                        <?php echo number_format($grade['grade'], 2); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #666; font-size: 0.9rem;">
                                    <?php echo isset($grade['created_at']) ? date('M d, Y', strtotime($grade['created_at'])) : '-'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #888;">
                                No grades found<?php echo $filter_class ? ' for this class' : ''; ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
