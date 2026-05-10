<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$page_title = 'Tambah Material Baru';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori_id = $_POST['kategori_id'];
    $nama_material = $_POST['nama_material'];
    $stok_minimum = $_POST['stok_minimum'];
    $satuan = $_POST['satuan'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO materials (kategori_id, nama_material, stok, stok_minimum, satuan)
            VALUES (?, ?, 0, ?, ?)
        ");
        $stmt->execute([$kategori_id, $nama_material, $stok_minimum, $satuan]);
        
        $material_id = $pdo->lastInsertId();
        
        logAktivitas($pdo, $_SESSION['user_id'], 'tambah_material', 'materials', $material_id, "Menambahkan material baru: $nama_material");
        
        $_SESSION['success'] = "Material $nama_material berhasil ditambahkan!";
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Gagal menambahkan material: ' . $e->getMessage();
    }
}

// Ambil kategori
$stmt = $pdo->query("SELECT * FROM kategori_material ORDER BY nama_kategori ASC");
$kategori = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-plus-circle"></i> Tambah Material Baru</h2>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori_id" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori as $k): ?>
                                <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kategori']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Material</label>
                        <input type="text" name="nama_material" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Contoh: Meter, Roll, Pcs" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Minimum</label>
                        <input type="number" step="0.01" name="stok_minimum" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Material
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
