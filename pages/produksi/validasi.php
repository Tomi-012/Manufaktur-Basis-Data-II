<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$page_title = 'Validasi Produksi';

// Proses validasi (approve / reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produksi_id = $_POST['produksi_id'] ?? 0;
    $action      = $_POST['action'] ?? '';

    if (!in_array($action, ['approve', 'reject'])) {
        $_SESSION['error'] = 'Aksi tidak valid';
        header('Location: validasi.php');
        exit;
    }

    try {
        // Ambil data produksi
        $stmt = $pdo->prepare("
            SELECT p.*, pr.nama_produk 
            FROM produksi p 
            JOIN products pr ON p.product_id = pr.id 
            WHERE p.id = ? AND p.status = 'proses'
        ");
        $stmt->execute([$produksi_id]);
        $produksi = $stmt->fetch();

        if (!$produksi) {
            $_SESSION['error'] = 'Data produksi tidak ditemukan atau sudah divalidasi';
            header('Location: validasi.php');
            exit;
        }

        $pdo->beginTransaction();

        if ($action === 'approve') {
            // Update status menjadi selesai
            $stmt = $pdo->prepare("UPDATE produksi SET status = 'selesai' WHERE id = ?");
            $stmt->execute([$produksi_id]);

            // Tambah stok produk (baru ditambah setelah divalidasi)
            $stmt = $pdo->prepare("UPDATE products SET stok = stok + ? WHERE id = ?");
            $stmt->execute([$produksi['jumlah_produksi'], $produksi['product_id']]);

            // Log aktivitas
            logAktivitas($pdo, $_SESSION['user_id'], 'validasi_produksi', 'produksi', $produksi_id,
                "Menyetujui produksi {$produksi['jumlah_produksi']} unit {$produksi['nama_produk']}");

            $pdo->commit();
            $_SESSION['success'] = "Produksi #{$produksi_id} ({$produksi['jumlah_produksi']} unit {$produksi['nama_produk']}) berhasil divalidasi!";

        } else { // reject
            // Update status menjadi gagal
            $stmt = $pdo->prepare("UPDATE produksi SET status = 'gagal' WHERE id = ?");
            $stmt->execute([$produksi_id]);

            // Kembalikan stok material yang sudah dikurangi
            $stmt = $pdo->prepare("
                SELECT material_id, jumlah_terpakai 
                FROM produksi_detail 
                WHERE produksi_id = ?
            ");
            $stmt->execute([$produksi_id]);
            $details = $stmt->fetchAll();

            foreach ($details as $detail) {
                $stmt = $pdo->prepare("UPDATE materials SET stok = stok + ? WHERE id = ?");
                $stmt->execute([$detail['jumlah_terpakai'], $detail['material_id']]);
            }

            // Log aktivitas
            logAktivitas($pdo, $_SESSION['user_id'], 'tolak_produksi', 'produksi', $produksi_id,
                "Menolak produksi {$produksi['jumlah_produksi']} unit {$produksi['nama_produk']} - stok material dikembalikan");

            $pdo->commit();
            $_SESSION['success'] = "Produksi #{$produksi_id} ditolak. Stok material telah dikembalikan.";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Gagal memproses validasi: ' . $e->getMessage();
    }

    header('Location: validasi.php');
    exit;
}

// Filter status
$filter = $_GET['filter'] ?? 'proses';
$allowed_filters = ['proses', 'selesai', 'gagal', 'semua'];
if (!in_array($filter, $allowed_filters)) $filter = 'proses';

// Query produksi
$where = ($filter !== 'semua') ? "WHERE p.status = '$filter'" : "";
$stmt = $pdo->query("
    SELECT p.*, pr.nama_produk, u.nama as nama_user
    FROM produksi p
    JOIN products pr ON p.product_id = pr.id
    JOIN users u ON p.user_id = u.id
    $where
    ORDER BY p.tanggal DESC
");
$daftar_produksi = $stmt->fetchAll();

// Hitung per status
$stmt = $pdo->query("SELECT status, COUNT(*) as total FROM produksi GROUP BY status");
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['total'];
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-clipboard-check"></i> Validasi Produksi</h2>
</div>

<!-- Status Filter Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-2">
        <a href="?filter=proses" class="text-decoration-none">
            <div class="card <?php echo $filter == 'proses' ? 'border-warning border-2' : ''; ?>">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Menunggu Validasi</small>
                            <h4 class="mb-0 text-warning"><?php echo $status_counts['proses'] ?? 0; ?></h4>
                        </div>
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-2">
        <a href="?filter=selesai" class="text-decoration-none">
            <div class="card <?php echo $filter == 'selesai' ? 'border-success border-2' : ''; ?>">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Disetujui</small>
                            <h4 class="mb-0 text-success"><?php echo $status_counts['selesai'] ?? 0; ?></h4>
                        </div>
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-2">
        <a href="?filter=gagal" class="text-decoration-none">
            <div class="card <?php echo $filter == 'gagal' ? 'border-danger border-2' : ''; ?>">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Ditolak</small>
                            <h4 class="mb-0 text-danger"><?php echo $status_counts['gagal'] ?? 0; ?></h4>
                        </div>
                        <i class="bi bi-x-circle text-danger" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-2">
        <a href="?filter=semua" class="text-decoration-none">
            <div class="card <?php echo $filter == 'semua' ? 'border-primary border-2' : ''; ?>">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Semua</small>
                            <h4 class="mb-0 text-primary"><?php echo array_sum($status_counts); ?></h4>
                        </div>
                        <i class="bi bi-list-ul text-primary" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Tabel Produksi -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-table"></i> 
            Daftar Produksi — 
            <strong>
                <?php 
                $filter_labels = [
                    'proses' => 'Menunggu Validasi',
                    'selesai' => 'Disetujui',
                    'gagal' => 'Ditolak',
                    'semua' => 'Semua Status'
                ];
                echo $filter_labels[$filter]; 
                ?>
            </strong>
        </span>
    </div>
    <div class="card-body">
        <?php if (empty($daftar_produksi)): ?>
        <p class="text-muted text-center py-4">
            <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
            Tidak ada data produksi
        </p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Diajukan Oleh</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_produksi as $prod): ?>
                    <tr>
                        <td><strong>#<?php echo $prod['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($prod['nama_produk']); ?></td>
                        <td><strong><?php echo $prod['jumlah_produksi']; ?></strong> unit</td>
                        <td>
                            <i class="bi bi-person-circle"></i> 
                            <?php echo htmlspecialchars($prod['nama_user']); ?>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($prod['tanggal'])); ?></small>
                        </td>
                        <td>
                            <?php
                            $badge_class = [
                                'proses' => 'bg-warning text-dark',
                                'selesai' => 'bg-success',
                                'gagal' => 'bg-danger'
                            ];
                            $badge_icon = [
                                'proses' => 'bi-hourglass-split',
                                'selesai' => 'bi-check-circle',
                                'gagal' => 'bi-x-circle'
                            ];
                            ?>
                            <span class="badge <?php echo $badge_class[$prod['status']]; ?>">
                                <i class="bi <?php echo $badge_icon[$prod['status']]; ?>"></i>
                                <?php echo ucfirst($prod['status']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($prod['status'] === 'proses'): ?>
                            <div class="btn-group btn-group-sm">
                                <!-- Tombol Setujui -->
                                <form method="POST" action="" class="d-inline" 
                                      onsubmit="return confirm('Setujui produksi #<?php echo $prod['id']; ?>?\nStok <?php echo htmlspecialchars($prod['nama_produk']); ?> akan bertambah <?php echo $prod['jumlah_produksi']; ?> unit.')">
                                    <input type="hidden" name="produksi_id" value="<?php echo $prod['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm" title="Setujui">
                                        <i class="bi bi-check-lg"></i> Setujui
                                    </button>
                                </form>
                                
                                <!-- Tombol Tolak -->
                                <form method="POST" action="" class="d-inline"
                                      onsubmit="return confirm('Tolak produksi #<?php echo $prod['id']; ?>?\nStok material yang sudah terpakai akan dikembalikan.')">
                                    <input type="hidden" name="produksi_id" value="<?php echo $prod['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Tolak">
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </button>
                                </form>
                            </div>
                            <?php else: ?>
                            <span class="text-muted"><small>—</small></span>
                            <?php endif; ?>
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
