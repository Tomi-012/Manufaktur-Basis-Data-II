<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

cekLogin();

$page_title = 'Dashboard';

// Ambil statistik berdasarkan role
$stats = [];

if ($_SESSION['role'] == 'administrator') {
    // Admin melihat semua data
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials");
    $stats['total_materials'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $stats['total_products'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produksi WHERE status = 'selesai'");
    $stats['total_produksi'] = $stmt->fetch()['total'];
    
    // Produksi menunggu validasi
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM produksi WHERE status = 'proses'");
    $stats['produksi_pending'] = $stmt->fetch()['total'];
    
    // Material dengan stok menipis
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials WHERE stok <= stok_minimum");
    $stats['material_menipis'] = $stmt->fetch()['total'];
    
    // Aktivitas terakhir
    $stmt = $pdo->query("
        SELECT la.*, u.nama 
        FROM log_aktivitas la
        JOIN users u ON la.user_id = u.id
        ORDER BY la.tanggal DESC
        LIMIT 10
    ");
    $aktivitas_terakhir = $stmt->fetchAll();
    
    // Material paling banyak digunakan
    $stmt = $pdo->query("
        SELECT m.nama_material, m.satuan, SUM(pd.jumlah_terpakai) as total_terpakai
        FROM produksi_detail pd
        JOIN materials m ON pd.material_id = m.id
        GROUP BY pd.material_id
        ORDER BY total_terpakai DESC
        LIMIT 5
    ");
    $material_terbanyak = $stmt->fetchAll();
    
} elseif ($_SESSION['role'] == 'procurement') {
    // Procurement melihat data material
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials");
    $stats['total_materials'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM materials WHERE stok <= stok_minimum");
    $stats['material_menipis'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM material_masuk WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['transaksi_saya'] = $stmt->fetch()['total'];
    
    // Material dengan stok menipis
    $stmt = $pdo->query("
        SELECT m.*, k.nama_kategori
        FROM materials m
        JOIN kategori_material k ON m.kategori_id = k.id
        WHERE m.stok <= m.stok_minimum
        ORDER BY m.stok ASC
        LIMIT 10
    ");
    $material_menipis = $stmt->fetchAll();
    
} else { // gudang
    // Gudang melihat data produksi
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $stats['total_products'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM produksi WHERE user_id = ? AND status = 'selesai'");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['produksi_saya'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(stok) as total FROM products");
    $stats['total_stok_produk'] = $stmt->fetch()['total'] ?? 0;
    
    // Produk dengan stok rendah
    $stmt = $pdo->query("
        SELECT * FROM products
        ORDER BY stok ASC
        LIMIT 10
    ");
    $produk_stok = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
    <div class="text-muted">
        <i class="bi bi-calendar"></i> <?php echo date('d F Y, H:i'); ?>
    </div>
</div>

<!-- Statistik Cards -->
<div class="row mb-4">
    <?php if ($_SESSION['role'] == 'administrator'): ?>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Users</h6>
                        <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                    </div>
                    <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Materials</h6>
                        <h2 class="mb-0"><?php echo $stats['total_materials']; ?></h2>
                    </div>
                    <div class="text-success" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Products</h6>
                        <h2 class="mb-0"><?php echo $stats['total_products']; ?></h2>
                    </div>
                    <div class="text-warning" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-bag"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <a href="/Manufaktur/pages/produksi/validasi.php" class="text-decoration-none">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Menunggu Validasi</h6>
                        <h2 class="mb-0"><?php echo $stats['produksi_pending']; ?></h2>
                    </div>
                    <div class="text-danger" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>
    
    <?php elseif ($_SESSION['role'] == 'procurement'): ?>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Materials</h6>
                        <h2 class="mb-0"><?php echo $stats['total_materials']; ?></h2>
                    </div>
                    <div class="text-success" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Material Menipis</h6>
                        <h2 class="mb-0"><?php echo $stats['material_menipis']; ?></h2>
                    </div>
                    <div class="text-danger" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Transaksi Saya</h6>
                        <h2 class="mb-0"><?php echo $stats['transaksi_saya']; ?></h2>
                    </div>
                    <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: // gudang ?>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Products</h6>
                        <h2 class="mb-0"><?php echo $stats['total_products']; ?></h2>
                    </div>
                    <div class="text-warning" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-bag"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Produksi Saya</h6>
                        <h2 class="mb-0"><?php echo $stats['produksi_saya']; ?></h2>
                    </div>
                    <div class="text-success" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-gear"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Stok Produk</h6>
                        <h2 class="mb-0"><?php echo $stats['total_stok_produk']; ?></h2>
                    </div>
                    <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<!-- Content berdasarkan role -->
<div class="row">
    <?php if ($_SESSION['role'] == 'administrator'): ?>
    
    <!-- Material Menipis Alert -->
    <?php if ($stats['material_menipis'] > 0): ?>
    <div class="col-12 mb-3">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Perhatian!</strong> Ada <?php echo $stats['material_menipis']; ?> material dengan stok menipis.
            <a href="/Manufaktur/pages/materials/index.php" class="alert-link">Lihat detail</a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Material Terbanyak Digunakan -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Material Paling Banyak Digunakan
            </div>
            <div class="card-body">
                <?php if (empty($material_terbanyak)): ?>
                <p class="text-muted text-center py-3">Belum ada data produksi</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Total Terpakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($material_terbanyak as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['nama_material']); ?></td>
                                <td><strong><?php echo number_format($m['total_terpakai'], 2); ?></strong> <?php echo $m['satuan']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Aktivitas Terakhir -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Aktivitas Terakhir
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aktivitas_terakhir as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['nama']); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($log['aksi']); ?></span>
                                </td>
                                <td><small><?php echo date('d/m/Y H:i', strtotime($log['tanggal'])); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($_SESSION['role'] == 'procurement'): ?>
    
    <!-- Material Menipis -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle"></i> Material dengan Stok Menipis
            </div>
            <div class="card-body">
                <?php if (empty($material_menipis)): ?>
                <p class="text-muted text-center py-3">Semua material memiliki stok yang cukup</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Nama Material</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Minimum</th>
                                <th>Satuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($material_menipis as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['nama_kategori']); ?></td>
                                <td><?php echo htmlspecialchars($m['nama_material']); ?></td>
                                <td><span class="badge bg-danger"><?php echo number_format($m['stok'], 2); ?></span></td>
                                <td><?php echo number_format($m['stok_minimum'], 2); ?></td>
                                <td><?php echo htmlspecialchars($m['satuan']); ?></td>
                                <td>
                                    <a href="/Manufaktur/pages/materials/tambah_stok.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle"></i> Tambah Stok
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php else: // gudang ?>
    
    <!-- Stok Produk -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-boxes"></i> Stok Produk
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Deskripsi</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produk_stok as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['nama_produk']); ?></td>
                                <td><?php echo htmlspecialchars($p['deskripsi']); ?></td>
                                <td>
                                    <span class="badge <?php echo $p['stok'] < 10 ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $p['stok']; ?> unit
                                    </span>
                                </td>
                                <td>
                                    <a href="/Manufaktur/pages/produksi/proses.php?product_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-gear"></i> Produksi
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
