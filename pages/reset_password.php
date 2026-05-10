<?php
session_start();
require_once '../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$token = $_GET['token'] ?? '';
$valid_token = false;
$user = null;

if (!empty($token)) {
    // Verifikasi token
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE reset_token = ? 
        AND reset_token_expire > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $valid_token = true;
    } else {
        $error = 'Link reset password tidak valid atau sudah kadaluarsa';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password dan konfirmasi tidak cocok';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_token_expire = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$hashed_password, $user['id']]);
        
        // Log aktivitas
        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aksi, detail) VALUES (?, 'reset_password', 'Password direset melalui forgot password')");
        $stmt->execute([$user['id']]);
        
        $_SESSION['success'] = 'Password berhasil direset. Silakan login dengan password baru Anda.';
        header('Location: login.php');
        exit;
    }
}

$page_title = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Manufaktur Tas</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Manufaktur/assets/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .reset-container {
            max-width: 500px;
            width: 100%;
            padding: 20px;
        }
        
        .reset-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .reset-header {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .reset-header i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        
        .reset-body {
            padding: 40px 30px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <i class="bi bi-shield-lock"></i>
                <h2>Reset Password</h2>
                <p class="mb-0">Masukkan password baru Anda</p>
            </div>
            
            <div class="reset-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($valid_token): ?>
                <div class="alert alert-info mb-4">
                    <i class="bi bi-person-circle"></i> 
                    Reset password untuk: <strong><?php echo htmlspecialchars($user['nama']); ?></strong>
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" name="new_password" minlength="6" placeholder="Minimal 6 karakter" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" name="confirm_password" minlength="6" placeholder="Ulangi password baru" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 mb-3">
                        <i class="bi bi-shield-check"></i> Reset Password
                    </button>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Kembali ke Login
                        </a>
                    </div>
                </form>
                <?php else: ?>
                <div class="text-center">
                    <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Link Tidak Valid</h4>
                    <p class="text-muted">Link reset password tidak valid atau sudah kadaluarsa.</p>
                    <a href="forgot_password.php" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Minta Link Baru
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
