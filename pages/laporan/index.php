<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$page_title = 'Laporan Aggregate';

// 1. Material paling banyak digunakan
$stmt = $pdo->query("
    SELECT m.nama_material, m.satuan, SUM(pd.jumlah_terpakai) as total_terpakai
    FROM produksi_detail pd
    JOIN materials m ON pd.material_id = m.id
    GROUP BY pd.material_id
    ORDER BY total_terpakai DESC
    LIMIT 10
");
$material_terbanyak = $stmt->fetchAll();

// 2. Produk paling banyak diproduksi
$stmt = $pdo->query("
    SELECT pr.nama_produk, SUM(p.jumlah_produksi) as total_produksi
    FROM produksi p
    JOIN products pr ON p.product_id = pr.id
    WHERE p.status = 'selesai'
    GROUP BY p.product_id
    ORDER BY total_produksi DESC
    LIMIT 10
");
$produk_terbanyak = $stmt->fetchAll();

// 3. User paling aktif
$stmt = $pdo->query("
    SELECT u.nama, u.role, COUNT(la.id) as total_aktivitas
    FROM log_aktivitas la
    JOIN users u ON la.user_id = u.id
    GROUP BY la.user_id
    ORDER BY total_aktivitas DESC
    LIMIT 10
");
$user_teraktif = $stmt->fetchAll();

// 4. Material dengan stok menipis
$stmt = $pdo->query("
    SELECT m.*, k.nama_kategori
    FROM materials m
    JOIN kategori_material k ON m.kategori_id = k.id
    WHERE m.stok <= m.stok_minimum
    ORDER BY (m.stok / m.stok_minimum) ASC
");
$material_menipis = $stmt->fetchAll();

// 5. Statistik umum
$stmt = $pdo->query("SELECT COUNT(*) as total FROM produksi WHERE status = 'selesai'");
$total_produksi_selesai = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(jumlah_produksi) as total FROM produksi WHERE status = 'selesai'");
$total_unit_diproduksi = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM material_masuk");
$total_transaksi_material = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM materials WHERE stok <= stok_minimum");
$total_material_menipis = $stmt->fetch()['total'];

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Aggregate</h2>
</div>

<!-- Statistik Umum -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <h6 class="text-muted">Total Produksi Selesai</h6>
                <h2><?php echo $total_produksi_selesai; ?></h2>
                <small class="text-muted">transaksi</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card primary">
            <div class="card-body">
                <h6 class="text-muted">Total Unit Diproduksi</h6>
                <h2><?php echo $total_unit_diproduksi; ?></h2>
                <small class="text-muted">unit</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <h6 class="text-muted">Transaksi Material Masuk</h6>
                <h2><?php echo $total_transaksi_material; ?></h2>
                <small class="text-muted">transaksi</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <h6 class="text-muted">Material Menipis</h6>
                <h2><?php echo $total_material_menipis; ?></h2>
                <small class="text-muted">item</small>
            </div>
        </div>
    </div>
</div>

<!-- Laporan Detail -->
<div class="row">
    <!-- Material Terbanyak Digunakan -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Top 10 Material Paling Banyak Digunakan
            </div>
            <div class="card-body">
                <?php if (empty($material_terbanyak)): ?>
                <p class="text-muted text-center py-3">Belum ada data</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Material</th>
                                <th>Total Terpakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($material_terbanyak as $m): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
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
    
    <!-- Produk Terbanyak Diproduksi -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy"></i> Top 10 Produk Paling Banyak Diproduksi
            </div>
            <div class="card-body">
                <?php if (empty($produk_terbanyak)): ?>
                <p class="text-muted text-center py-3">Belum ada data</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Total Produksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($produk_terbanyak as $p): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($p['nama_produk']); ?></td>
                                <td><strong><?php echo $p['total_produksi']; ?></strong> unit</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- User Paling Aktif -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-people"></i> Top 10 User Paling Aktif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Total Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1; 
                            $badge_class = [
                                'administrator' => 'bg-danger',
                                'procurement' => 'bg-primary',
                                'gudang' => 'bg-success'
                            ];
                            foreach ($user_teraktif as $u): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($u['nama']); ?></td>
                                <td>
                                    <span class="badge <?php echo $badge_class[$u['role']]; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo $u['total_aktivitas']; ?></strong> aktivitas</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Material Menipis -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle"></i> Material dengan Stok Menipis
            </div>
            <div class="card-body">
                <?php if (empty($material_menipis)): ?>
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle"></i> Semua material memiliki stok yang cukup
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Material</th>
                                <th>Stok</th>
                                <th>Min</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($material_menipis as $m): 
                                $percentage = ($m['stok'] / $m['stok_minimum']) * 100;
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($m['nama_kategori']); ?></span></td>
                                <td><?php echo htmlspecialchars($m['nama_material']); ?></td>
                                <td><span class="badge bg-danger"><?php echo number_format($m['stok'], 2); ?></span></td>
                                <td><?php echo number_format($m['stok_minimum'], 2); ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                             style="width: <?php echo min($percentage, 100); ?>%">
                                            <?php echo number_format($percentage, 0); ?>%
                                        </div>
                                    </div>
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
</div>

<?php include '../../includes/footer.php'; ?>
