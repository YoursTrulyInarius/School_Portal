<?php
// C:\xampp\htdocs\School_Portal\teacher\announcements.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'teacher') { header("Location: ../index.php"); exit(); }

// Fetch All Announcements
$sql = "SELECT * FROM announcements WHERE target_audience IN ('all', 'teacher') ORDER BY created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f4f8; margin: 0; }
        
        .page-title {
            color: #0056b3;
            font-size: 1.5rem;
            margin: 0 0 20px 0;
        }
        
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        .announcement-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .announcement-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .announcement-title {
            color: #004494;
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }
        
        .announcement-date {
            color: #888;
            font-size: 0.8rem;
            display: block;
            margin-bottom: 10px;
        }
        
        .announcement-content {
            color: #444;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.3rem;
            }
            
            .card {
                padding: 18px;
            }
            
            .announcement-title {
                font-size: 1rem;
            }
            
            .announcement-content {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/teacher_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="margin-bottom: 30px; color: var(--primary-color);">Announcements</h2>

        <div class="dashboard-grid">
            <div class="card" style="grid-column: span 3;">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($ann = $result->fetch_assoc()): ?>
                        <div style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                            <h3 style="color: var(--primary-dark); margin-bottom: 5px;"><?php echo htmlspecialchars($ann['title']); ?></h3>
                            <small style="color: #888; display: block; margin-bottom: 10px;">
                                Posted on <?php echo date('F d, Y h:i A', strtotime($ann['created_at'])); ?>
                            </small>
                            <div style="color: #444; line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No announcements found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
