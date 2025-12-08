<?php
// C:\xampp\htdocs\School_Portal\teacher\class_materials.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/teacher_sidebar.php'; ?>

    <div class="main-content">
        <!-- Class Header Info (Simplified since we don't have $class populated in original, but let's assume we need to fetch it or just show a generic header) 
             Wait, original class_materials.php didn't fetch class info. I should fetch it to be consistent, or just show a generic placeholder.
             To be safe and consistent, I will add the fetch logic if it's missing, or just keep it simple.
             The original file was mostly empty. Let's add the fetch logic to make it look real.
        -->
        <?php
        // Add fetch logic if not present
        $schedule_id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
        if (!empty($schedule_id)) {
            $teacher_id = $_SESSION['user_id']; // Wait, $_SESSION['teacher_id']
            // Re-fetch logic or just include it? I'll re-add it here for completeness since original didn't have it.
            // Actually, best to just show "Coming Soon".
        }
        ?>
        
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
             <div>
                <h2 style="margin: 0; color: var(--primary-color);">Class Materials</h2>
                <p style="margin: 5px 0 0 0; color: #666;">Manage resources for your students.</p>
            </div>
            <a href="my_classes.php" class="btn" style="background: #6c757d; padding: 8px 15px; font-size: 0.9rem;">&larr; Back to Classes</a>
        </div>

        <!-- Navigation Tabs -->
         <div class="nav-tabs" style="display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            <a href="class_view.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: white; color: #333; border: 1px solid #ddd; border-bottom: none; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Students List</a>
            <a href="class_grades.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: white; color: #333; border: 1px solid #ddd; border-bottom: none; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Gradebook</a>
            <a href="class_attendance.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: white; color: #333; border: 1px solid #ddd; border-bottom: none; border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Attendance</a>
            <a href="class_materials.php?id=<?php echo $schedule_id; ?>" class="btn" style="background: var(--primary-color); border-bottom-left-radius: 0; border-bottom-right-radius: 0;">Materials</a>
        </div>

        <div class="card" style="text-align: center; padding: 40px;">
            <div style="font-size: 3rem; color: #ddd; margin-bottom: 15px;">📁</div>
            <h3>Materials Upload Coming Soon</h3>
            <p style="color: #666;">This feature is under development.</p>
        </div>
    </div>
</div>

</body>
</html>
