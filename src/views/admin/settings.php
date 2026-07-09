<?php
// =============================================
// FreshMart Online - Admin Settings / Generate Invoice (System Automation)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('admin');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();

// Generate Invoice (System Automation Feature)
if (isset($_GET['generate_invoice'])) {
    $orderId = intval($_GET['generate_invoice']);
    $stmt = $db->prepare("SELECT o.*, u.name AS buyer_name, u.email, u.phone, u.address 
                          FROM orders o JOIN users u ON o.buyer_id = u.id WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        // Ambil items
        $itemsStmt = $db->prepare("SELECT oi.*, p.name AS product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();

        // Buat invoice HTML sederhana
        echo '<div class="container py-5" style="max-width:800px;">';
        echo '<div class="card border">';
        echo '<div class="card-body">';
        echo '<h3 class="text-center fw-bold mb-0"><i class="fas fa-leaf text-success"></i> FreshMart Online</h3>';
        echo '<p class="text-center text-muted mb-4">Invoice</p>';
        echo '<hr>';
        echo '<div class="row mb-3">';
        echo '<div class="col-6"><strong>Invoice:</strong> INV-' . e($order['order_code']) . '<br>';
        echo '<strong>Tanggal:</strong> ' . date('d F Y', strtotime($order['created_at'])) . '</div>';
        echo '<div class="col-6 text-end"><strong>Kepada:</strong><br>' . e($order['buyer_name']) . '<br>' . e($order['address'] ?? '') . '</div>';
        echo '</div>';
        echo '<table class="table"><thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody>';
        foreach ($items as $item) {
            echo '<tr><td>' . e($item['product_name']) . '</td><td>' . $item['quantity'] . '</td>';
            echo '<td>' . formatRupiah($item['price']) . '</td><td>' . formatRupiah($item['subtotal']) . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<div class="text-end">';
        echo '<p>Subtotal: <strong>' . formatRupiah($order['total_amount']) . '</strong></p>';
        echo '<p>Ongkir: <strong>' . formatRupiah($order['shipping_cost']) . '</strong></p>';
        echo '<hr>';
        echo '<h4 class="text-danger">Total: ' . formatRupiah($order['total_amount'] + $order['shipping_cost']) . '</h4>';
        echo '</div>';
        echo '<hr><p class="text-center text-muted"><small>Invoice ini digenerate otomatis oleh sistem FreshMart Online</small></p>';
        echo '</div></div></div>';
        require_once __DIR__ . '/../partials/footer.php';
        exit;
    }
}

// Ambil semua pesanan untuk generate invoice
$orders = $db->query("SELECT o.*, u.name AS buyer_name FROM orders o JOIN users u ON o.buyer_id = u.id ORDER BY o.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-cog"></i> System Settings & Invoice</h3>

    <!-- Generate Invoice Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="fas fa-file-invoice"></i> Auto Generate Invoice</h5>
            <p class="text-muted">Pilih pesanan untuk melihat/mencetak invoice (System Automation: Auto Generate Invoice).</p>
            <div class="table-responsive">
                <table class="table">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pembeli</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($o['order_code']); ?></td>
                            <td><?php echo e($o['buyer_name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                            <td><?php echo formatRupiah($o['total_amount']); ?></td>
                            <td><span class="badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                            <td>
                                <a href="?generate_invoice=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-print"></i> Cetak Invoice
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Automation Info -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="fas fa-robot text-primary"></i> Fitur System Automation</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <i class="fas fa-boxes text-success" style="font-size:2rem;"></i>
                        <h6 class="mt-2">Auto Update Stok</h6>
                        <small class="text-muted">Stok produk otomatis berkurang saat checkout dan kembali saat pesanan dibatalkan.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <i class="fas fa-calculator text-primary" style="font-size:2rem;"></i>
                        <h6 class="mt-2">Auto Kalkulasi Ongkir</h6>
                        <small class="text-muted">Ongkir dihitung otomatis flat Rp 10.000 per transaksi.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <i class="fas fa-bell text-warning" style="font-size:2rem;"></i>
                        <h6 class="mt-2">Auto Notification</h6>
                        <small class="text-muted">Notifikasi otomatis dikirim ke user saat ada perubahan status pesanan/pembayaran.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <i class="fas fa-file-invoice text-danger" style="font-size:2rem;"></i>
                        <h6 class="mt-2">Auto Generate Invoice</h6>
                        <small class="text-muted">Invoice otomatis tersedia untuk setiap pesanan, siap cetak.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <i class="fas fa-exchange-alt text-info" style="font-size:2rem;"></i>
                        <h6 class="mt-2">Auto Update Status</h6>
                        <small class="text-muted">Status order otomatis berubah saat pembayaran diverifikasi oleh admin.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
