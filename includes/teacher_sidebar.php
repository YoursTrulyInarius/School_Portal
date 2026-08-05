<?php
// C:\xampp\htdocs\School_Portal\includes\teacher_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Menu Toggle Button -->
<button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open Menu">☰</button>

<!-- Overlay for closing sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar Navigation -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header" style="padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.1);">
        <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Logo" class="sidebar-logo" style="width: 70px; height: 70px; border-radius: 50%; margin-bottom: 12px; background: white; padding: 5px;">
        <h3 style="color: white; margin: 0; font-size: 1.1rem; font-weight: 600;">Teacher Portal</h3>
    </div>
    <ul class="sidebar-menu" style="list-style: none; padding: 15px 0; flex: 1; margin: 0;">
        <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo $current_page == 'dashboard.php' ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Dashboard</a>
        </li>
        <li class="<?php echo ($current_page == 'schedule.php' || $current_page == 'class_view.php' || $current_page == 'class_grades.php' || $current_page == 'class_attendance.php' || $current_page == 'class_materials.php') ? 'active' : ''; ?>">
            <a href="schedule.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo ($current_page == 'schedule.php' || $current_page == 'class_view.php' || $current_page == 'class_grades.php' || $current_page == 'class_attendance.php' || $current_page == 'class_materials.php') ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">My Schedule</a>
        </li>
        <li class="<?php echo $current_page == 'grades.php' ? 'active' : ''; ?>">
            <a href="grades.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo $current_page == 'grades.php' ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Grades</a>
        </li>
        <li class="<?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>">
            <a href="announcements.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo $current_page == 'announcements.php' ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">Announcements</a>
        </li>
        <li class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <a href="profile.php" style="display: block; padding: 14px 25px; color: rgba(255,255,255,0.85); font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent; <?php echo $current_page == 'profile.php' ? 'background: rgba(255,255,255,0.15); color: white; border-left-color: white;' : ''; ?>">My Profile</a>
        </li>
        <li>
            <a href="../logout.php" style="display: block; padding: 14px 25px; color: #ffcccc; font-weight: 500; transition: all 0.2s; border-left: 4px solid transparent;">Logout</a>
        </li>
    </ul>
</div>
