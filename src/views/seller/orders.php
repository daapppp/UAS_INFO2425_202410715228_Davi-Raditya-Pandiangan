<?php
// =============================================
// FreshMart Online - Seller Orders
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('seller');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$sellerId = $_SESSION['user_id'];

// Handle konfirmasi pengiriman
if (isset($_POST['confirm_shipping'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    $stmt = $db->prepare("UPDATE orders SET status = 'shipped' WHERE id = ? AND status = 'confirmed'");
    $stmt->execute([$orderId]);
    if ($stmt->rowCount() > 0) {
        // SYSTEM OTOMASI: Kirim notifikasi ke buyer
        $orderStmt = $db->prepare("SELECT buyer_id, order_code FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $orderData = $orderStmt->fetch();
        if ($orderData) {
            sendNotification($orderData['buyer_id'], 'Pesanan Dikirim', 
                "Pesanan #{$orderData['order_code']} telah dikirim. Estimasi tiba dalam 1-2 hari.", 'order');
        }
        redirect($baseUrl . '/src/views/seller/orders.php', 'Pesanan berhasil dikonfirmasi pengiriman!', 'success');
    }
}

// Handle konfirmasi pesanan
if (isset($_POST['confirm_order'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    $stmt = $db->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ? AND status = 'paid'");
    $stmt->execute([$orderId]);
    if ($stmt->rowCount() > 0) {
        $orderStmt = $db->prepare("SELECT buyer_id, order_code FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $orderData = $orderStmt->fetch();
        if ($orderData) {
            sendNotification($orderData['buyer_id'], 'Pesanan Dikonfirmasi', 
                "Pesanan #{$orderData['order_code']} telah dikonfirmasi oleh penjual.", 'order');
        }
        redirect($baseUrl . '/src/views/seller/orders.php', 'Pesanan berhasil dikonfirmasi!', 'success');
    }
}

// Ambil pesanan yang mengandung produk seller
$stmt = $db->prepare("SELECT DISTINCT o.id, o.order_code, o.total_amount, o.shipping_cost, o.status, o.created_at, u.name AS buyer_name 
                       FROM orders o 
                       JOIN order_items oi ON o.id = oi.order_id 
                       JOIN users u ON o.buyer_id = u.id 
                       WHERE oi.seller_id = ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$sellerId]);
$orders = $stmt->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-receipt"></i> Pesanan Masuk</h3>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Belum ada pesanan masuk.</div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pembeli</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($o['order_code']); ?></td>
                            <td><?php echo e($o['buyer_name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                            <td class="fw-bold"><?php echo formatRupiah($o['total_amount']); ?></td>
                            <td><span class="badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                <?php if ($o['status'] === 'paid'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                        <button type="submit" name="confirm_order" class="btn btn-sm btn-green" onclick="return confirm('Konfirmasi pesanan ini?')">Konfirmasi</button>
                                    </form>
                                <?php elseif ($o['status'] === 'confirmed'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                        <button type="submit" name="confirm_shipping" class="btn btn-sm btn-primary" onclick="return confirm('Konfirmasi pengiriman?')">Kirim</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
