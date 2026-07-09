<?php
// =============================================
// FreshMart Online - Seller Order Detail
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('seller');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$sellerId = $_SESSION['user_id'];
$orderId = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT o.*, u.name AS buyer_name, u.phone AS buyer_phone 
                      FROM orders o 
                      JOIN users u ON o.buyer_id = u.id 
                      WHERE o.id = ? AND EXISTS (
                          SELECT 1 FROM order_items WHERE order_id = ? AND seller_id = ?
                      )");
$stmt->execute([$orderId, $orderId, $sellerId]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="container py-5"><div class="alert alert-danger">Pesanan tidak ditemukan.</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

// Ambil items milik seller ini
$itemsStmt = $db->prepare("SELECT oi.*, p.name AS product_name, p.image 
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           WHERE oi.order_id = ? AND oi.seller_id = ?");
$itemsStmt->execute([$orderId, $sellerId]);
$items = $itemsStmt->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3">Detail Pesanan: <?php echo e($order['order_code']); ?></h3>

    <div class="row g-4">
        <div class="col-md-8">
            <!-- Items -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Item Pesanan (Produk Anda)</h5>
                    <?php foreach ($items as $item): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <?php if ($item['image']): ?>
                            <img src="<?php echo $baseUrl; ?>/src/uploads/products/<?php echo e($item['image']); ?>" style="width:60px;height:60px;object-fit:cover;" class="rounded me-3">
                        <?php else: ?>
                            <div class="no-image me-3" style="width:60px;height:60px;font-size:1rem;"><i class="fas fa-box"></i></div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><?php echo e($item['product_name']); ?></h6>
                            <small class="text-muted"><?php echo formatRupiah($item['price']); ?> x <?php echo $item['quantity']; ?></small>
                        </div>
                        <strong><?php echo formatRupiah($item['subtotal']); ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Info Pesanan -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Info Pesanan</h5>
                    <p><strong>Status:</strong> <span class="badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
                    <p class="mb-1"><strong>Pembeli:</strong> <?php echo e($order['buyer_name']); ?></p>
                    <p class="mb-1"><strong>Telepon:</strong> <?php echo e($order['buyer_phone']); ?></p>
                    <p class="mb-0"><strong>Alamat:</strong><br><?php echo nl2br(e($order['shipping_address'])); ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Total</h5>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal Produk Anda</span>
                        <span class="fw-bold"><?php 
                            $myTotal = 0;
                            foreach ($items as $i) $myTotal += $i['subtotal'];
                            echo formatRupiah($myTotal); 
                        ?></span>
                    </div>
                </div>
            </div>

            <?php if ($order['status'] === 'paid'): ?>
                <form method="POST" action="orders.php" class="mt-3">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" name="confirm_order" class="btn btn-green w-100 fw-bold">
                        <i class="fas fa-check"></i> Konfirmasi Pesanan
                    </button>
                </form>
            <?php elseif ($order['status'] === 'confirmed'): ?>
                <form method="POST" action="orders.php" class="mt-3">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" name="confirm_shipping" class="btn btn-primary w-100 fw-bold">
                        <i class="fas fa-truck"></i> Konfirmasi Pengiriman
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
