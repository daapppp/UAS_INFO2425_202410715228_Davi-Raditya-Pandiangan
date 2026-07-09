<?php
// =============================================
// FreshMart Online - Buyer Dashboard
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('buyer');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$userId = $_SESSION['user_id'];

// Statistik
$totalOrders = $db->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ?");
$totalOrders->execute([$userId]);
$totalOrders = $totalOrders->fetchColumn();

$pendingOrders = $db->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND status IN ('pending', 'paid', 'confirmed', 'shipped')");
$pendingOrders->execute([$userId]);
$pendingOrders = $pendingOrders->fetchColumn();

$completedOrders = $db->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND status = 'delivered'");
$completedOrders->execute([$userId]);
$completedOrders = $completedOrders->fetchColumn();

$totalSpent = $db->prepare("SELECT COALESCE(SUM(total_amount + shipping_cost), 0) FROM orders WHERE buyer_id = ? AND status IN ('paid', 'confirmed', 'shipped', 'delivered')");
$totalSpent->execute([$userId]);
$totalSpent = $totalSpent->fetchColumn();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-1">Dashboard Pembeli</h3>
    <p class="text-muted mb-4">Selamat datang, <?php echo e($_SESSION['user_name']); ?>!</p>

    <!-- Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#198754;">
                <i class="fas fa-shopping-bag icon"></i>
                <small>Total Pesanan</small>
                <h3><?php echo $totalOrders; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#ffc107; color:#333;">
                <i class="fas fa-clock icon"></i>
                <small>Pesanan Aktif</small>
                <h3><?php echo $pendingOrders; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#0d6efd;">
                <i class="fas fa-check-circle icon"></i>
                <small>Selesai</small>
                <h3><?php echo $completedOrders; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#6f42c1;">
                <i class="fas fa-wallet icon"></i>
                <small>Total Belanja</small>
                <h3><?php echo formatRupiah($totalSpent); ?></h3>
            </div>
        </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="card border-0 shadow-sm">
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
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $db->prepare("SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC LIMIT 5");
                        $stmt->execute([$userId]);
                        $orders = $stmt->fetchAll();
                        if (empty($orders)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada pesanan.</td></tr>
                        <?php else: foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($o['order_code']); ?></td>
                            <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
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
    <div class="row mt-4 g-3">
        <div class="col-md-4">
            <a href="<?php echo $baseUrl; ?>/src/views/public/index.php" class="card border-0 shadow-sm p-3 text-center text-decoration-none text-dark">
                <i class="fas fa-shopping-bag text-success" style="font-size:2rem;"></i>
                <h6 class="mt-2 mb-0">Mulai Belanja</h6>
            </a>
        </div>
        <div class="col-md-4">
            <a href="orders.php" class="card border-0 shadow-sm p-3 text-center text-decoration-none text-dark">
                <i class="fas fa-list text-primary" style="font-size:2rem;"></i>
                <h6 class="mt-2 mb-0">Pesanan Saya</h6>
            </a>
        </div>
        <div class="col-md-4">
            <a href="profile.php" class="card border-0 shadow-sm p-3 text-center text-decoration-none text-dark">
                <i class="fas fa-user text-warning" style="font-size:2rem;"></i>
                <h6 class="mt-2 mb-0">Profil Saya</h6>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
