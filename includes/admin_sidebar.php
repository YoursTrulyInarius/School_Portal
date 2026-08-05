<?php
// C:\xampp\htdocs\School_Portal\includes\admin_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
// Check for pending enrollments
$pending_enrollments_count = 0;
if (isset($conn)) {
    // Suppress errors if table doesn't exist
    $enrollment_check_result = @$conn->query("SELECT COUNT(*) as count FROM enrollment_requests WHERE status = 'pending'");
    if ($enrollment_check_result && $enrollment_check_result->num_rows > 0) {
        $pending_enrollments_count = $enrollment_check_result->fetch_assoc()['count'];
    }
}
?>
<!-- Mobile Menu Toggle Button -->
<button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open Menu">☰</button>

<!-- Overlay for closing sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar Navigation -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header" style="padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.1);">
        <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="sidebar-logo" style="width: 70px; height: 70px; border-radius: 50%; margin-bottom: 12px; background: white; padding: 5px;">
        <h3 style="color: white; margin: 0; font-size: 1.1rem; font-weight: 600;">Admin Portal</h3>
    </div>
    <ul class="sidebar-menu" style="list-style: none; padding: 15px 0; flex: 1; margin: 0;">
        <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo $current_page == 'dashboard.php' ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Dashboard</a>
        </li>
        <li class="<?php echo ($current_page == 'users.php' || $current_page == 'user_form.php') ? 'active' : ''; ?>">
            <a href="users.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo ($current_page == 'users.php' || $current_page == 'user_form.php') ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Manage Users</a>
        </li>
        <li class="<?php echo $current_page == 'enrollments.php' ? 'active' : ''; ?>">
            <a href="enrollments.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo $current_page == 'enrollments.php' ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">
                Enrollments
                <?php if ($pending_enrollments_count > 0): ?>
                    <span style="background: #e74c3c; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; float: right; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        <?php echo $pending_enrollments_count; ?>
                    </span>
                <?php endif; ?>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
            <a href="payments.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo ($current_page == 'payments.php') ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Payments</a>
        </li>
        <li class="<?php echo ($current_page == 'academics.php' || $current_page == 'course_form.php' || $current_page == 'section_form.php') ? 'active' : ''; ?>">
            <a href="academics.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo ($current_page == 'academics.php' || $current_page == 'course_form.php' || $current_page == 'section_form.php') ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Academics</a>
        </li>
        <li class="<?php echo ($current_page == 'schedules.php' || $current_page == 'schedule_form.php') ? 'active' : ''; ?>">
            <a href="schedules.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo ($current_page == 'schedules.php' || $current_page == 'schedule_form.php') ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Schedules</a>
        </li>
        <li class="<?php echo $current_page == 'grades.php' ? 'active' : ''; ?>">
            <a href="grades.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo $current_page == 'grades.php' ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Grades</a>
        </li>
        <li class="<?php echo ($current_page == 'announcements.php' || $current_page == 'announcement_form.php') ? 'active' : ''; ?>">
            <a href="announcements.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo ($current_page == 'announcements.php' || $current_page == 'announcement_form.php') ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Announcements</a>
        </li>
        <li>
            <a href="../logout.php" style="display: block; padding: 14px 25px; color: #ffcccc; font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent;">Logout</a>
        </li>
    </ul>
</div>
