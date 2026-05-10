<?php
session_start();
require_once '../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $error = 'Username dan password harus diisi';
  } else {
    try {
      $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
      $stmt->execute([$username]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aksi, detail) VALUES (?, 'login', 'User login ke sistem')");
        $stmt->execute([$user['id']]);

        header('Location: dashboard.php');
        exit;
      } else {
        $error = 'Username atau password salah';
      }
    } catch (PDOException $e) {
      $error = 'Terjadi kesalahan sistem';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Manufaktur Tas</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f8f9fa;
      padding: 20px;
      overflow: hidden;
    }

    /* Flickering Grid Canvas */
    #flickering-grid {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
    }

    /* Gradient Overlay */
    .gradient-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(ellipse at center, transparent 0%, rgba(248, 249, 250, 0.5) 100%);
      z-index: 1;
      pointer-events: none;
    }

    .login-card {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(20px);
      width: 100%;
      max-width: 420px;
      border-radius: 24px;
      padding: 44px 40px 36px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.10);
      position: relative;
      z-index: 10;
      border: 1px solid rgba(0, 0, 0, 0.06);
      animation: slideUp 0.5s cubic-bezier(.22, 1, .36, 1) both;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(28px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Header */
    .logo-area {
      text-align: center;
      margin-bottom: 32px;
    }

    .logo-icon {
      width: 72px;
      height: 72px;
      background: linear-gradient(135deg, #0ea5e9, #0284c7);
      border-radius: 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 8px 24px rgba(14, 165, 233, 0.35);
      animation: pulse-glow 3s ease-in-out infinite;
    }

    @keyframes pulse-glow {

      0%,
      100% {
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.35);
      }

      50% {
        box-shadow: 0 8px 32px rgba(14, 165, 233, 0.55);
      }
    }

    .logo-icon i {
      font-size: 32px;
      color: #fff;
    }

    .logo-area h2 {
      font-size: 20px;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 4px;
    }

    .logo-area p {
      font-size: 13px;
      color: #6b7280;
    }

    .badge-role {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #0ea5e9, #0284c7);
      color: white;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 11px;
      margin-top: 10px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    /* Error */
    .error-msg {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Success */
    .success-msg {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #16a34a;
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Inputs */
    .input-group {
      margin-bottom: 18px;
    }

    .input-group label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: #374151;
      margin-bottom: 8px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-wrapper i.icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: 14px;
      pointer-events: none;
    }

    .input-wrapper .toggle-pw {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: 14px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 4px;
      transition: color 0.2s;
    }

    .input-wrapper .toggle-pw:hover {
      color: #0ea5e9;
    }

    .input-group input {
      width: 100%;
      padding: 13px 42px 13px 46px;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.25s;
      background: #f9fafb;
      color: #111;
    }

    .input-group input:focus {
      outline: none;
      border-color: #0ea5e9;
      background: white;
      box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
    }

    /* Forgot link */
    .forgot-row {
      display: flex;
      justify-content: flex-end;
      margin-top: -8px;
      margin-bottom: 20px;
    }

    .forgot-row a {
      font-size: 12px;
      color: #0ea5e9;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s;
    }

    .forgot-row a:hover {
      color: #0284c7;
      text-decoration: underline;
    }

    /* Button */
    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #0ea5e9, #0284c7);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      transition: all 0.25s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      letter-spacing: 0.3px;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(14, 165, 233, 0.40);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    /* Demo info */
    .demo-info {
      margin-top: 20px;
      padding: 14px 16px;
      background: #f8fafc;
      border-radius: 12px;
      border: 1px dashed #e2e8f0;
      font-size: 12px;
      color: #64748b;
    }

    .demo-info strong {
      color: #1e293b;
      display: block;
      margin-bottom: 4px;
    }

    .demo-info span {
      display: block;
      line-height: 1.8;
    }

    /* Footer */
    .footer-link {
      text-align: center;
      margin-top: 22px;
      padding-top: 18px;
      border-top: 1px solid #e5e7eb;
      font-size: 13px;
      color: #9ca3af;
    }

    .footer-link a {
      color: #0ea5e9;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s;
    }

    .footer-link a:hover {
      color: #0284c7;
    }
  </style>
</head>

<body>
  <!-- Flickering Grid Background -->
  <canvas id="flickering-grid"></canvas>
  <div class="gradient-overlay"></div>

  <div class="login-card">
    <div class="logo-area">
      <h2>Manufaktur Tas</h2>
      <p>Sistem Informasi Manajemen Produksi</p>
      <span class="badge-role"><i class="fas fa-circle" style="font-size:6px"></i> PORTAL LOGIN</span>
    </div>

    <?php if (isset($error)): ?>
      <div class="error-msg">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="success-msg">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($_SESSION['success']);
        unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off">
      <div class="input-group">
        <label for="username">Username</label>
        <div class="input-wrapper">
          <i class="fas fa-user icon"></i>
          <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
        </div>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <i class="fas fa-lock icon"></i>
          <input type="password" id="password" name="password" placeholder="Masukkan password" required>
          <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" tabindex="-1">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="forgot-row">
        <a href="forgot_password.php"><i class="fas fa-key" style="font-size:11px"></i> Reset Password</a>
      </div>

      <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt"></i> MASUK
      </button>
    </form>

    <div class="demo-info">
      <strong><i class="fas fa-info-circle"></i> Akun Demo:</strong>
      <span><b>Admin:</b> admin / password</span>
      <span><b>Procurement:</b> procurement / password</span>
      <span><b>Gudang:</b> gudang / password</span>
    </div>
  </div>

  <script>
    function togglePassword(fieldId, btn) {
      const input = document.getElementById(fieldId);
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }

    // Flickering Grid Animation
    (function () {
      const canvas = document.getElementById('flickering-grid');
      const ctx = canvas.getContext('2d');
      let width, height, cols, rows;
      const squareSize = 4;
      const gap = 4;
      const cellSize = squareSize + gap;
      let opacities = [];

      function init() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        cols = Math.floor(width / cellSize) + 1;
        rows = Math.floor(height / cellSize) + 1;
        opacities = [];
        for (let i = 0; i < cols * rows; i++) {
          opacities.push(Math.random() * 0.3);
        }
      }

      function draw() {
        ctx.clearRect(0, 0, width, height);
        for (let i = 0; i < cols; i++) {
          for (let j = 0; j < rows; j++) {
            const opacity = opacities[i + j * cols];
            ctx.fillStyle = 'rgba(0,0,0,' + opacity + ')';
            ctx.fillRect(i * cellSize, j * cellSize, squareSize, squareSize);
          }
        }
      }

      function update() {
        const updateCount = Math.floor(cols * rows * 0.02);
        for (let i = 0; i < updateCount; i++) {
          const idx = Math.floor(Math.random() * opacities.length);
          const target = Math.random() < 0.5 ? 0 : Math.random() * 0.3;
          opacities[idx] += (target - opacities[idx]) * 0.3;
        }
        for (let i = 0; i < opacities.length; i++) {
          if (Math.random() < 0.005) opacities[i] = Math.random() * 0.3;
        }
      }

      function animate() { update(); draw(); requestAnimationFrame(animate); }

      init();
      animate();
      window.addEventListener('resize', init);
    })();
  </script>
</body>

</html>