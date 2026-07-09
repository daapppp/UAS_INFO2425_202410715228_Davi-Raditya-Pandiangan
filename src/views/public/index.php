<?php
// =============================================
// FreshMart Online - Homepage (Beranda)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();

// Ambil semua kategori
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Ambil produk terbaru (8 produk)
$stmt = $db->query("SELECT p.*, c.name AS category_name, u.name AS seller_name 
                    FROM products p 
                    JOIN categories c ON p.category_id = c.id 
                    JOIN users u ON p.seller_id = u.id 
                    WHERE p.is_active = 1 
                    ORDER BY p.created_at DESC LIMIT 12");
$products = $stmt->fetchAll();

// Hitung rata-rata rating per produk (dalam satu query)
$ratings = [];
$ratingStmt = $db->query("SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as total 
                           FROM reviews GROUP BY product_id");
foreach ($ratingStmt->fetchAll() as $r) {
    $ratings[$r['product_id']] = ['avg' => round($r['avg_rating'], 1), 'total' => $r['total']];
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1>Belanja Produk Segar Online</h1>
                <p>Sayur, buah, daging, dan kebutuhan dapur segar langsung dari penjual terpercaya. Pengiriman cepat ke rumah Anda!</p>
                <a href="#produk" class="btn btn-light btn-lg fw-bold">
                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                </a>
            </div>
            <div class="col-md-5 text-center d-none d-md-block">
                <i class="fas fa-shopping-basket" style="font-size: 8rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Kategori -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center fw-bold mb-4">Kategori Produk</h3>
        <div class="row g-3">
            <?php foreach ($categories as $cat): ?>
            <div class="col-6 col-md-3">
                <a href="search.php?category=<?php echo $cat['id']; ?>" class="category-card d-block">
                    <div class="icon"><?php echo e($cat['icon']); ?></div>
                    <h6 class="mb-0"><?php echo e($cat['name']); ?></h6>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Produk Terbaru -->
<section class="py-5" id="produk">
    <div class="container">
        <h3 class="text-center fw-bold mb-4">Produk Terbaru</h3>
        <div class="row g-3">
            <?php if (empty($products)): ?>
                <p class="text-center text-muted">Belum ada produk tersedia.</p>
            <?php else: foreach ($products as $p): 
                $rating = $ratings[$p['id']] ?? ['avg' => 0, 'total' => 0];
            ?>
            <div class="col-6 col-md-3">
                <div class="card product-card">
                    <?php if ($p['image']): ?>
                        <img src="<?php echo $baseUrl; ?>/src/uploads/products/<?php echo e($p['image']); ?>" class="card-img-top" alt="<?php echo e($p['name']); ?>">
                    <?php else: ?>
                        <div class="no-image"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <small class="text-muted d-block"><?php echo e($p['category_name']); ?></small>
                        <h6 class="fw-bold mt-1" style="font-size:0.9rem;"><?php echo e($p['name']); ?></h6>
                        <div class="d-flex align-items-center mb-2">
                            <span class="stars me-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= round($rating['avg']) ? '' : 'text-muted'; ?>" style="font-size:0.7rem;"></i>
                                <?php endfor; ?>
                            </span>
                            <small class="text-muted">(<?php echo $rating['total']; ?>)</small>
                        </div>
                        <div class="price"><?php echo formatRupiah($p['price']); ?></div>
                        <small class="stock-badge badge <?php echo $p['stock'] > 10 ? 'bg-success' : ($p['stock'] > 0 ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                            Stok: <?php echo $p['stock']; ?>
                        </small>
                        <div class="d-flex gap-2 mt-2">
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">Detail</a>
                            <?php if ($p['stock'] > 0 && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'buyer'): ?>
                            <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn btn-sm btn-green flex-grow-1">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<!-- Keunggulan -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-md-3">
                <i class="fas fa-leaf text-success" style="font-size:2rem;"></i>
                <h6 class="mt-2 fw-bold">Produk Segar</h6>
                <small class="text-muted">Dipilih langsung dari petani</small>
            </div>
            <div class="col-md-3">
                <i class="fas fa-truck text-success" style="font-size:2rem;"></i>
                <h6 class="mt-2 fw-bold">Pengiriman Cepat</h6>
                <small class="text-muted">Same-day delivery tersedia</small>
            </div>
            <div class="col-md-3">
                <i class="fas fa-shield-alt text-success" style="font-size:2rem;"></i>
                <h6 class="mt-2 fw-bold">Terjamin Halal</h6>
                <small class="text-muted">Produk terverifikasi kualitasnya</small>
            </div>
            <div class="col-md-3">
                <i class="fas fa-wallet text-success" style="font-size:2rem;"></i>
                <h6 class="mt-2 fw-bold">Harga Terjangkau</h6>
                <small class="text-muted">Langsung dari supplier</small>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
