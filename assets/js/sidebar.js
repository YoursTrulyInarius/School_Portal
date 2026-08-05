// assets/js/sidebar.js
(function() {
    // Get elements
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar function
    function toggleSidebar() {
        if (!sidebar || !overlay || !toggleBtn) return;
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
            if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    });
    
    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && sidebar && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            if (toggleBtn) toggleBtn.innerHTML = '☰';
        }
    });
})();
