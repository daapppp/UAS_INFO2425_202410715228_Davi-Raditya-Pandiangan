<?php
// =============================================
// FreshMart Online - Submit Review
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';

checkRole('buyer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $userId = $_SESSION['user_id'];

    // Validasi
    if ($rating < 1 || $rating > 5) {
        redirect($baseUrl . '/src/views/public/product.php?id=' . $productId, 'Rating harus antara 1-5.', 'error');
    }
    if (empty($comment)) {
        redirect($baseUrl . '/src/views/public/product.php?id=' . $productId, 'Komentar wajib diisi.', 'error');
    }

    $db = getDBConnection();

    // Cek apakah sudah pernah review
    $stmt = $db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$productId, $userId]);
    if ($stmt->fetch()) {
        redirect($baseUrl . '/src/views/public/product.php?id=' . $productId, 'Anda sudah pernah mereview produk ini.', 'warning');
    }

    // Upload gambar review (opsional)
    $reviewImage = null;
    if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $reviewImage = 'review_' . $userId . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['review_image']['tmp_name'], __DIR__ . '/../../uploads/products/' . $reviewImage);
        }
    }

    // Cek apakah user pernah memesan produk ini
    $orderCheck = $db->prepare("SELECT oi.order_id FROM order_items oi 
                                JOIN orders o ON oi.order_id = o.id 
                                WHERE oi.product_id = ? AND o.buyer_id = ? AND o.status = 'delivered'");
    $orderCheck->execute([$productId, $userId]);
    $order = $orderCheck->fetch();

    if (!$order) {
        redirect($baseUrl . '/src/views/public/product.php?id=' . $productId, 'Anda hanya dapat memberikan review untuk produk yang telah Anda beli dan terkirim.', 'error');
    }

    // Insert review
    $stmt = $db->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, comment, review_image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$productId, $userId, $order['order_id'], $rating, $comment, $reviewImage]);

    // Notifikasi ke penjual
    $productStmt = $db->prepare("SELECT seller_id FROM products WHERE id = ?");
    $productStmt->execute([$productId]);
    $productData = $productStmt->fetch();
    if ($productData) {
        sendNotification($productData['seller_id'], 'Review Baru', 'Produk Anda mendapat review baru.', 'order');
    }

    redirect($baseUrl . '/src/views/public/product.php?id=' . $productId, 'Review berhasil ditambahkan!', 'success');
}
