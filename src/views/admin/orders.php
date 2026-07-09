<?php
// =============================================
// FreshMart Online - Admin Manage Orders
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('admin');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();

// Handle update status
if (isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    $allowedStatuses = ['pending', 'paid', 'confirmed', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        
        // SYSTEM OTOMASI: Kirim notifikasi
        $orderStmt = $db->prepare("SELECT buyer_id, order_code FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $orderData = $orderStmt->fetch();
        if ($orderData) {
            $statusLabels = [
                'pending' => 'Menunggu Pembayaran',
                'paid' => 'Pembayaran Diverifikasi',
                'confirmed' => 'Pesanan Dikonfirmasi',
                'shipped' => 'Dalam Pengiriman',
                'delivered' => 'Pesanan Diterima',
                'cancelled' => 'Pesanan Dibatalkan'
            ];
            sendNotification($orderData['buyer_id'], 'Status Pesanan Diperbarui', 
                "Pesanan #{$orderData['order_code']} status: {$statusLabels[$newStatus]}", 'order');
        }
        
        // Jika status diubah ke 'cancelled', kembalikan stok (SYSTEM OTOMASI)
        if ($newStatus === 'cancelled') {
            $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$orderId]);
            foreach ($itemsStmt->fetchAll() as $item) {
                $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
            }
        }
        
        redirect($baseUrl . '/src/views/admin/orders.php', 'Status pesanan berhasil diperbarui!', 'success');
    }
}

// Handle verifikasi pembayaran
if (isset($_POST['verify_payment'])) {
    $paymentId = intval($_POST['payment_id'] ?? 0);
    $action = $_POST['verify_action'] ?? '';
    
    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE payments SET status = 'verified', verified_by = ?, verified_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $paymentId]);
        // Update order status ke 'paid' (SYSTEM OTOMASI)
        $payStmt = $db->prepare("SELECT order_id FROM payments WHERE id = ?");
        $payStmt->execute([$paymentId]);
        $payData = $payStmt->fetch();
        if ($payData) {
            $db->prepare("UPDATE orders SET status = 'paid' WHERE id = ? AND status = 'pending'")->execute([$payData['order_id']]);
            $orderStmt = $db->prepare("SELECT buyer_id, order_code FROM orders WHERE id = ?");
            $orderStmt->execute([$payData['order_id']]);
            $orderData = $orderStmt->fetch();
            if ($orderData) {
                sendNotification($orderData['buyer_id'], 'Pembayaran Diverifikasi', 
                    "Pembayaran pesanan #{$orderData['order_code']} telah diverifikasi.", 'payment');
            }
        }
        redirect($baseUrl . '/src/views/admin/orders.php', 'Pembayaran berhasil diverifikasi!', 'success');
    } elseif ($action === 'reject') {
        $stmt = $db->prepare("UPDATE payments SET status = 'rejected', verified_by = ?, verified_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $paymentId]);
        redirect($baseUrl . '/src/views/admin/orders.php', 'Pembayaran ditolak!', 'warning');
    }
}

// Handle delete order
if (isset($_POST['delete_order'])) {
    $orderId = intval($_POST['order_id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    redirect($baseUrl . '/src/views/admin/orders.php', 'Pesanan berhasil dihapus!', 'success');
}

// Ambil semua pesanan
$orders = $db->query("SELECT o.*, u.name AS buyer_name, p.status as payment_status, p.payment_method, p.proof, p.id as payment_id 
                      FROM orders o 
                      JOIN users u ON o.buyer_id = u.id 
                      LEFT JOIN payments p ON o.id = p.order_id 
                      ORDER BY o.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-receipt"></i> Kelola Pesanan</h3>

    <!-- Pembayaran Pending -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="fas fa-clock text-warning"></i> Menunggu Verifikasi Pembayaran</h5>
            <?php
            $pendingPayments = array_filter($orders, fn($o) => ($o['payment_status'] ?? '') === 'pending' && $o['proof']);
            if (empty($pendingPayments)): ?>
                <p class="text-muted">Tidak ada pembayaran yang menunggu verifikasi.</p>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($pendingPayments as $o): ?>
                <div class="col-md-4">
                    <div class="card border">
                        <div class="card-body">
                            <h6 class="fw-bold"><?php echo e($o['order_code']); ?></h6>
                            <p class="mb-1 text-muted">Pembeli: <?php echo e($o['buyer_name']); ?></p>
                            <p class="mb-2 fw-bold text-danger"><?php echo formatRupiah($o['total_amount'] + $o['shipping_cost']); ?></p>
                            <?php if ($o['proof']): ?>
                                <img src="<?php echo $baseUrl; ?>/src/uploads/payments/<?php echo e($o['proof']); ?>" class="rounded mb-2" style="max-width:100%;max-height:150px;">
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <form method="POST" class="flex-fill">
                                    <input type="hidden" name="payment_id" value="<?php echo $o['payment_id']; ?>">
                                    <input type="hidden" name="verify_action" value="approve">
                                    <button type="submit" name="verify_payment" class="btn btn-sm btn-success w-100">Verifikasi</button>
                                </form>
                                <form method="POST" class="flex-fill">
                                    <input type="hidden" name="payment_id" value="<?php echo $o['payment_id']; ?>">
                                    <input type="hidden" name="verify_action" value="reject">
                                    <button type="submit" name="verify_payment" class="btn btn-sm btn-danger w-100">Tolak</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Semua Pesanan -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Kode</th>
                            <th>Pembeli</th>
                            <th>Detail Barang</th>
                            <th>Tanggal</th>
                            <th>Total (Ongkir)</th>
                            <th>Bayar</th>
                            <th>Status Order</th>
                            <th>Update Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($o['order_code']); ?></td>
                            <td><?php echo e($o['buyer_name']); ?></td>
                            <td>
                                <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
                                    <?php
                                    $itemsStmt = $db->prepare("SELECT oi.quantity, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                                    $itemsStmt->execute([$o['id']]);
                                    $orderItems = $itemsStmt->fetchAll();
                                    foreach ($orderItems as $item):
                                    ?>
                                        <li><?php echo e($item['name']); ?> (x<?php echo $item['quantity']; ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                            <td>
                                <strong><?php echo formatRupiah($o['total_amount'] + $o['shipping_cost']); ?></strong>
                                <br><small class="text-muted">Barang: <?php echo formatRupiah($o['total_amount']); ?></small>
                                <br><small class="text-muted">Ongkir: <?php echo formatRupiah($o['shipping_cost']); ?></small>
                            </td>
                            <td>
                                <span class="badge <?php echo $o['payment_status'] === 'verified' ? 'bg-success' : ($o['payment_status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                    <?php echo ucfirst($o['payment_status'] ?? '-'); ?>
                                </span>
                            </td>
                            <td><span class="badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                    <select name="new_status" class="form-select form-select-sm" style="max-width:120px;">
                                        <option value="pending" <?php echo $o['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="paid" <?php echo $o['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        <option value="confirmed" <?php echo $o['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="shipped" <?php echo $o['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $o['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $o['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-green">Update</button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form method="POST" onsubmit="return confirmDelete('Apakah Anda yakin ingin menghapus pesanan ini?')">
                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                    <button type="submit" name="delete_order" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
