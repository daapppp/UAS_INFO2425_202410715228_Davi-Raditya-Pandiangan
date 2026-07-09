<?php
// =============================================
// FreshMart Online - Entry Point (index.php)
// =============================================
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
session_start();

// Redirect ke halaman publik
header('Location: ' . $baseUrl . '/src/views/public/index.php');
exit;
