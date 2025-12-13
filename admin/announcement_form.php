<?php
// C:\xampp\htdocs\School_Portal\admin\announcement_form.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
$is_edit = !empty($id);

$title = '';
$content = '';
$target_audience = 'all';
$error = '';

if ($is_edit) {
    $sql = "SELECT * FROM announcements WHERE id = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $content = $row['content'];
        $target_audience = $row['target_audience'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $target_audience = clean_input($_POST['target_audience']);
    
    if (empty($title) || empty($content)) {
        $error = "Title and Content are required.";
    }
    
    if (!$error) {
        $user_id = $_SESSION['user_id'];
        if ($is_edit) {
            $sql = "UPDATE announcements SET title='$title', content='$content', target_audience='$target_audience' WHERE id='$id'";
        } else {
            $sql = "INSERT INTO announcements (user_id, title, content, target_audience) VALUES ('$user_id', '$title', '$content', '$target_audience')";
        }
        
        if ($conn->query($sql) === TRUE) {
            header("Location: announcements.php?msg=Saved");
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
    <title><?php echo $is_edit ? 'Edit Announcement' : 'Post Announcement'; ?> - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }</style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);"><?php echo $is_edit ? 'Edit Announcement' : 'Post Announcement'; ?></h2>
            <a href="announcements.php" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Announcements</a>
        </div>
        
        <?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>
        
        <div class="card" style="max-width: 700px; margin: 0 auto;">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" class="form-control" required placeholder="Announcement Title">
                </div>
                
                <div class="form-group">
                    <label>Target Audience</label>
                    <select name="target_audience" class="form-control">
                        <option value="all" <?php echo $target_audience=='all'?'selected':''; ?>>All Users (Public)</option>
                        <option value="student" <?php echo $target_audience=='student'?'selected':''; ?>>Students Only</option>
                        <option value="teacher" <?php echo $target_audience=='teacher'?'selected':''; ?>>Teachers Only</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" class="form-control" rows="8" required placeholder="Write your announcement here..."><?php echo htmlspecialchars($content); ?></textarea>
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex: 1;"><?php echo $is_edit ? 'Update Announcement' : 'Post Announcement'; ?></button>
                    <a href="announcements.php" class="btn" style="background: #95a5a6; flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
