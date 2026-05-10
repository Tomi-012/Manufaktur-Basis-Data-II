<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$product_id = $_GET['product_id'] ?? 0;

// Validasi produk
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Produk tidak ditemukan!';
    header('Location: index.php');
    exit;
}

$page_title = 'Tambah Material ke BOM';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $material_id = $_POST['material_id'];
    $jumlah = $_POST['jumlah_dibutuhkan'];

    // Cek apakah material sudah ada di BOM produk ini
    $stmt = $pdo->prepare("SELECT id FROM bill_of_materials WHERE product_id = ? AND material_id = ?");
    $stmt->execute([$product_id, $material_id]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Material tersebut sudah ada di BOM produk ini!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bill_of_materials (product_id, material_id, jumlah_dibutuhkan) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $material_id, $jumlah]);
            
            $_SESSION['success'] = "Material berhasil ditambahkan ke BOM!";
            header("Location: index.php?product_id=$product_id");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal menambahkan material: ' . $e->getMessage();
        }
    }
}

// Ambil semua material untuk opsi pilihan
$stmt = $pdo->query("SELECT m.*, k.nama_kategori FROM materials m JOIN kategori_material k ON m.kategori_id = k.id ORDER BY m.nama_material ASC");
$materials = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-plus-circle"></i> Tambah Material ke BOM</h2>
    <a href="index.php?product_id=<?php echo $product_id; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bag"></i> Produk: <strong><?php echo htmlspecialchars($product['nama_produk']); ?></strong>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Material</label>
                        <select name="material_id" class="form-select" required>
                            <option value="">Pilih Material</option>
                            <?php foreach ($materials as $m): ?>
                                <option value="<?php echo $m['id']; ?>">
                                    [<?php echo htmlspecialchars($m['nama_kategori']); ?>] <?php echo htmlspecialchars($m['nama_material']); ?> (Satuan: <?php echo htmlspecialchars($m['satuan']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Dibutuhkan per 1 Unit Produk</label>
                        <input type="number" step="0.01" name="jumlah_dibutuhkan" class="form-control" required min="0.01">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan ke BOM
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
