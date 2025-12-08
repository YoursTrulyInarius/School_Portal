<?php
// C:\xampp\htdocs\School_Portal\admin\academics.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

// Fetch College Courses
$sql_college = "SELECT * FROM courses WHERE description = 'College' ORDER BY course_code ASC";
$result_college = $conn->query($sql_college);

// Fetch Senior High Strands
$sql_seniorhigh = "SELECT * FROM courses WHERE description = 'Senior High' ORDER BY course_code ASC";
$result_seniorhigh = $conn->query($sql_seniorhigh);

// Fetch Sections
$sql_sections = "SELECT s.*, t.firstname, t.lastname 
        FROM sections s 
        LEFT JOIN teachers t ON s.adviser_id = t.id 
        ORDER BY s.grade_level, s.section_name ASC";
$result_sections = $conn->query($sql_sections);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics Management - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        
        /* Table Styles from Users.php for Consistency */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }
        .data-table th {
            padding: 15px;
            text-align: left;
            background: #0056b3; /* Darker background for visibility */
            color: white; /* White text for contrast */
            border-bottom: 2px solid #004494;
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
        }
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }
        .data-table tr:hover td {
            background: #f9f9f9;
        }
        
        .btn-edit {
            color: var(--primary-color); 
            margin-right: 15px; 
            text-decoration: none; 
            font-weight: 500;
        }
        .btn-delete {
            color: #e74c3c; 
            text-decoration: none; 
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="color: var(--primary-color); margin-bottom: 25px;">Academics Management</h2>

        <!-- COLLEGE COURSES SECTION -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; margin-top: 10px;">
            <h3 style="margin: 0; color: #555;">College Courses</h3>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_college->num_rows > 0): ?>
                            <?php while($row = $result_college->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($row['course_code']); ?></td>
                                    <td style="color: #333;"><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td>
                                        <a href="course_form.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="course_delete.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this course?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="padding: 30px; text-align: center; color: #888;">No college courses found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SENIOR HIGH STRANDS SECTION -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; color: #555;">Senior High Strands</h3>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Strand Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_seniorhigh->num_rows > 0): ?>
                            <?php while($row = $result_seniorhigh->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($row['course_code']); ?></td>
                                    <td style="color: #333;"><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td>
                                        <a href="course_form.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="course_delete.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this strand?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="padding: 30px; text-align: center; color: #888;">No senior high strands found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>

// Fetch College Courses
$sql_college = "SELECT * FROM courses WHERE description = 'College' ORDER BY course_code ASC";
$result_college = $conn->query($sql_college);

// Fetch Senior High Strands
$sql_seniorhigh = "SELECT * FROM courses WHERE description = 'Senior High' ORDER BY course_code ASC";
$result_seniorhigh = $conn->query($sql_seniorhigh);

// Fetch Sections
$sql_sections = "SELECT s.*, t.firstname, t.lastname 
        FROM sections s 
        LEFT JOIN teachers t ON s.adviser_id = t.id 
        ORDER BY s.grade_level, s.section_name ASC";
$result_sections = $conn->query($sql_sections);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics Management - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <h2 style="color: var(--primary-color); margin-bottom: 25px;">Academics Management</h2>

        <!-- COLLEGE COURSES SECTION -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; margin-top: 10px;">
            <h3 style="margin: 0; color: #555;">College Courses</h3>
        </div>

        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 40px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Code</th>
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Course Name</th>
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_college->num_rows > 0): ?>
                        <?php while($row = $result_college->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee; transition: background 0.1s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px; font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($row['course_code']); ?></td>
                                <td style="padding: 15px; color: #333;"><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td style="padding: 15px;">
                                    <a href="course_form.php?id=<?php echo $row['id']; ?>" style="color: var(--primary-color); margin-right: 15px; text-decoration: none; font-weight: 500;">Edit</a>
                                    <a href="course_delete.php?id=<?php echo $row['id']; ?>" style="color: #e74c3c; text-decoration: none; font-weight: 500;" onclick="return confirm('Delete this course?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="padding: 30px; text-align: center; color: #888;">No college courses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- SENIOR HIGH STRANDS SECTION -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; color: #555;">Senior High Strands</h3>
        </div>

        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 40px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Code</th>
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Strand Name</th>
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_seniorhigh->num_rows > 0): ?>
                        <?php while($row = $result_seniorhigh->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee; transition: background 0.1s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px; font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($row['course_code']); ?></td>
                                <td style="padding: 15px; color: #333;"><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td style="padding: 15px;">
                                    <a href="course_form.php?id=<?php echo $row['id']; ?>" style="color: var(--primary-color); margin-right: 15px; text-decoration: none; font-weight: 500;">Edit</a>
                                    <a href="course_delete.php?id=<?php echo $row['id']; ?>" style="color: #e74c3c; text-decoration: none; font-weight: 500;" onclick="return confirm('Delete this strand?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="padding: 30px; text-align: center; color: #888;">No senior high strands found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>



    </div>
</div>

</body>
</html>
