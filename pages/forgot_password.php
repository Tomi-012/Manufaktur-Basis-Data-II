<?php
session_start();
require_once '../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username'] ?? '');
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (empty($username) || empty($new_password) || empty($confirm_password)) {
    $error = 'Semua field harus diisi';
  } elseif ($new_password !== $confirm_password) {
    $error = 'Password baru dan konfirmasi tidak cocok';
  } elseif (strlen($new_password) < 6) {
    $error = 'Password baru minimal 6 karakter';
  } else {
    try {
      // Cek user
      $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
      $stmt->execute([$username]);
      $user = $stmt->fetch();

      if (!$user) {
        $error = 'Username tidak ditemukan';
      } else {
        // Update password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expire = NULL WHERE id = ?");
        $stmt->execute([$hashed, $user['id']]);

        // Log aktivitas
        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aksi, detail) VALUES (?, 'reset_password', 'Password direset melalui halaman lupa password')");
        $stmt->execute([$user['id']]);

        $_SESSION['success'] = 'Password berhasil direset! Silakan login dengan password baru Anda.';
        header('Location: login.php');
        exit;
      }
    } catch (PDOException $e) {
      $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Manufaktur Tas</title>
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

    #flickering-grid {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
    }

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

    .reset-card {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(20px);
      width: 100%;
      max-width: 440px;
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
      margin-bottom: 30px;
    }

    .logo-icon {
      width: 72px;
      height: 72px;
      background: linear-gradient(135deg, #f59e0b, #d97706);
      border-radius: 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);
      animation: pulse-glow 3s ease-in-out infinite;
    }

    @keyframes pulse-glow {

      0%,
      100% {
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);
      }

      50% {
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.55);
      }
    }

    .logo-icon i {
      font-size: 30px;
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
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: white;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 11px;
      margin-top: 10px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    /* Divider */
    .section-divider {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 18px;
      color: #9ca3af;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }

    .section-divider::before,
    .section-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e5e7eb;
    }

    /* Error / Success */
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

    /* Inputs */
    .input-group {
      margin-bottom: 16px;
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
      color: #f59e0b;
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
      border-color: #f59e0b;
      background: white;
      box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
    }

    /* Password strength */
    .pw-hint {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 5px;
    }

    .pw-strength {
      height: 4px;
      border-radius: 4px;
      margin-top: 8px;
      background: #e5e7eb;
      overflow: hidden;
    }

    .pw-strength-bar {
      height: 100%;
      border-radius: 4px;
      width: 0;
      transition: width 0.3s, background 0.3s;
    }

    /* Button */
    .btn-reset {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #f59e0b, #d97706);
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
      margin-top: 8px;
    }

    .btn-reset:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(245, 158, 11, 0.40);
    }

    .btn-reset:active {
      transform: translateY(0);
    }

    /* Footer */
    .footer-link {
      text-align: center;
      margin-top: 22px;
      padding-top: 18px;
      border-top: 1px solid #e5e7eb;
      font-size: 13px;
    }

    .footer-link a {
      color: #0ea5e9;
      text-decoration: none;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: color 0.2s;
    }

    .footer-link a:hover {
      color: #0284c7;
    }
  </style>
</head>

<body>
  <canvas id="flickering-grid"></canvas>
  <div class="gradient-overlay"></div>

  <div class="reset-card">
    <div class="logo-area">
      <h2>Reset Password</h2>
      <p>Ganti password lama dengan yang baru</p>
      <span class="badge-role"><i class="fas fa-circle" style="font-size:6px"></i> KEAMANAN AKUN</span>
    </div>

    <?php if ($error): ?>
      <div class="error-msg">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off">
      <!-- Identitas -->
      <div class="section-divider">Identitas Akun</div>

      <div class="input-group">
        <label for="username">Username</label>
        <div class="input-wrapper">
          <i class="fas fa-user icon"></i>
          <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required autofocus
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
      </div>

      <!-- Password Baru -->
      <div class="section-divider">Password Baru</div>

      <div class="input-group">
        <label for="new_password">Password Baru</label>
        <div class="input-wrapper">
          <i class="fas fa-lock-open icon"></i>
          <input type="password" id="new_password" name="new_password" placeholder="Minimal 6 karakter" required
            minlength="6" oninput="checkStrength(this.value); checkMatch()">
          <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)" tabindex="-1">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <div class="pw-strength">
          <div class="pw-strength-bar" id="strength-bar"></div>
        </div>
        <div class="pw-hint" id="strength-label">Kekuatan password</div>
      </div>

      <div class="input-group">
        <label for="confirm_password">Konfirmasi Password Baru</label>
        <div class="input-wrapper">
          <i class="fas fa-check-double icon"></i>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru"
            required minlength="6" oninput="checkMatch()">
          <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)" tabindex="-1">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <div class="pw-hint" id="match-label"></div>
      </div>

      <button type="submit" class="btn-reset">
        <i class="fas fa-shield-halved"></i> SIMPAN PASSWORD BARU
      </button>
    </form>

    <div class="footer-link">
      <a href="login.php"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
    </div>
  </div>

  <script>
    function togglePw(fieldId, btn) {
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

    function checkStrength(val) {
      const bar = document.getElementById('strength-bar');
      const label = document.getElementById('strength-label');
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      const levels = [
        { pct: '20%', color: '#ef4444', text: 'Sangat Lemah' },
        { pct: '40%', color: '#f97316', text: 'Lemah' },
        { pct: '60%', color: '#eab308', text: 'Sedang' },
        { pct: '80%', color: '#84cc16', text: 'Kuat' },
        { pct: '100%', color: '#22c55e', text: 'Sangat Kuat' },
      ];
      const lvl = levels[Math.min(score, 4)];
      bar.style.width = val ? lvl.pct : '0';
      bar.style.background = lvl.color;
      label.textContent = val ? lvl.text : 'Kekuatan password';
      label.style.color = val ? lvl.color : '#9ca3af';
    }

    function checkMatch() {
      const np = document.getElementById('new_password').value;
      const cp = document.getElementById('confirm_password').value;
      const lbl = document.getElementById('match-label');
      if (!cp) { lbl.textContent = ''; return; }
      if (np === cp) {
        lbl.textContent = '✓ Password cocok';
        lbl.style.color = '#22c55e';
      } else {
        lbl.textContent = '✗ Password tidak cocok';
        lbl.style.color = '#ef4444';
      }
    }

    // Flickering Grid
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
            ctx.fillStyle = 'rgba(0,0,0,' + opacities[i + j * cols] + ')';
            ctx.fillRect(i * cellSize, j * cellSize, squareSize, squareSize);
          }
        }
      }

      function update() {
        const n = Math.floor(cols * rows * 0.02);
        for (let i = 0; i < n; i++) {
          const idx = Math.floor(Math.random() * opacities.length);
          const t = Math.random() < 0.5 ? 0 : Math.random() * 0.3;
          opacities[idx] += (t - opacities[idx]) * 0.3;
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