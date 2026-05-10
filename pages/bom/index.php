<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$page_title = 'Bill of Materials';

$product_id = $_GET['product_id'] ?? null;

// Ambil semua products
$stmt = $pdo->query("SELECT * FROM products ORDER BY nama_produk ASC");
$products = $stmt->fetchAll();

// Jika ada product yang dipilih, ambil BOM-nya
$bom_items = [];
$selected_product = null;
if ($product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $selected_product = $stmt->fetch();
    
    if ($selected_product) {
        $stmt = $pdo->prepare("
            SELECT bom.*, m.nama_material, m.satuan, k.nama_kategori
            FROM bill_of_materials bom
            JOIN materials m ON bom.material_id = m.id
            JOIN kategori_material k ON m.kategori_id = k.id
            WHERE bom.product_id = ?
            ORDER BY m.nama_material ASC
        ");
        $stmt->execute([$product_id]);
        $bom_items = $stmt->fetchAll();
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-list-check"></i> Bill of Materials (BOM)</h2>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bag"></i> Pilih Produk
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($products as $p): ?>
                    <a href="?product_id=<?php echo $p['id']; ?>" 
                       class="list-group-item list-group-item-action <?php echo ($product_id == $p['id']) ? 'active' : ''; ?>">
                        <strong><?php echo htmlspecialchars($p['nama_produk']); ?></strong>
                        <br><small><?php echo htmlspecialchars($p['deskripsi']); ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php if ($selected_product): ?>
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-check"></i> BOM: <?php echo htmlspecialchars($selected_product['nama_produk']); ?>
            </div>
            <div class="card-body">
                <?php if (empty($bom_items)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada BOM untuk produk ini
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Material</th>
                                <th>Jumlah Dibutuhkan</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bom_items as $item): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['nama_kategori']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['nama_material']); ?></td>
                                <td><strong><?php echo number_format($item['jumlah_dibutuhkan'], 2); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['satuan']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Catatan:</strong> Jumlah di atas adalah kebutuhan untuk membuat <strong>1 unit</strong> produk.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-arrow-left" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="text-muted mt-3">Pilih produk di sebelah kiri untuk melihat BOM</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
