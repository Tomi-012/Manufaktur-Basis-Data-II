<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();
cekRole(['administrator']);

$id = $_GET['id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;

if ($id && $product_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM bill_of_materials WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Material berhasil dihapus dari BOM.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menghapus material: " . $e->getMessage();
    }
}

header("Location: index.php?product_id=$product_id");
exit;
