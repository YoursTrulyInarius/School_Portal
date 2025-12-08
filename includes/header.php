<?php
// C:\xampp\htdocs\School_Portal\includes\header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Westprime Horizon Institute</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <header>
        <div class="container header-content">
            <div class="logo-area">
                <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Westprime Logo">
                <h1>Westprime Horizon</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <?php endif; ?>
    <div class="container">
