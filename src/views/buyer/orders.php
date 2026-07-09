<?php
// =============================================
// FreshMart Online - Pesanan Saya (Buyer)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('buyer');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// Ambil semua pesanan buyer
$stmt = $db->prepare("SELECT o.*, p.status as payment_status, p.payment_method 
                      FROM orders o 
                      LEFT JOIN payments p ON o.id = p.order_id 
                      WHERE o.buyer_id = ? 
                      ORDER BY o.created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-list"></i> Pesanan Saya</h3>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Belum ada pesanan.</div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Ongkir</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): 
                            // Ambil jumlah item
                            $itemStmt = $db->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                            $itemStmt->execute([$o['id']]);
                            $itemCount = $itemStmt->fetchColumn();
                        ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($o['order_code']); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($o['created_at'])); ?></td>
                            <td><?php echo $itemCount; ?> item</td>
                            <td class="fw-bold"><?php echo formatRupiah($o['total_amount']); ?></td>
                            <td><?php echo formatRupiah($o['shipping_cost']); ?></td>
                            <td>
                                <span class="badge <?php echo $o['payment_status'] === 'verified' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?php echo ucfirst($o['payment_status'] ?? 'pending'); ?>
                                </span>
                            </td>
                            <td><span class="badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                <?php if ($o['status'] === 'pending'): ?>
                                    <a href="payment_confirm.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-green">Bayar</a>
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
