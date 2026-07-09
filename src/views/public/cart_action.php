<?php
// =============================================
// FreshMart Online - Cart Action (AJAX Handler)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';

// Pastikan buyer
if (!isLoggedIn() || $_SESSION['user_role'] !== 'buyer') {
    echo json_encode(['success' => false, 'message' => 'Anda harus login sebagai pembeli.']);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = intval($_POST['product_id'] ?? 0);

header('Content-Type: application/json');

if ($action === 'add') {
    $qty = intval($_POST['qty'] ?? 1);
    if ($qty < 1) $qty = 1;

    // Cek produk dan stok
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
        exit;
    }

    // Inisialisasi cart di session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Tambah/update di cart
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$productId] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => $qty,
            'stock' => $product['stock']
        ];
    }

    // Cek apakah qty melebihi stok
    if ($_SESSION['cart'][$productId]['qty'] > $product['stock']) {
        $_SESSION['cart'][$productId]['qty'] = $product['stock'];
        echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart']), 'message' => 'Stok terbatas.']);
        exit;
    }

    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);

} elseif ($action === 'update') {
    $qty = intval($_POST['qty'] ?? 1);
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] = max(1, $qty);
    }
    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'] ?? [])]);

} elseif ($action === 'remove') {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'] ?? [])]);

} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
}
