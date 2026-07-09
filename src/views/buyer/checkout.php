<?php
// =============================================
// FreshMart Online - Checkout Page
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('buyer');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    redirect($baseUrl . '/src/views/buyer/cart.php', 'Keranjang kosong!', 'warning');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($address)) {
        $error = 'Alamat pengiriman wajib diisi!';
    } elseif (empty($paymentMethod)) {
        $error = 'Pilih metode pembayaran!';
    } else {
        // Hitung total
        $totalAmount = 0;
        $totalWeight = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['qty'];
            $totalWeight += 0.5 * $item['qty'];
        }
        $shippingCost = calculateShipping($totalWeight);
        $grandTotal = $totalAmount + $shippingCost;

        // Generate order code
        $orderCode = generateOrderCode();

        try {
            $db->beginTransaction();

            // 1. Buat order
            $stmt = $db->prepare("INSERT INTO orders (buyer_id, order_code, total_amount, shipping_cost, shipping_address, status, notes) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $orderCode, $totalAmount, $shippingCost, $address, $notes]);
            $orderId = $db->lastInsertId();

            // 2. Insert order items + update stok (SYSTEM OTOMASI)
            foreach ($cart as $productId => $item) {
                // Insert order item
                $stmt = $db->prepare("SELECT seller_id FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $sellerId = $stmt->fetchColumn();

                $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $productId, $sellerId, $item['qty'], $item['price'], $item['price'] * $item['qty']]);

                // SYSTEM OTOMASI: Auto update stok
                $stmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                $stmt->execute([$item['qty'], $productId, $item['qty']]);
            }

            // 3. Buat payment record
            $stmt = $db->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$orderId, $paymentMethod, $grandTotal]);

            // 4. Kirim notifikasi ke buyer (SYSTEM OTOMASI)
            sendNotification($_SESSION['user_id'], 'Pesanan Dibuat', 
                "Pesanan #$orderCode berhasil dibuat. Total: " . formatRupiah($grandTotal) . ". Silakan lakukan pembayaran.", 'order');

            // Kirim notifikasi ke seller
            $stmt = $db->prepare("SELECT DISTINCT seller_id FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $sellers = $stmt->fetchAll();
            foreach ($sellers as $s) {
                sendNotification($s['seller_id'], 'Pesanan Baru', 
                    "Pesanan #$orderCode masuk. Segera proses pesanan Anda.", 'order');
            }

            $db->commit();

            // Kosongkan cart
            unset($_SESSION['cart']);

            redirect($baseUrl . '/src/views/buyer/orders.php', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.', 'success');

        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage();
        }
    }
}

// Hitung untuk tampilan
$totalAmount = 0;
$totalWeight = 0;
foreach ($cart as $item) {
    $totalAmount += $item['price'] * $item['qty'];
    $totalWeight += 0.5 * $item['qty'];
}
$shippingCost = calculateShipping($totalWeight);
$grandTotal = $totalAmount + $shippingCost;

// Ambil data user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-check-circle"></i> Checkout</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-8">
            <form method="POST">
                <!-- Alamat Pengiriman -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Alamat Pengiriman</h5>
                        <textarea name="address" class="form-control" rows="3" required placeholder="Masukkan alamat lengkap pengiriman..."><?php echo e($user['address'] ?? ''); ?></textarea>
                        <textarea name="notes" class="form-control mt-2" rows="2" placeholder="Catatan tambahan (opsional)..."></textarea>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Metode Pembayaran</h5>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm1" value="transfer_bca" required>
                                    <label class="form-check-label" for="pm1">Transfer BCA</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm2" value="transfer_mandiri">
                                    <label class="form-check-label" for="pm2">Transfer Mandiri</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm3" value="transfer_bni">
                                    <label class="form-check-label" for="pm3">Transfer BNI</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm4" value="cod">
                                    <label class="form-check-label" for="pm4">COD (Bayar di Tempat)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm5" value="ewallet">
                                    <label class="form-check-label" for="pm5">E-Wallet (OVO/GoPay)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Pesanan -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Ringkasan Pesanan</h5>
                        <?php foreach ($cart as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo e($item['name']); ?> x<?php echo $item['qty']; ?></span>
                            <span><?php echo formatRupiah($item['price'] * $item['qty']); ?></span>
                        </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?php echo formatRupiah($totalAmount); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ongkir</span>
                            <span><?php echo formatRupiah($shippingCost); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold">Total</h5>
                            <h5 class="fw-bold text-danger"><?php echo formatRupiah($grandTotal); ?></h5>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-green btn-lg w-100 mt-3 fw-bold">
                    <i class="fas fa-paper-plane"></i> Buat Pesanan
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
