<?php
// C:\xampp\htdocs\School_Portal\includes\teacher_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
/* ============================================ */
/* RESPONSIVE MOBILE STYLES - TEACHER SIDEBAR  */
/* ============================================ */

/* Mobile Menu Toggle Button */
.mobile-menu-toggle {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 99999;
    background: linear-gradient(135deg, #0056b3, #0077cc);
    color: white;
    border: none;
    border-radius: 12px;
    width: 52px;
    height: 52px;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0, 86, 179, 0.5);
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    pointer-events: auto;
}

.mobile-menu-toggle:hover,
.mobile-menu-toggle:active {
    background: linear-gradient(135deg, #004494, #0056b3);
    transform: scale(1.05);
}

/* Sidebar Overlay */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9998;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
    pointer-events: auto;
}

/* Sidebar */
.sidebar {
    width: 260px;
    background: linear-gradient(180deg, #0056b3, #004494);
    color: white;
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
    z-index: 9999;
    overflow-y: auto;
    box-shadow: 4px 0 25px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
}

/* MOBILE STYLES */
@media screen and (max-width: 768px) {
    .mobile-menu-toggle {
        display: flex !important;
    }
    
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 80px 16px 20px 16px !important;
        width: 100% !important;
    }
    
    /* Dashboard grid - single column */
    .dashboard-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 16px !important;
    }
    
    /* Cards - full width */
    .card {
        width: 100% !important;
        margin: 0 !important;
    }
    
    /* Table container */
    .table-container,
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }
}

/* DESKTOP STYLES */
@media screen and (min-width: 769px) {
    .mobile-menu-toggle {
        display: none !important;
    }
    
    .sidebar-overlay {
        display: none !important;
    }
    
    .sidebar {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 260px;
    }
}
</style>

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

<script>
(function() {
    // Get elements
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar function
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Update button text
        if (sidebar.classList.contains('active')) {
            toggleBtn.innerHTML = '✕';
            toggleBtn.setAttribute('aria-label', 'Close Menu');
        } else {
            toggleBtn.innerHTML = '☰';
            toggleBtn.setAttribute('aria-label', 'Open Menu');
        }
    }
    
    // Event listeners
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
    }
    
    // Close sidebar when clicking menu links on mobile
    document.querySelectorAll('.sidebar-menu a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    });
    
    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            toggleBtn.innerHTML = '☰';
        }
    });
})();
</script>
