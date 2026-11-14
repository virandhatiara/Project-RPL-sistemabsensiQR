<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(isset($data['judul']) ? $data['judul'] : 'Sistem Absensi'); ?> - SMK NU WARGABINANGUN</title>
    <!-- Prevent Sidebar FOUC / Flash on Reload -->
    <script>
        (function() {
            try {
                if (window.innerWidth > 768 && localStorage.getItem('sidebar_collapsed') === '1') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= e(BASEURL); ?>/css/style.css?v=<?= time(); ?>">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <?php 
    if (isset($_SESSION['user_id']) && empty($_SESSION['nama'])) {
        try {
            $dbHeader = new Database();
            $dbHeader->query("SELECT nama, username FROM users WHERE id = :id LIMIT 1");
            $dbHeader->bind('id', $_SESSION['user_id']);
            $uHeader = $dbHeader->single();
            if ($uHeader) {
                $_SESSION['nama'] = !empty($uHeader['nama']) ? $uHeader['nama'] : $uHeader['username'];
            }
        } catch (Exception $e) {}
    }
    ?>
    <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebar-overlay"></div>

        <!-- Sidebar -->
        <?php require_once '../views/components/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Navbar -->
            <?php require_once '../views/components/navbar.php'; ?>
            
            <!-- Page Content -->
            <div class="container-fluid px-0 pb-4">
    <?php else: ?>
    <!-- Halaman Publik (Login) -->
    <main class="login-page">
    <?php endif; ?>
