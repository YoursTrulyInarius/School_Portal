<?php
// C:\xampp\htdocs\School_Portal\admin\sections.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

// Join to get adviser name
$sql = "SELECT s.*, t.firstname, t.lastname 
        FROM sections s 
        LEFT JOIN teachers t ON s.adviser_id = t.id 
        ORDER BY s.grade_level, s.section_name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);">Manage Sections</h2>
            <a href="section_form.php" class="btn">Add New Section</a>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #0056b3; border-bottom: 2px solid #004494;">
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Grade Level</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Section Name</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Adviser</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee; transition: background 0.1s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px; color: #666;"><?php echo htmlspecialchars($row['grade_level']); ?></td>
                                <td style="padding: 15px; font-weight: 600; color: #333;"><?php echo htmlspecialchars($row['section_name']); ?></td>
                                <td style="padding: 15px;">
                                    <?php 
                                    if ($row['firstname']) {
                                        echo '<span style="color: var(--primary-dark); font-weight: 500;">' . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . '</span>';
                                    } else {
                                        echo '<span style="color:#999; font-style: italic;">None</span>';
                                    }
                                    ?>
                                </td>
                                <td style="padding: 15px;">
                                    <a href="section_form.php?id=<?php echo $row['id']; ?>" style="color: var(--primary-color); margin-right: 15px; text-decoration: none; font-weight: 500;">Edit</a>
                                    <a href="section_delete.php?id=<?php echo $row['id']; ?>" style="color: #e74c3c; text-decoration: none; font-weight: 500;" onclick="return confirm('Delete this section?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 30px; text-align: center; color: #888;">No sections found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
