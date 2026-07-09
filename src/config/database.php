<?php
ob_start();
// =============================================
// FreshMart Online - Database Configuration
// =============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'freshmart_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Koneksi PDO ke MySQL
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    return $pdo;
}

// =============================================
// Helper Functions
// =============================================

// Redirect dengan pesan flash
function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

// Tampilkan pesan flash
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        $colors = [
            'success' => 'success',
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info'
        ];
        return '<div class="alert alert-' . ($colors[$type] ?? 'info') . ' alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($msg) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    return '';
}

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Cek role user
function checkRole($role) {
    global $baseUrl;
    if (!isLoggedIn()) {
        redirect($baseUrl . '/index.php');
    }
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        redirect($baseUrl . '/index.php');
    }
}

// Escape HTML (XSS Prevention)
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Format harga Rupiah
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Generate order code unik
function generateOrderCode() {
    return 'FM' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
}

// Hitung ongkir berdasarkan berat total
function calculateShipping($totalWeightKg) {
    return 10000;
}

// Kirim notifikasi ke user
function sendNotification($userId, $title, $message, $type = 'system') {
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $message, $type]);
}

// Hitung jumlah notifikasi belum dibaca
function countUnreadNotifications($userId) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi baseUrl secara otomatis
$requestUri = $_SERVER['SCRIPT_NAME'] ?? '';
$posViews = strpos($requestUri, '/src/views/');
if ($posViews !== false) {
    $baseUrl = substr($requestUri, 0, $posViews);
} else {
    $posIndex = strpos($requestUri, '/index.php');
    if ($posIndex !== false) {
        $baseUrl = substr($requestUri, 0, $posIndex);
    } else {
        $baseUrl = '';
    }
}
