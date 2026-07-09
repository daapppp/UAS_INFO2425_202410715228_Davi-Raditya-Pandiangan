<?php
// =============================================
// FreshMart Online - Admin Dashboard
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('admin');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();

// Statistik keseluruhan
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$totalSellers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$totalBuyers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ('paid', 'confirmed', 'shipped', 'delivered')")->fetchColumn();
$pendingPayments = $db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$pendingSellers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'seller' AND is_verified = 0")->fetchColumn();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-1">Dashboard Admin</h3>
    <p class="text-muted mb-4">Selamat datang, <?php echo e($_SESSION['user_name']); ?>!</p>

    <!-- Statistik Utama -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#198754;">
                <i class="fas fa-money-bill icon"></i>
                <small>Total Pendapatan</small>
                <h3><?php echo formatRupiah($totalRevenue); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#0d6efd;">
                <i class="fas fa-shopping-bag icon"></i>
                <small>Total Pesanan</small>
                <h3><?php echo $totalOrders; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#6f42c1;">
                <i class="fas fa-users icon"></i>
                <small>Total User</small>
                <h3><?php echo $totalUsers; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background-color:#ffc107; color:#333;">
                <i class="fas fa-exclamation-triangle icon"></i>
                <small>Perlu Verifikasi</small>
                <h3><?php echo $pendingPayments; ?> bayar / <?php echo $pendingSellers; ?> seller</h3>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Distribusi User</h5>
                    <div class="d-flex gap-3 mb-2">
                        <div class="flex-fill bg-success text-white rounded p-2 text-center">
                            <small>Penjual</small><br><strong><?php echo $totalSellers; ?></strong>
                        </div>
                        <div class="flex-fill bg-primary text-white rounded p-2 text-center">
                            <small>Pembeli</small><br><strong><?php echo $totalBuyers; ?></strong>
                        </div>
                        <div class="flex-grow-1 bg-info text-white rounded p-2 text-center">
                            <small>Produk</small><br><strong><?php echo $totalProducts; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Status Pesanan</h5>
                    <?php
                    $statuses = ['pending', 'paid', 'confirmed', 'shipped', 'delivered', 'cancelled'];
                    $colors = ['warning text-dark', 'info', 'primary', 'purple', 'success', 'danger'];
                    $statusStmt = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
                    $statusCounts = [];
                    foreach ($statusStmt->fetchAll() as $s) $statusCounts[$s['status']] = $s['cnt'];
                    ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($statuses as $idx => $st): ?>
                        <span class="badge bg-<?php echo $colors[$idx]; ?> p-2">
                            <?php echo ucfirst($st); ?>: <?php echo $statusCounts[$st] ?? 0; ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Menu Cepat</h5>
            <div class="row g-2">
                <div class="col-md-3"><a href="users.php" class="btn btn-outline-success w-100"><i class="fas fa-users"></i> Kelola User</a></div>
                <div class="col-md-3"><a href="categories.php" class="btn btn-outline-success w-100"><i class="fas fa-tags"></i> Kelola Kategori</a></div>
                <div class="col-md-3"><a href="orders.php" class="btn btn-outline-success w-100"><i class="fas fa-receipt"></i> Kelola Pesanan</a></div>
                <div class="col-md-3"><a href="reports.php" class="btn btn-outline-success w-100"><i class="fas fa-chart-bar"></i> Laporan</a></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
