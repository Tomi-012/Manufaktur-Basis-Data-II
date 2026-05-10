<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['procurement']);

$page_title = 'Tambah Stok Material';

$material_id = $_GET['id'] ?? null;

// Ambil semua materials
$stmt = $pdo->query("SELECT * FROM materials ORDER BY nama_material ASC");
$materials = $stmt->fetchAll();

// Jika ada ID material yang dipilih
$selected_material = null;
if ($material_id) {
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
    $stmt->execute([$material_id]);
    $selected_material = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $material_id = $_POST['material_id'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];
    
    if (empty($material_id) || empty($jumlah) || $jumlah <= 0) {
        $_SESSION['error'] = 'Material dan jumlah harus diisi dengan benar';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Insert ke material_masuk
            $stmt = $pdo->prepare("
                INSERT INTO material_masuk (material_id, user_id, jumlah, keterangan)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$material_id, $_SESSION['user_id'], $jumlah, $keterangan]);
            
            // Update stok material
            $stmt = $pdo->prepare("UPDATE materials SET stok = stok + ? WHERE id = ?");
            $stmt->execute([$jumlah, $material_id]);
            
            // Log aktivitas
            $stmt = $pdo->prepare("SELECT nama_material FROM materials WHERE id = ?");
            $stmt->execute([$material_id]);
            $material_name = $stmt->fetchColumn();
            
            logAktivitas($pdo, $_SESSION['user_id'], 'material_masuk', 'material_masuk', $pdo->lastInsertId(), 
                "Menambah stok $material_name sebanyak $jumlah");
            
            $pdo->commit();
            
            $_SESSION['success'] = 'Stok material berhasil ditambahkan';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Gagal menambah stok: ' . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-plus-circle"></i> Tambah Stok Material</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Material</label>
                        <select class="form-select" name="material_id" id="material_id" required>
                            <option value="">Pilih Material</option>
                            <?php foreach ($materials as $m): ?>
                            <option value="<?php echo $m['id']; ?>" 
                                    <?php echo ($selected_material && $selected_material['id'] == $m['id']) ? 'selected' : ''; ?>
                                    data-stok="<?php echo $m['stok']; ?>"
                                    data-satuan="<?php echo $m['satuan']; ?>">
                                <?php echo htmlspecialchars($m['nama_material']); ?> 
                                (Stok: <?php echo number_format($m['stok'], 2); ?> <?php echo $m['satuan']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" step="0.01" class="form-control" name="jumlah" required>
                        <small class="text-muted">Satuan: <span id="satuan-info">-</span></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keterangan / Supplier</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Contoh: Pembelian dari PT. ABC"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <p><strong>Stok Saat Ini:</strong> <span id="stok-info">-</span></p>
                <p class="mb-0"><small class="text-muted">Pilih material untuk melihat stok saat ini</small></p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('material_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const stok = selected.getAttribute('data-stok');
    const satuan = selected.getAttribute('data-satuan');
    
    if (stok && satuan) {
        document.getElementById('stok-info').textContent = parseFloat(stok).toFixed(2) + ' ' + satuan;
        document.getElementById('satuan-info').textContent = satuan;
    } else {
        document.getElementById('stok-info').textContent = '-';
        document.getElementById('satuan-info').textContent = '-';
    }
});

// Trigger change event jika ada material yang sudah dipilih
if (document.getElementById('material_id').value) {
    document.getElementById('material_id').dispatchEvent(new Event('change'));
}
</script>

<?php include '../../includes/footer.php'; ?>
