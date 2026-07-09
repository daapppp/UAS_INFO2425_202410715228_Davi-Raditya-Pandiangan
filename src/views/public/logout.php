<?php
// =============================================
// FreshMart Online - Logout
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';

// Hapus semua session
$_SESSION = [];
session_destroy();

// Hapus cookie remember me
if (isset($_COOKIE['remember_email'])) {
    setcookie('remember_email', '', time() - 3600, '/');
}

header('Location: ' . $baseUrl . '/src/views/public/login.php');
exit;
