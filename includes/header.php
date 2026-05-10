<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Manufaktur Tas</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Manufaktur/assets/css/style.css">
</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <!-- Brand -->
            <div class="sidebar-brand">
                <a href="/Manufaktur/pages/dashboard.php">
                    <div class="sidebar-brand-icon">
                        <i class="bi bi-building-gear"></i>
                    </div>
                    <span class="sidebar-brand-text">Manufaktur Tas</span>
                </a>
            </div>

            <!-- Navigation -->
            <div class="sidebar-nav">
                <div class="sidebar-nav-label">MENU UTAMA</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"
                            href="/Manufaktur/pages/dashboard.php">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <?php if ($_SESSION['role'] == 'administrator'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'active' : ''; ?>"
                                href="/Manufaktur/pages/users/index.php">
                                <i class="bi bi-people-fill"></i>
                                <span>Kelola Users</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/materials/') !== false ? 'active' : ''; ?>"
                            href="/Manufaktur/pages/materials/index.php">
                            <i class="bi bi-box-seam-fill"></i>
                            <span>Materials</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/products/') !== false ? 'active' : ''; ?>"
                            href="/Manufaktur/pages/products/index.php">
                            <i class="bi bi-handbag-fill"></i>
                            <span>Products</span>
                        </a>
                    </li>

                    <?php if ($_SESSION['role'] == 'administrator'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/bom/') !== false ? 'active' : ''; ?>"
                                href="/Manufaktur/pages/bom/index.php">
                                <i class="bi bi-list-check"></i>
                                <span>Bill of Materials</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <?php if ($_SESSION['role'] == 'gudang' || $_SESSION['role'] == 'administrator'): ?>
                    <div class="sidebar-nav-label">PRODUKSI</div>
                    <ul class="nav flex-column">
                        <?php if ($_SESSION['role'] == 'gudang'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/produksi/') !== false ? 'active' : ''; ?>"
                                    href="/Manufaktur/pages/produksi/index.php">
                                    <i class="bi bi-gear-fill"></i>
                                    <span>Produksi</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] == 'administrator'): ?>
                            <li class="nav-item">
                                <?php
                                $stmt_pending = $pdo->query("SELECT COUNT(*) as total FROM produksi WHERE status = 'proses'");
                                $pending_count = $stmt_pending->fetch()['total'];
                                ?>
                                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/produksi/') !== false ? 'active' : ''; ?>"
                                    href="/Manufaktur/pages/produksi/validasi.php">
                                    <i class="bi bi-clipboard-check-fill"></i>
                                    <span>Validasi Produksi</span>
                                    <?php if ($pending_count > 0): ?>
                                        <span class="sidebar-badge"><?php echo $pending_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>

                <div class="sidebar-nav-label">LAINNYA</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/log/') !== false ? 'active' : ''; ?>"
                            href="/Manufaktur/pages/log/index.php">
                            <i class="bi bi-clock-history"></i>
                            <span>Log Aktivitas</span>
                        </a>
                    </li>

                    <?php if ($_SESSION['role'] == 'administrator'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/laporan/') !== false ? 'active' : ''; ?>"
                                href="/Manufaktur/pages/laporan/index.php">
                                <i class="bi bi-bar-chart-line-fill"></i>
                                <span>Laporan</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>"
                            href="/Manufaktur/pages/profile.php">
                            <i class="bi bi-person-fill-gear"></i>
                            <span>Profil Saya</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- User Info + Logout -->
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                    </div>
                    <div class="sidebar-user-info">
                        <span class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                        <span class="sidebar-user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                </div>
                <a href="/Manufaktur/pages/logout.php" class="sidebar-logout">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Mobile Toggle -->
        <button class="sidebar-toggle d-md-none" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>

        <!-- Overlay for mobile -->
        <div class="sidebar-overlay d-md-none" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <?php echo $_SESSION['warning'];
                    unset($_SESSION['warning']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <main class="main-content-full">
            <?php endif; ?>