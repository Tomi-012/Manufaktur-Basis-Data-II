<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Log aktivitas logout
if (isset($_SESSION['user_id'])) {
    logAktivitas($pdo, $_SESSION['user_id'], 'logout', null, null, 'User logout dari sistem');
}

// Destroy session
session_destroy();
header('Location: login.php');
exit;
