// Sistem Absensi — General JS
document.addEventListener('DOMContentLoaded', function () {
    console.log('Sistem Absensi SMK — Connected');

    // Auto-dismiss flash alerts after 4 seconds
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            const btn = el.querySelector('.btn-close');
            if (btn) btn.click();
        }, 4000);
    });

    // Sidebar Toggle Logic (Desktop & Mobile)
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function isMobile() {
        return window.innerWidth <= 768;
    }

    // Sync body class if html was pre-marked with sidebar-collapsed
    if (document.documentElement.classList.contains('sidebar-collapsed')) {
        document.body.classList.add('sidebar-collapsed');
    } else {
        // Also check localStorage in case head script was bypassed
        try {
            if (!isMobile() && localStorage.getItem('sidebar_collapsed') === '1') {
                document.body.classList.add('sidebar-collapsed');
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    }

    function openMobileSidebar() {
        if (sidebar) sidebar.classList.add('show');
        if (overlay) overlay.classList.add('show');
    }

    function closeMobileSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
    }

    function collapseDesktopSidebar() {
        document.body.classList.add('sidebar-collapsed');
        document.documentElement.classList.add('sidebar-collapsed');
        try {
            localStorage.setItem('sidebar_collapsed', '1');
        } catch (e) {}
    }

    function expandDesktopSidebar() {
        document.body.classList.remove('sidebar-collapsed');
        document.documentElement.classList.remove('sidebar-collapsed');
        try {
            localStorage.setItem('sidebar_collapsed', '0');
        } catch (e) {}
    }

    function toggleSidebar() {
        if (isMobile()) {
            if (sidebar && sidebar.classList.contains('show')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        } else {
            if (document.body.classList.contains('sidebar-collapsed')) {
                expandDesktopSidebar();
            } else {
                collapseDesktopSidebar();
            }
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            toggleSidebar();
        });
    }

    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (isMobile()) {
                closeMobileSidebar();
            } else {
                collapseDesktopSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            closeMobileSidebar();
        });
    }

    // Handle screen resize
    window.addEventListener('resize', function () {
        if (!isMobile()) {
            closeMobileSidebar();
        }
    });
});

