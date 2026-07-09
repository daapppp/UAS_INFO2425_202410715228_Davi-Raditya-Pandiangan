<?php
// =============================================
// FreshMart Online - Seller Dashboard
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('seller');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$sellerId = $_SESSION['user_id'];

// Statistik Penjualan
$totalProducts = $db->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$totalProducts->execute([$sellerId]);
$totalProducts = $totalProducts->fetchColumn();

$totalOrders = $db->prepare("SELECT COUNT(DISTINCT oi.order_id) 
                             FROM order_items oi 
                             JOIN orders o ON oi.order_id = o.id 
                             WHERE oi.seller_id = ? AND o.status != 'cancelled'");
$totalOrders->execute([$sellerId]);
$totalOrders = $totalOrders->fetchColumn();

$pendingOrders = $db->prepare("SELECT COUNT(DISTINCT oi.order_id) 
                                FROM order_items oi 
                                JOIN orders o ON oi.order_id = o.id 
                                WHERE oi.seller_id = ? AND o.status IN ('paid', 'confirmed')");
$pendingOrders->execute([$sellerId]);
$pendingOrders = $pendingOrders->fetchColumn();

$totalRevenue = $db->prepare("SELECT COALESCE(SUM(oi.subtotal), 0) 
                              FROM order_items oi 
                              JOIN orders o ON oi.order_id = o.id 
                              WHERE oi.seller_id = ? AND o.status IN ('confirmed', 'shipped', 'delivered')");
$totalRevenue->execute([$sellerId]);
$totalRevenue = $totalRevenue->fetchColumn();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-1">Dashboard Penjual</h3>
    <p class="text-muted mb-4">Selamat datang, <?php echo e($_SESSION['user_name']); ?>!</p>

    <!-- Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#198754;">
                <i class="fas fa-box icon"></i>
                <small>Total Produk</small>
                <h3><?php echo $totalProducts; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#0d6efd;">
                <i class="fas fa-receipt icon"></i>
                <small>Total Pesanan</small>
                <h3><?php echo $totalOrders; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#ffc107; color:#333;">
                <i class="fas fa-clock icon"></i>
                <small>Perlu Diproses</small>
                <h3><?php echo $pendingOrders; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#6f42c1;">
                <i class="fas fa-money-bill icon"></i>
                <small>Total Pendapatan</small>
                <h3><?php echo formatRupiah($totalRevenue); ?></h3>
            </div>
        </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="fw-bold">Pesanan Terbaru</h5>
                <a href="orders.php" class="btn btn-sm btn-green">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $db->prepare("SELECT DISTINCT o.id, o.order_code, o.total_amount, o.status, o.created_at, u.name AS buyer_name 
                                              FROM orders o 
                                              JOIN order_items oi ON o.id = oi.order_id 
                                              JOIN users u ON o.buyer_id = u.id 
                                              WHERE oi.seller_id = ? 
                                              ORDER BY o.created_at DESC LIMIT 5");
                        $stmt->execute([$sellerId]);
                        $orders = $stmt->fetchAll();
                        if (empty($orders)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada pesanan.</td></tr>
                        <?php else: foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($o['order_code']); ?></td>
                            <td><?php echo e($o['buyer_name']); ?></td>
                            <td class="fw-bold"><?php echo formatRupiah($o['total_amount']); ?></td>
                            <td><span class="badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="products.php" class="card border-0 shadow-sm p-3 text-center text-decoration-none text-dark">
                <i class="fas fa-box text-success" style="font-size:2rem;"></i>
                <h6 class="mt-2 mb-0">Kelola Produk</h6>
            </a>
        </div>
        <div class="col-md-4">
            <a href="orders.php" class="card border-0 shadow-sm p-3 text-center text-decoration-none text-dark">
                <i class="fas fa-receipt text-primary" style="font-size:2rem;"></i>
                <h6 class="mt-2 mb-0">Kelola Pesanan</h6>
            </a>
        </div>
        <div class="col-md-4">
            <a href="add_product.php" class="card border-0 shadow-sm p-3 text-center text-decoration-none text-dark">
                <i class="fas fa-plus-circle text-warning" style="font-size:2rem;"></i>
                <h6 class="mt-2 mb-0">Tambah Produk</h6>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
