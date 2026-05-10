<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$page_title = 'Edit Produk';
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Produk tidak ditemukan!';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);

    if (empty($nama_produk)) {
        $_SESSION['error'] = 'Nama produk tidak boleh kosong!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET nama_produk = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$nama_produk, $deskripsi, $id]);
            
            logAktivitas($pdo, $_SESSION['user_id'], 'edit_produk', 'products', $id, "Mengupdate produk: $nama_produk");
            
            $_SESSION['success'] = "Produk $nama_produk berhasil diupdate!";
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal mengupdate produk: ' . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-pencil-square"></i> Edit Produk</h2>
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
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Produk
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
