<?php
// =============================================
// FreshMart Online - Konfirmasi Pembayaran (Buyer)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('buyer');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$orderId = intval($_GET['id'] ?? 0);

// Cek order
$stmt = $db->prepare("SELECT o.*, p.payment_method FROM orders o JOIN payments p ON o.id = p.order_id WHERE o.id = ? AND o.buyer_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="container py-5"><div class="alert alert-danger">Pesanan tidak ditemukan.</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

if ($order['status'] !== 'pending') {
    redirect($baseUrl . '/src/views/buyer/order_detail.php?id=' . $orderId, 'Pesanan sudah diproses.', 'warning');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek apakah ada file upload
    if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload bukti transfer wajib!';
    } else {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = 'Format file tidak valid! Gunakan jpg, png, atau gif.';
        } else {
            $fileName = 'payment_' . $orderId . '_' . time() . '.' . $ext;
            $uploadPath = __DIR__ . '/../../uploads/payments/' . $fileName;

            if (move_uploaded_file($_FILES['proof']['tmp_name'], $uploadPath)) {
                // Update payment record
                $stmt = $db->prepare("UPDATE payments SET proof = ? WHERE order_id = ?");
                $stmt->execute([$fileName, $orderId]);

                // SYSTEM OTOMASI: Update order status to "paid" (menunggu verifikasi)
                // Notifikasi ke admin
                sendNotification(1, 'Bukti Pembayaran', 
                    "Pesanan #{$order['order_code']} mengupload bukti pembayaran. Segera verifikasi.", 'payment');

                redirect($baseUrl . '/src/views/buyer/order_detail.php?id=' . $orderId, 
                    'Bukti pembayaran berhasil diupload! Menunggu verifikasi.', 'success');
            } else {
                $error = 'Gagal mengupload file.';
            }
        }
    }
}
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-credit-card"></i> Konfirmasi Pembayaran</h3>

    <!-- Info Pesanan -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Kode Pesanan:</strong> <?php echo e($order['order_code']); ?></p>
                    <p><strong>Metode:</strong> <?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><strong>Total Bayar:</strong></p>
                    <h4 class="text-danger fw-bold"><?php echo formatRupiah($order['total_amount'] + $order['shipping_cost']); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Rekening -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light">
            <h5 class="fw-bold">Transfer ke Rekening:</h5>
            <?php if ($order['payment_method'] === 'transfer_bca'): ?>
                <p><strong>Bank BCA</strong><br>No. Rekening: 123-456-7890<br>Atas Nama: PT FreshMart Indonesia</p>
            <?php elseif ($order['payment_method'] === 'transfer_mandiri'): ?>
                <p><strong>Bank Mandiri</strong><br>No. Rekening: 098-765-4321<br>Atas Nama: PT FreshMart Indonesia</p>
            <?php elseif ($order['payment_method'] === 'transfer_bni'): ?>
                <p><strong>Bank BNI</strong><br>No. Rekening: 555-666-7777<br>Atas Nama: PT FreshMart Indonesia</p>
            <?php else: ?>
                <p class="text-muted">Pembayaran akan dilakukan saat barang diterima.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload Bukti -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Upload Bukti Transfer</h5>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Bukti Transfer (gambar)</label>
                    <input type="file" name="proof" class="form-control" accept="image/*" required>
                    <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                </div>
                <button type="submit" class="btn btn-green fw-bold">
                    <i class="fas fa-upload"></i> Upload Bukti Bayar
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
