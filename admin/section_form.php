<?php
// C:\xampp\htdocs\School_Portal\admin\section_form.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
$is_edit = !empty($id);
$section_name = '';
$grade_level = '';
$adviser_id = '';
$error = '';

// Fetch Teachers for dropdown
$teachers_res = $conn->query("SELECT id, firstname, lastname FROM teachers ORDER BY lastname ASC");

if ($is_edit) {
    $sql = "SELECT * FROM sections WHERE id = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $section_name = $row['section_name'];
        $grade_level = $row['grade_level'];
        $adviser_id = $row['adviser_id'];
    } else {
        header("Location: academics.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_name = clean_input($_POST['section_name']);
    $grade_level = clean_input($_POST['grade_level']);
    $adviser_id = clean_input($_POST['adviser_id']);
    
    // Allow empty adviser? Yes.
    $adviser_val = empty($adviser_id) ? "NULL" : "'$adviser_id'";

    if (empty($section_name) || empty($grade_level)) {
        $error = "Name and Grade Level are required.";
    }
    
    if (!$error) {
        if ($is_edit) {
            $sql = "UPDATE sections SET section_name='$section_name', grade_level='$grade_level', adviser_id=$adviser_val WHERE id='$id'";
        } else {
            $sql = "INSERT INTO sections (section_name, grade_level, adviser_id) VALUES ('$section_name', '$grade_level', $adviser_val)";
        }
        
        if ($conn->query($sql) === TRUE) {
            header("Location: academics.php?msg=Saved");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Section' : 'Add New Section'; ?> - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);"><?php echo $is_edit ? 'Edit Section' : 'Add New Section'; ?></h2>
            <a href="academics.php" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Academics</a>
        </div>
        
        <?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Grade Level</label>
                    <select name="grade_level" class="form-control" required>
                        <option value="">Select Grade</option>
                        <?php 
                        $grades = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
                        foreach ($grades as $g) {
                            $sel = ($g == $grade_level) ? 'selected' : '';
                            echo "<option value='$g' $sel>$g</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Section Name</label>
                    <input type="text" name="section_name" value="<?php echo htmlspecialchars($section_name); ?>" class="form-control" required placeholder="e.g. Rizal">
                </div>
                
                <div class="form-group">
                    <label>Adviser (Teacher)</label>
                    <select name="adviser_id" class="form-control">
                        <option value="">-- Select Adviser --</option>
                        <?php while($t = $teachers_res->fetch_assoc()): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $adviser_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['lastname'] . ', ' . $t['firstname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex: 1;"><?php echo $is_edit ? 'Update Section' : 'Create Section'; ?></button>
                    <a href="academics.php" class="btn" style="background: #95a5a6; flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
