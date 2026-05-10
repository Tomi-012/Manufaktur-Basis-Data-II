<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();

$page_title = 'Materials';

// Ambil semua materials dengan kategori
$stmt = $pdo->query("
    SELECT m.*, k.nama_kategori
    FROM materials m
    JOIN kategori_material k ON m.kategori_id = k.id
    ORDER BY m.nama_material ASC
");
$materials = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-box-seam"></i> Materials</h2>
    <div class="d-flex gap-2">
        <?php if ($_SESSION['role'] == 'procurement'): ?>
        <a href="tambah_stok.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Stok Material
        </a>
        <?php endif; ?>
        <?php if ($_SESSION['role'] == 'administrator'): ?>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Material Baru
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori</th>
                        <th>Nama Material</th>
                        <th>Stok</th>
                        <th>Stok Minimum</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <?php if ($_SESSION['role'] == 'procurement' || $_SESSION['role'] == 'administrator'): ?>
                        <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $m): ?>
                    <tr>
                        <td><?php echo $m['id']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($m['nama_kategori']); ?></span></td>
                        <td><?php echo htmlspecialchars($m['nama_material']); ?></td>
                        <td><strong><?php echo number_format($m['stok'], 2); ?></strong></td>
                        <td><?php echo number_format($m['stok_minimum'], 2); ?></td>
                        <td><?php echo htmlspecialchars($m['satuan']); ?></td>
                        <td>
                            <?php if ($m['stok'] <= $m['stok_minimum']): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-exclamation-triangle"></i> Menipis
                            </span>
                            <?php else: ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Aman
                            </span>
                            <?php endif; ?>
                        </td>
                        <?php if ($_SESSION['role'] == 'procurement' || $_SESSION['role'] == 'administrator'): ?>
                        <td>
                            <?php if ($_SESSION['role'] == 'procurement'): ?>
                            <a href="tambah_stok.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Stok
                            </a>
                            <?php endif; ?>
                            <?php if ($_SESSION['role'] == 'administrator'): ?>
                            <a href="edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
