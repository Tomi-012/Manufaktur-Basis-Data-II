<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['gudang', 'administrator']);

$page_title = 'Detail Produksi';

$id = $_GET['id'] ?? 0;

// Admin bisa lihat semua, gudang hanya miliknya sendiri
if ($_SESSION['role'] === 'administrator') {
    $stmt = $pdo->prepare("
        SELECT p.*, pr.nama_produk, u.nama as nama_user
        FROM produksi p
        JOIN products pr ON p.product_id = pr.id
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, pr.nama_produk, u.nama as nama_user
        FROM produksi p
        JOIN products pr ON p.product_id = pr.id
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$id, $_SESSION['user_id']]);
}
$produksi = $stmt->fetch();

if (!$produksi) {
    $_SESSION['error'] = 'Data produksi tidak ditemukan';
    header('Location: index.php');
    exit;
}

// Ambil detail material yang digunakan
$stmt = $pdo->prepare("
    SELECT pd.*, m.nama_material, m.satuan
    FROM produksi_detail pd
    JOIN materials m ON pd.material_id = m.id
    WHERE pd.produksi_id = ?
");
$stmt->execute([$id]);
$detail_materials = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-eye"></i> Detail Produksi #<?php echo $id; ?></h2>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi Produksi
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">ID Produksi</th>
                        <td><?php echo $produksi['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Produk</th>
                        <td><strong><?php echo htmlspecialchars($produksi['nama_produk']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Jumlah Produksi</th>
                        <td><strong><?php echo $produksi['jumlah_produksi']; ?></strong> unit</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php
                            $badge_class = [
                                'proses' => 'bg-warning',
                                'selesai' => 'bg-success',
                                'gagal' => 'bg-danger'
                            ];
                            ?>
                            <span class="badge <?php echo $badge_class[$produksi['status']]; ?>">
                                <?php echo ucfirst($produksi['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Diproses Oleh</th>
                        <td><?php echo htmlspecialchars($produksi['nama_user']); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td><?php echo date('d F Y, H:i', strtotime($produksi['tanggal'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-seam"></i> Material yang Digunakan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail_materials as $dm): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dm['nama_material']); ?></td>
                                <td><strong><?php echo number_format($dm['jumlah_terpakai'], 2); ?></strong></td>
                                <td><?php echo htmlspecialchars($dm['satuan']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<a href="index.php" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Kembali
</a>

<?php include '../../includes/footer.php'; ?>
