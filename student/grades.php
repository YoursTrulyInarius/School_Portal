<?php
// C:\xampp\htdocs\School_Portal\student\grades.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}
$student_user_id = $_SESSION['user_id'];
$s_res = $conn->query("SELECT id FROM students WHERE user_id='$student_user_id'");
$student = $s_res->fetch_assoc();
$student_id = $student['id'];

// Fetch Grades with subject names and teacher information
$sql = "SELECT 
            COALESCE(sch.subject, c.course_name) AS subject_name,
            CONCAT(t.firstname, ' ', t.lastname) AS teacher_name,
            g.grade
        FROM grades g 
        LEFT JOIN schedules sch ON g.schedule_id = sch.id
        LEFT JOIN courses c ON sch.course_id = c.id 
        LEFT JOIN teachers t ON g.teacher_id = t.id
        WHERE g.student_id = '$student_id' AND g.grade IS NOT NULL
        ORDER BY subject_name";
$grades_res = $conn->query($sql);

function getGradeColor($grade)
{
    $g = floatval($grade);
    if ($g <= 1.50)
        return '#2ecc71';
    if ($g <= 2.50)
        return '#3498db';
    if ($g <= 3.00)
        return '#f39c12';
    return '#e74c3c';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
        }

        .page-title {
            color: #4169E1;
            font-size: 1.5rem;
            margin: 0 0 20px 0;
            font-weight: 600;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 400px;
        }

        .data-table thead tr {
            background: #4169E1;
        }

        .data-table th {
            text-align: left;
            padding: 16px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .subject-name {
            font-weight: 600;
            color: #333;
        }

        .teacher-name {
            color: #666;
        }

        .grade-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #888;
        }

        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .page-title {
                font-size: 1.3rem;
                margin-bottom: 16px;
            }

            .data-table th,
            .data-table td {
                padding: 12px 10px;
                font-size: 0.85rem;
            }

            .grade-badge {
                padding: 5px 10px;
                font-size: 0.8rem;
            }

            .desktop-only {
                display: none;
            }

            .mobile-only {
                display: block;
            }

            .mobile-card {
                background: white;
                border: 1px solid #eee;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 15px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            }
        }

        @media screen and (min-width: 769px) {
            .mobile-only {
                display: none;
            }
        }

        @media screen and (max-width: 480px) {
            .data-table {
                min-width: 350px;
            }

            .data-table th,
            .data-table td {
                padding: 10px 8px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <?php include '../includes/student_sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">My Grades</h2>

            <div class="card">
                <?php
                // Fetch all grades into an array first
                $grades_data = [];
                if ($grades_res->num_rows > 0) {
                    while ($row = $grades_res->fetch_assoc()) {
                        $grades_data[] = $row;
                    }
                }

                if (!empty($grades_data)):
                    ?>
                    <!-- Desktop Table View -->
                    <div class="table-container desktop-only">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th style="text-align: center;">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades_data as $grade): ?>
                                    <tr>
                                        <td class="subject-name">
                                            <?php echo htmlspecialchars($grade['subject_name'] ?: 'Unknown Subject'); ?>
                                        </td>
                                        <td class="teacher-name">
                                            <?php echo htmlspecialchars($grade['teacher_name'] ?: 'N/A'); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="grade-badge"
                                                style="background: <?php echo getGradeColor($grade['grade']); ?>;">
                                                <?php echo number_format($grade['grade'], 2); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="mobile-only">
                        <?php foreach ($grades_data as $grade): ?>
                            <div class="mobile-card">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                    <div>
                                        <div class="subject-name" style="font-size: 1rem; margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($grade['subject_name'] ?: 'Unknown Subject'); ?>
                                        </div>
                                        <div class="teacher-name" style="font-size: 0.85rem;">
                                            👨‍🏫 <?php echo htmlspecialchars($grade['teacher_name'] ?: 'N/A'); ?>
                                        </div>
                                    </div>
                                    <span class="grade-badge"
                                        style="background: <?php echo getGradeColor($grade['grade']); ?>; font-size: 0.9rem;">
                                        <?php echo number_format($grade['grade'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 15px;">📝</div>
                        <h3>No Grades Recorded</h3>
                        <p>Your grades have not been posted yet.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>

</html>
