<?php
/**
 * Authentication & Authorization
 * Menangani session, login check, dan role check
 */

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login
 */
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /Manufaktur/pages/login.php');
        exit;
    }
}

/**
 * Cek role user
 * @param array $role_diizinkan Array role yang diizinkan mengakses halaman
 */
function cekRole($role_diizinkan) {
    if (!in_array($_SESSION['role'], $role_diizinkan)) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
        header('Location: /Manufaktur/pages/dashboard.php');
        exit;
    }
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: /Manufaktur/pages/login.php');
    exit;
}

/**
 * Catat aktivitas ke log
 */
function logAktivitas($pdo, $user_id, $aksi, $tabel_referensi = null, $referensi_id = null, $detail = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO log_aktivitas (user_id, aksi, tabel_referensi, referensi_id, detail)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $aksi, $tabel_referensi, $referensi_id, $detail]);
    } catch (PDOException $e) {
        // Silent fail untuk log
        error_log("Log aktivitas gagal: " . $e->getMessage());
    }
}

/**
 * Get user info
 */
function getUserInfo($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
