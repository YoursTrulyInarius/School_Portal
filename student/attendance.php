<?php
// C:\xampp\htdocs\School_Portal\student\attendance.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') { header("Location: ../index.php"); exit(); }
$student_user_id = $_SESSION['user_id'];
$s_res = $conn->query("SELECT id FROM students WHERE user_id='$student_user_id'");
$student = $s_res->fetch_assoc();
$student_id = $student['id'];

// Fetch Attendance
$sql = "SELECT a.*, c.course_name 
        FROM attendance a 
        JOIN courses c ON a.course_id = c.id 
        WHERE a.student_id = '$student_id' 
        ORDER BY a.date DESC";
$result = $conn->query($sql);

function getStatusBadge($status) {
    $s = strtolower($status);
    $label = ucfirst($s);
    if ($s == 'present') return "<span class='status-badge status-present'>$label</span>";
    if ($s == 'absent') return "<span class='status-badge status-absent'>$label</span>";
    if ($s == 'late') return "<span class='status-badge status-late'>$label</span>";
    return "<span class='status-badge'>$label</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; margin: 0; }
        
        .page-title {
            color: #0056b3;
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
            min-width: 350px;
        }
        
        .data-table thead tr {
            background: linear-gradient(135deg, #28a745, #34c759);
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
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-present {
            background: #d4edda;
            color: #28a745;
        }
        
        .status-absent {
            background: #f8d7da;
            color: #dc3545;
        }
        
        .status-late {
            background: #fff3cd;
            color: #e67e00;
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
            
            .status-badge {
                padding: 4px 10px;
                font-size: 0.8rem;
            }
        }
        
        @media screen and (max-width: 480px) {
            .data-table {
                min-width: 300px;
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
        <h2 class="page-title">My Attendance</h2>

        <div class="card">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td style="text-align: center;"><?php echo getStatusBadge($row['status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No attendance records found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
