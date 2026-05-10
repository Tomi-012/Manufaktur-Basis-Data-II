<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['gudang']);

$page_title = 'Produksi';

// Ambil riwayat produksi
$stmt = $pdo->prepare("
    SELECT p.*, pr.nama_produk, u.nama as nama_user
    FROM produksi p
    JOIN products pr ON p.product_id = pr.id
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY p.tanggal DESC
");
$stmt->execute([$_SESSION['user_id']]);
$riwayat_produksi = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-gear"></i> Produksi</h2>
    <a href="proses.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Proses Produksi Baru
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> Riwayat Produksi Saya
    </div>
    <div class="card-body">
        <?php if (empty($riwayat_produksi)): ?>
        <p class="text-muted text-center py-3">Belum ada riwayat produksi</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat_produksi as $prod): ?>
                    <tr>
                        <td><?php echo $prod['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($prod['nama_produk']); ?></strong></td>
                        <td><?php echo $prod['jumlah_produksi']; ?> unit</td>
                        <td>
                            <?php
                            $badge_class = [
                                'proses' => 'bg-warning',
                                'selesai' => 'bg-success',
                                'gagal' => 'bg-danger'
                            ];
                            ?>
                            <span class="badge <?php echo $badge_class[$prod['status']]; ?>">
                                <?php echo ucfirst($prod['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($prod['tanggal'])); ?></td>
                        <td>
                            <a href="detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Detail
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

<?php include '../../includes/footer.php'; ?>
