<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$page_title = 'Tambah Produk Baru';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);

    if (empty($nama_produk)) {
        $_SESSION['error'] = 'Nama produk tidak boleh kosong!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (nama_produk, deskripsi, stok) VALUES (?, ?, 0)");
            $stmt->execute([$nama_produk, $deskripsi]);
            
            $product_id = $pdo->lastInsertId();
            logAktivitas($pdo, $_SESSION['user_id'], 'tambah_produk', 'products', $product_id, "Menambahkan produk baru: $nama_produk");
            
            $_SESSION['success'] = "Produk $nama_produk berhasil ditambahkan!";
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal menambahkan produk: ' . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-plus-circle"></i> Tambah Produk Baru</h2>
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
                        <input type="text" name="nama_produk" class="form-control" required placeholder="Masukkan nama produk">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Masukkan deskripsi produk"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Stok awal produk akan di-set ke 0. Stok hanya akan bertambah melalui proses produksi yang divalidasi.
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Produk
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
