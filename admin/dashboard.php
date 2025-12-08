<?php
// C:\xampp\htdocs\School_Portal\admin\dashboard.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin(); // Ensure only admin can access

// Fetch Stats
$total_students = $conn->query("SELECT count(*) as count FROM users WHERE role='student'")->fetch_assoc()['count'];
$total_teachers = $conn->query("SELECT count(*) as count FROM users WHERE role='teacher'")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT count(*) as count FROM courses")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; margin: 0; }
        
        /* Welcome Header */
        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .welcome-header h2 {
            margin: 0;
            color: #0056b3;
            font-size: 1.5rem;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0056b3, #0077cc);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #17a2b8;
        }
        .stat-card.blue { border-left-color: #0056b3; }
        .stat-card.green { border-left-color: #28a745; }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin: 5px 0;
        }
        .stat-card .btn-link {
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid;
            border-radius: 6px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .stat-card .btn-link:hover {
            opacity: 0.8;
        }
        
        /* Quick Management */
        .section-title {
            color: #444;
            font-size: 1.2rem;
            margin: 0 0 20px 0;
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .quick-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: inherit;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #0056b3;
        }
        .quick-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        .quick-card .icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }
        .quick-card h4 {
            margin: 0;
            color: #333;
            font-size: 1rem;
        }
        
        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .welcome-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .welcome-header h2 {
                font-size: 1.3rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }
            .stat-card .number {
                font-size: 2rem;
            }
            
            .quick-links {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .quick-card {
                padding: 20px;
            }
            .quick-card .icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="welcome-header">
            <h2>Dashboard</h2>
            <div class="user-info">
                <span style="font-weight: 600; color: #555;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Students</h3>
                <p class="number"><?php echo $total_students; ?></p>
                <a href="users.php?role=student" class="btn-link" style="color: #17a2b8; border-color: #17a2b8;">View Details →</a>
            </div>
            <div class="stat-card blue">
                <h3>Total Teachers</h3>
                <p class="number"><?php echo $total_teachers; ?></p>
                <a href="users.php?role=teacher" class="btn-link" style="color: #0056b3; border-color: #0056b3;">View Details →</a>
            </div>
            <div class="stat-card green">
                <h3>Active Courses</h3>
                <p class="number"><?php echo $total_courses; ?></p>
                <a href="academics.php" class="btn-link" style="color: #28a745; border-color: #28a745;">Manage →</a>
            </div>
        </div>
        
        <!-- Quick Management -->
        <h3 class="section-title">Quick Management</h3>
        <div class="quick-links">
            <a href="users.php" class="quick-card">
                <div class="icon">👥</div>
                <h4>Manage Users</h4>
            </a>
            <a href="schedules.php" class="quick-card">
                <div class="icon">📅</div>
                <h4>Class Schedules</h4>
            </a>
            <a href="announcements.php" class="quick-card">
                <div class="icon">📢</div>
                <h4>Announcements</h4>
            </a>
        </div>
    </div>
</div>

</body>
</html>
