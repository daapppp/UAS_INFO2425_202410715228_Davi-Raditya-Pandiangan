<?php
// =============================================
// FreshMart Online - Keranjang Belanja
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('buyer');
require_once __DIR__ . '/../partials/header.php';

$cart = $_SESSION['cart'] ?? [];
$totalAmount = 0;
$totalWeight = 0;

foreach ($cart as $item) {
    $subtotal = $item['price'] * $item['qty'];
    $totalAmount += $subtotal;
    // Hitung berat total (estimasi 0.5kg per produk jika tidak ada data)
    $totalWeight += 0.5 * $item['qty'];
}

$shippingCost = calculateShipping($totalWeight);
$grandTotal = $totalAmount + $shippingCost;
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h3>

    <?php if (empty($cart)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-shopping-basket"></i> Keranjang Anda kosong. 
            <a href="index.php" class="fw-bold">Mulai belanja</a>
        </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-8">
            <!-- Daftar Produk -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="bg-green text-white">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Subtotal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $id => $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="no-image me-3" style="width:60px;height:60px;font-size:1.5rem;"><i class="fas fa-box"></i></div>
                                        <span class="fw-bold"><?php echo e($item['name']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo formatRupiah($item['price']); ?></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <button onclick="updateCartQty(<?php echo $id; ?>, <?php echo $item['qty'] - 1; ?>)" 
                                                class="btn btn-sm btn-outline-secondary">-</button>
                                        <span class="mx-2 fw-bold"><?php echo $item['qty']; ?></span>
                                        <button onclick="updateCartQty(<?php echo $id; ?>, <?php echo $item['qty'] + 1; ?>)" 
                                                class="btn btn-sm btn-outline-secondary">+</button>
                                    </div>
                                </td>
                                <td class="text-center fw-bold"><?php echo formatRupiah($item['price'] * $item['qty']); ?></td>
                                <td class="text-center">
                                    <button onclick="removeFromCart(<?php echo $id; ?>)" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Ringkasan Belanja</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span class="fw-bold"><?php echo formatRupiah($totalAmount); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkir (est. <?php echo $totalWeight; ?>kg)</span>
                        <span class="fw-bold"><?php echo formatRupiah($shippingCost); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold mb-0">Total</h5>
                        <h5 class="fw-bold text-danger mb-0"><?php echo formatRupiah($grandTotal); ?></h5>
                    </div>
                    <a href="checkout.php" class="btn btn-green w-100 fw-bold">
                        <i class="fas fa-check"></i> Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
