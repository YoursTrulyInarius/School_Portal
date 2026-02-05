<?php
// C:\xampp\htdocs\School_Portal\student\schedule.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}
$student_user_id = $_SESSION['user_id'];

// Fetch student info including section
$s_res = $conn->query("SELECT * FROM students WHERE user_id='$student_user_id'");
$student = $s_res->fetch_assoc();
$section_id = $student['section_id'];

// Fetch Schedules that match the student's section
$sch_sql = "SELECT sch.*, 
                   c.course_name as subject,
                   c.course_code,
                   t.firstname, 
                   t.lastname,
                   sch.room
            FROM schedules sch
            LEFT JOIN courses c ON sch.course_id = c.id
            LEFT JOIN teachers t ON sch.teacher_id = t.id
            WHERE sch.section_id = '$section_id'
            ORDER BY FIELD(sch.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sch.time";
$result = $conn->query($sch_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Westprime Horizon</title>
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
            color: #4169E1;
        }

        .day-badge {
            display: inline-block;
            background: #eef2f7;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #555;
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

            .day-badge {
                padding: 4px 8px;
                font-size: 0.75rem;
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
            <h2 class="page-title">My Schedule</h2>

            <div class="card">
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="subject-name"><?php echo htmlspecialchars($row['subject'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? '')); ?>
                                        </td>
                                        <td><span class="day-badge"><?php echo htmlspecialchars($row['day'] ?? ''); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['time'] ?? 'TBA'); ?></td>
                                        <td><?php echo htmlspecialchars($row['room'] ?: 'N/A'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No schedule found. Contact your adviser.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>
