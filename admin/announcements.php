<?php
// C:\xampp\htdocs\School_Portal\admin\announcements.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Announcements - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);">System Announcements</h2>
            <a href="announcement_form.php" class="btn">Post Announcement</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="dashboard-grid" style="grid-template-columns: 1fr;">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card" style="border-left: 5px solid #28a745;">
                    <div style="display: flex; justify-content: space-between;">
                        <h3 style="margin-top: 0; color: #333;"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <small style="color: #888;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></small>
                    </div>
                    <p style="margin: 5px 0;">
                        <strong>Target:</strong> 
                        <span style="background: #eef2f7; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; color: #555;">
                            <?php echo ucfirst($row['target_audience']); ?>
                        </span>
                    </p>
                    <div style="margin: 15px 0; white-space: pre-wrap; color: #555; line-height: 1.6;"><?php echo htmlspecialchars($row['content']); ?></div>
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="announcement_form.php?id=<?php echo $row['id']; ?>" class="btn" style="background: transparent; color: #17a2b8; border: 1px solid #17a2b8; font-size: 0.85rem; margin-right: 5px;">Edit</a>
                        <a href="announcement_delete.php?id=<?php echo $row['id']; ?>" class="btn" style="background: transparent; color: #dc3545; border: 1px solid #dc3545; font-size: 0.85rem;" onclick="return confirm('Delete?');">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; color: #888; padding: 40px;">
                <p>No announcements posted.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
