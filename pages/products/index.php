<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();

$page_title = 'Products';

// Ambil semua products
$stmt = $pdo->query("SELECT * FROM products ORDER BY nama_produk ASC");
$products = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-bag"></i> Products</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Deskripsi</th>
                        <th>Stok</th>
                        <th>Dibuat</th>
                        <?php if ($_SESSION['role'] == 'administrator'): ?>
                        <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($p['nama_produk']); ?></strong></td>
                        <td><?php echo htmlspecialchars($p['deskripsi']); ?></td>
                        <td>
                            <span class="badge <?php echo $p['stok'] < 10 ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo $p['stok']; ?> unit
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($p['created_at'])); ?></td>
                        <?php if ($_SESSION['role'] == 'administrator'): ?>
                        <td>
                            <a href="/Manufaktur/pages/bom/index.php?product_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-list-check"></i> BOM
                            </a>
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
