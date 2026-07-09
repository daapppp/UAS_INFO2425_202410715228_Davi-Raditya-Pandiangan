<?php
// =============================================
// FreshMart Online - Admin Reports & Analytics
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('admin');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();

// Statistik Bulanan
$stmt = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                    COUNT(*) as total_orders, 
                    COALESCE(SUM(total_amount), 0) as total_revenue
                    FROM orders 
                    WHERE status IN ('paid', 'confirmed', 'shipped', 'delivered')
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month DESC LIMIT 6");
$monthlyStats = $stmt->fetchAll();

// Top Produk
$stmt = $db->query("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    JOIN orders o ON oi.order_id = o.id 
                    WHERE o.status != 'cancelled'
                    GROUP BY oi.product_id 
                    ORDER BY total_sold DESC LIMIT 5");
$topProducts = $stmt->fetchAll();

// Top Penjual
$stmt = $db->query("SELECT u.name, COUNT(DISTINCT oi.order_id) as total_orders, SUM(oi.subtotal) as total_revenue 
                    FROM order_items oi 
                    JOIN users u ON oi.seller_id = u.id 
                    JOIN orders o ON oi.order_id = o.id 
                    WHERE o.status != 'cancelled'
                    GROUP BY oi.seller_id 
                    ORDER BY total_revenue DESC LIMIT 5");
$topSellers = $stmt->fetchAll();

// Pembeli Teraktif
$stmt = $db->query("SELECT u.name, COUNT(*) as total_orders, COALESCE(SUM(o.total_amount), 0) as total_spent 
                    FROM orders o 
                    JOIN users u ON o.buyer_id = u.id 
                    GROUP BY o.buyer_id 
                    ORDER BY total_orders DESC LIMIT 5");
$topBuyers = $stmt->fetchAll();

// Penjualan per kategori
$stmt = $db->query("SELECT c.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    JOIN categories c ON p.category_id = c.id 
                    JOIN orders o ON oi.order_id = o.id 
                    WHERE o.status != 'cancelled'
                    GROUP BY p.category_id 
                    ORDER BY total_revenue DESC");
$categorySales = $stmt->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-chart-bar"></i> Laporan & Analitik</h3>

    <!-- Ringkasan Bulanan -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Penjualan Bulanan</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Bulan</th>
                            <th>Total Pesanan</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthlyStats)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr>
                        <?php else: foreach ($monthlyStats as $m): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $m['month']; ?></td>
                            <td><?php echo $m['total_orders']; ?></td>
                            <td class="fw-bold text-success"><?php echo formatRupiah($m['total_revenue']); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top Produk -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-trophy text-warning"></i> Top Produk</h5>
                    <?php foreach ($topProducts as $p): ?>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <div>
                            <strong><?php echo e($p['name']); ?></strong>
                            <small class="text-muted d-block"><?php echo $p['total_sold']; ?> terjual</small>
                        </div>
                        <span class="text-success fw-bold"><?php echo formatRupiah($p['total_revenue']); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($topProducts)) echo '<p class="text-muted">Belum ada data.</p>'; ?>
                </div>
            </div>
        </div>

        <!-- Top Penjual -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-star text-primary"></i> Top Penjual</h5>
                    <?php foreach ($topSellers as $s): ?>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <div>
                            <strong><?php echo e($s['name']); ?></strong>
                            <small class="text-muted d-block"><?php echo $s['total_orders']; ?> pesanan</small>
                        </div>
                        <span class="text-primary fw-bold"><?php echo formatRupiah($s['total_revenue']); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($topSellers)) echo '<p class="text-muted">Belum ada data.</p>'; ?>
                </div>
            </div>
        </div>

        <!-- Top Pembeli -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-heart text-danger"></i> Top Pembeli</h5>
                    <?php foreach ($topBuyers as $b): ?>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <div>
                            <strong><?php echo e($b['name']); ?></strong>
                            <small class="text-muted d-block"><?php echo $b['total_orders']; ?> pesanan</small>
                        </div>
                        <span class="text-danger fw-bold"><?php echo formatRupiah($b['total_spent']); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($topBuyers)) echo '<p class="text-muted">Belum ada data.</p>'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Penjualan per Kategori -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Penjualan per Kategori</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Kategori</th>
                            <th>Qty Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorySales as $c): ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($c['name']); ?></td>
                            <td><?php echo $c['total_sold']; ?></td>
                            <td class="fw-bold text-success"><?php echo formatRupiah($c['total_revenue']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
