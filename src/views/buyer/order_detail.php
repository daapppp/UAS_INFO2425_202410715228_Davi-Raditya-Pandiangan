<?php
// =============================================
// FreshMart Online - Order Detail (Buyer)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('buyer');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$orderId = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND buyer_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="container py-5"><div class="alert alert-danger">Pesanan tidak ditemukan.</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

// Ambil order items
$itemsStmt = $db->prepare("SELECT oi.*, p.name AS product_name, p.image 
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           WHERE oi.order_id = ?");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

// Ambil payment
$payStmt = $db->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
$payStmt->execute([$orderId]);
$payment = $payStmt->fetch();

// Status tracking
$statusSteps = [
    'pending' => ['label' => 'Menunggu Pembayaran', 'icon' => 'fa-clock', 'active' => true],
    'paid' => ['label' => 'Pembayaran Diverifikasi', 'icon' => 'fa-check', 'active' => true],
    'confirmed' => ['label' => 'Pesanan Dikonfirmasi', 'icon' => 'fa-box', 'active' => true],
    'shipped' => ['label' => 'Dalam Pengiriman', 'icon' => 'fa-truck', 'active' => true],
    'delivered' => ['label' => 'Pesanan Diterima', 'icon' => 'fa-check-double', 'active' => true],
];
$currentStatusIndex = array_search($order['status'], array_keys($statusSteps));
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="orders.php">Pesanan</a></li>
            <li class="breadcrumb-item active"><?php echo e($order['order_code']); ?></li>
        </ol>
    </nav>

    <h3 class="fw-bold mb-3">Detail Pesanan: <?php echo e($order['order_code']); ?></h3>

    <!-- Order Tracking -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Tracking Pesanan</h5>
            <div class="d-flex justify-content-between text-center">
                <?php $stepKeys = array_keys($statusSteps); foreach ($stepKeys as $idx => $key):
                    $step = $statusSteps[$key];
                    $isActive = $idx <= $currentStatusIndex;
                ?>
                <div class="flex-fill">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" 
                         style="width:40px;height:40px;<?php echo $isActive ? 'background:#2d6a4f;color:white;' : 'background:#e9ecef;color:#999;'; ?>">
                        <i class="fas <?php echo $step['icon']; ?>"></i>
                    </div>
                    <small class="<?php echo $isActive ? 'fw-bold' : 'text-muted'; ?>"><?php echo $step['label']; ?></small>
                </div>
                <?php if ($idx < count($stepKeys) - 1): ?>
                <div class="flex-fill d-flex align-items-center justify-content-center" style="padding-top:10px;">
                    <div style="height:3px;width:60%;<?php echo $idx < $currentStatusIndex ? 'background:#2d6a4f;' : 'background:#e9ecef;'; ?>"></div>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Items -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Item Pesanan</h5>
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

        <!-- Info -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Info Pesanan</h5>
                    <div class="mb-2"><strong>Status:</strong> <span class="badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></div>
                    <div class="mb-2"><strong>Tanggal:</strong> <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></div>
                    <div class="mb-2"><strong>Alamat:</strong><br><?php echo nl2br(e($order['shipping_address'])); ?></div>
                    <?php if ($order['notes']): ?>
                    <div class="mb-2"><strong>Catatan:</strong><br><?php echo e($order['notes']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Pembayaran</h5>
                    <?php if ($payment): ?>
                        <div class="mb-2"><strong>Metode:</strong> <?php echo strtoupper(str_replace('_', ' ', $payment['payment_method'])); ?></div>
                        <div class="mb-2"><strong>Status:</strong> <span class="badge <?php echo $payment['status'] === 'verified' ? 'bg-success' : ($payment['status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark'); ?>"><?php echo ucfirst($payment['status']); ?></span></div>
                        <?php if ($payment['proof']): ?>
                            <div class="mb-2"><strong>Bukti Bayar:</strong><br>
                                <img src="<?php echo $baseUrl; ?>/src/uploads/payments/<?php echo e($payment['proof']); ?>" style="max-width:200px;" class="rounded">
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">Belum ada data pembayaran.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Ringkasan</h5>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span><span><?php echo formatRupiah($order['total_amount']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Ongkir</span><span><?php echo formatRupiah($order['shipping_cost']); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total</strong><strong class="text-danger"><?php echo formatRupiah($order['total_amount'] + $order['shipping_cost']); ?></strong>
                    </div>
                </div>
            </div>

            <?php if ($order['status'] === 'pending'): ?>
                <a href="payment_confirm.php?id=<?php echo $order['id']; ?>" class="btn btn-green w-100 mt-3 fw-bold">
                    <i class="fas fa-credit-card"></i> Konfirmasi Pembayaran
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
