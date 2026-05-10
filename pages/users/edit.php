<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$page_title = 'Edit User';

$id = $_GET['id'] ?? 0;

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'User tidak ditemukan';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($nama) || empty($username) || empty($role)) {
        $_SESSION['error'] = 'Nama, username, dan role harus diisi';
    } else {
        // Cek username sudah ada (kecuali milik user ini)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username sudah digunakan';
        } else {
            // Update user
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, username = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$nama, $username, $hashed_password, $role, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, username = ?, role = ? WHERE id = ?");
                $stmt->execute([$nama, $username, $role, $id]);
            }
            
            logAktivitas($pdo, $_SESSION['user_id'], 'update_user', 'users', $id, "Mengupdate user: $nama");
            
            $_SESSION['success'] = 'User berhasil diupdate';
            header('Location: index.php');
            exit;
        }
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-pencil"></i> Edit User</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="administrator" <?php echo $user['role'] == 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                            <option value="procurement" <?php echo $user['role'] == 'procurement' ? 'selected' : ''; ?>>Procurement</option>
                            <option value="gudang" <?php echo $user['role'] == 'gudang' ? 'selected' : ''; ?>>Gudang</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
