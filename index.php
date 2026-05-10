<?php
session_start();

// Redirect ke dashboard jika sudah login, ke login jika belum
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
} else {
    header('Location: pages/login.php');
}
exit;
