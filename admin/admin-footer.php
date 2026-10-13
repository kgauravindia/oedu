    </div> <!-- End .admin-main -->
</div> <!-- End .admin-layout -->

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin Core Interactive Scripts -->
<script>
    // Desktop sidebar collapse toggle with persistence
    function toggleSidebarCollapse() {
        const layout = document.querySelector('.admin-layout');
        const icon = document.getElementById('sidebarCollapseIcon');
        if (!layout) return;

        layout.classList.toggle('sidebar-collapsed');
        const isCollapsed = layout.classList.contains('sidebar-collapsed');
        localStorage.setItem('oedu_admin_sidebar_collapsed', isCollapsed ? '1' : '0');

        if (icon) {
            icon.className = isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
        }
    }

    // Restore desktop sidebar collapse state on page load
    (function initSidebar() {
        if (window.innerWidth >= 992) {
            const isCollapsed = localStorage.getItem('oedu_admin_sidebar_collapsed') === '1';
            const layout = document.querySelector('.admin-layout');
            const icon = document.getElementById('sidebarCollapseIcon');
            if (isCollapsed && layout) {
                layout.classList.add('sidebar-collapsed');
                if (icon) {
                    icon.className = 'fas fa-chevron-right';
                }
            }
        }
    })();

    // Mobile sidebar drawer toggle
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (sidebar && backdrop) {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        }
    }

    // Auto-fade dismissible bootstrap alerts after 4 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-auto-dismiss');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 4000);
</script>
</body>
</html>
