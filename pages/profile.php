<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

cekLogin();

$page_title = 'Profil Saya';

// Ambil data user
$user = getUserInfo($pdo, $_SESSION['user_id']);

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'update_profile') {
        $nama = $_POST['nama'];
        $username = $_POST['username'];
        
        if (empty($nama) || empty($username)) {
            $_SESSION['error'] = 'Nama dan username harus diisi';
        } else {
            // Cek username sudah ada (kecuali milik user ini)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Username sudah digunakan';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, username = ? WHERE id = ?");
                $stmt->execute([$nama, $username, $_SESSION['user_id']]);
                
                $_SESSION['nama'] = $nama;
                $_SESSION['username'] = $username;
                
                logAktivitas($pdo, $_SESSION['user_id'], 'update_profile', 'users', $_SESSION['user_id'], 'Mengupdate profil');
                
                $_SESSION['success'] = 'Profil berhasil diupdate';
                header('Location: profile.php');
                exit;
            }
        }
    }
    
    if ($_POST['action'] == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Semua field password harus diisi';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok';
        } elseif (strlen($new_password) < 6) {
            $_SESSION['error'] = 'Password baru minimal 6 karakter';
        } else {
            // Verifikasi password lama
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                logAktivitas($pdo, $_SESSION['user_id'], 'change_password', 'users', $_SESSION['user_id'], 'Mengubah password');
                
                $_SESSION['success'] = 'Password berhasil diubah';
                header('Location: profile.php');
                exit;
            } else {
                $_SESSION['error'] = 'Password lama tidak sesuai';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-person-circle"></i> Profil Saya</h2>
</div>

<div class="row">
    <!-- Informasi Profil -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle" style="font-size: 6rem; color: var(--primary-color);"></i>
                </div>
                <h4><?php echo htmlspecialchars($user['nama']); ?></h4>
                <p class="text-muted mb-2">@<?php echo htmlspecialchars($user['username']); ?></p>
                <?php
                $badge_class = [
                    'administrator' => 'bg-danger',
                    'procurement' => 'bg-primary',
                    'gudang' => 'bg-success'
                ];
                ?>
                <span class="badge <?php echo $badge_class[$user['role']]; ?> mb-3">
                    <?php echo ucfirst($user['role']); ?>
                </span>
                <hr>
                <div class="text-start">
                    <p class="mb-2"><i class="bi bi-calendar-check text-muted"></i> <small>Bergabung sejak</small></p>
                    <p class="mb-0"><strong><?php echo date('d F Y', strtotime($user['created_at'])); ?></strong></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Update Profil -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Edit Profil
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                        <small class="text-muted">Role tidak dapat diubah sendiri. Hubungi administrator.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Form Ubah Password -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-lock"></i> Ubah Password
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" name="new_password" minlength="6" required>
                        </div>
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
