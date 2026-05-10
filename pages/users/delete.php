<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$id = $_GET['id'] ?? 0;

// Cek tidak bisa hapus diri sendiri
if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = 'Tidak dapat menghapus akun sendiri';
    header('Location: index.php');
    exit;
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'User tidak ditemukan';
    header('Location: index.php');
    exit;
}

// Hapus user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

logAktivitas($pdo, $_SESSION['user_id'], 'delete_user', 'users', $id, "Menghapus user: " . $user['nama']);

$_SESSION['success'] = 'User berhasil dihapus';
header('Location: index.php');
exit;
