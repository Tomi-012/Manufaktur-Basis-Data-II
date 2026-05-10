<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['gudang']);

$page_title = 'Proses Produksi';

$product_id = $_GET['product_id'] ?? null;

// Ambil semua products
$stmt = $pdo->query("SELECT * FROM products ORDER BY nama_produk ASC");
$products = $stmt->fetchAll();

// Jika ada product yang dipilih, ambil BOM dan cek stok
$selected_product = null;
$bom_items = [];
$can_produce = true;
$max_producible = 0;

if ($product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $selected_product = $stmt->fetch();
    
    if ($selected_product) {
        // Ambil BOM
        $stmt = $pdo->prepare("
            SELECT bom.*, m.nama_material, m.satuan, m.stok
            FROM bill_of_materials bom
            JOIN materials m ON bom.material_id = m.id
            WHERE bom.product_id = ?
        ");
        $stmt->execute([$product_id]);
        $bom_items = $stmt->fetchAll();
        
        // Hitung maksimal yang bisa diproduksi
        $max_producible = PHP_INT_MAX;
        foreach ($bom_items as $item) {
            $possible = floor($item['stok'] / $item['jumlah_dibutuhkan']);
            if ($possible < $max_producible) {
                $max_producible = $possible;
            }
            if ($item['stok'] < $item['jumlah_dibutuhkan']) {
                $can_produce = false;
            }
        }
        
        if ($max_producible < 0) $max_producible = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $jumlah_produksi = $_POST['jumlah_produksi'];
    
    if (empty($product_id) || empty($jumlah_produksi) || $jumlah_produksi <= 0) {
        $_SESSION['error'] = 'Produk dan jumlah harus diisi dengan benar';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Ambil BOM
            $stmt = $pdo->prepare("
                SELECT bom.*, m.stok
                FROM bill_of_materials bom
                JOIN materials m ON bom.material_id = m.id
                WHERE bom.product_id = ?
            ");
            $stmt->execute([$product_id]);
            $bom = $stmt->fetchAll();
            
            // Cek stok mencukupi
            $stok_cukup = true;
            foreach ($bom as $item) {
                $total_dibutuhkan = $item['jumlah_dibutuhkan'] * $jumlah_produksi;
                if ($item['stok'] < $total_dibutuhkan) {
                    $stok_cukup = false;
                    break;
                }
            }
            
            if (!$stok_cukup) {
                throw new Exception('Stok material tidak mencukupi');
            }
            
            // Insert produksi dengan status 'proses' (menunggu validasi admin)
            $stmt = $pdo->prepare("
                INSERT INTO produksi (product_id, user_id, jumlah_produksi, status)
                VALUES (?, ?, ?, 'proses')
            ");
            $stmt->execute([$product_id, $_SESSION['user_id'], $jumlah_produksi]);
            $produksi_id = $pdo->lastInsertId();
            
            // Kurangi stok material dan insert produksi_detail
            foreach ($bom as $item) {
                $total_terpakai = $item['jumlah_dibutuhkan'] * $jumlah_produksi;
                
                // Insert detail
                $stmt = $pdo->prepare("
                    INSERT INTO produksi_detail (produksi_id, material_id, jumlah_terpakai)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$produksi_id, $item['material_id'], $total_terpakai]);
                
                // Kurangi stok material
                $stmt = $pdo->prepare("UPDATE materials SET stok = stok - ? WHERE id = ?");
                $stmt->execute([$total_terpakai, $item['material_id']]);
            }
            
            // NOTE: Stok produk BELUM ditambah, menunggu validasi admin
            
            // Log aktivitas
            $stmt = $pdo->prepare("SELECT nama_produk FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product_name = $stmt->fetchColumn();
            
            logAktivitas($pdo, $_SESSION['user_id'], 'produksi', 'produksi', $produksi_id, 
                "Mengajukan produksi $jumlah_produksi unit $product_name (menunggu validasi)");
            
            $pdo->commit();
            
            $_SESSION['success'] = "Produksi $jumlah_produksi unit $product_name berhasil diajukan! Menunggu validasi admin.";
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Gagal memproses produksi: ' . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-gear"></i> Proses Produksi</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Pilih Produk</label>
                        <select class="form-select" name="product_id" id="product_id" required onchange="window.location.href='?product_id='+this.value">
                            <option value="">Pilih Produk</option>
                            <?php foreach ($products as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($product_id == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama_produk']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($selected_product): ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Jumlah Produksi</label>
                        <input type="number" class="form-control" name="jumlah_produksi" min="1" max="<?php echo $max_producible; ?>" required>
                        <small class="text-muted">Maksimal yang bisa diproduksi: <strong><?php echo $max_producible; ?> unit</strong></small>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle"></i> Kebutuhan Material per Unit:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($bom_items as $item): ?>
                            <li>
                                <?php echo htmlspecialchars($item['nama_material']); ?>: 
                                <strong><?php echo number_format($item['jumlah_dibutuhkan'], 2); ?></strong> <?php echo $item['satuan']; ?>
                                <span class="badge <?php echo ($item['stok'] >= $item['jumlah_dibutuhkan']) ? 'bg-success' : 'bg-danger'; ?>">
                                    Stok: <?php echo number_format($item['stok'], 2); ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <?php if (!$can_produce): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Stok material tidak mencukupi!</strong> Hubungi procurement untuk menambah stok.
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" <?php echo !$can_produce ? 'disabled' : ''; ?>>
                            <i class="bi bi-gear"></i> Proses Produksi
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                    
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightbulb"></i> Panduan
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li>Pilih produk yang ingin diproduksi</li>
                    <li>Sistem akan mengecek ketersediaan material</li>
                    <li>Masukkan jumlah yang ingin diproduksi</li>
                    <li>Klik "Proses Produksi"</li>
                    <li>Tunggu <strong>validasi admin</strong></li>
                </ol>
                <hr>
                <p class="mb-0"><small class="text-muted">Stok material akan langsung berkurang saat pengajuan. Stok produk baru bertambah setelah <strong>divalidasi oleh admin</strong>.</small></p>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
