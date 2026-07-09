<?php
// =============================================
// FreshMart Online - Product Detail Page
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$productId = intval($_GET['id'] ?? 0);

// Ambil detail produk
$stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.icon, u.name AS seller_name 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      JOIN users u ON p.seller_id = u.id 
                      WHERE p.id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="container py-5"><div class="alert alert-danger">Produk tidak ditemukan.</div></div>';
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

// Ambil rating produk
$ratingStmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ?");
$ratingStmt->execute([$productId]);
$rating = $ratingStmt->fetch();

// Ambil review produk
$reviewStmt = $db->prepare("SELECT r.*, u.name AS reviewer_name 
                            FROM reviews r JOIN users u ON r.user_id = u.id 
                            WHERE r.product_id = ? ORDER BY r.created_at DESC");
$reviewStmt->execute([$productId]);
$reviews = $reviewStmt->fetchAll();

// Cek apakah user sudah pernah review produk ini & apakah sudah pernah membeli dan terkirim
$hasReviewed = false;
$hasOrdered = false;
if (isLoggedIn() && $_SESSION['user_role'] === 'buyer') {
    $checkStmt = $db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $checkStmt->execute([$productId, $_SESSION['user_id']]);
    $hasReviewed = $checkStmt->fetch() ? true : false;

    $orderCheck = $db->prepare("SELECT oi.order_id FROM order_items oi 
                                JOIN orders o ON oi.order_id = o.id 
                                WHERE oi.product_id = ? AND o.buyer_id = ? AND o.status = 'delivered'");
    $orderCheck->execute([$productId, $_SESSION['user_id']]);
    $hasOrdered = $orderCheck->fetch() ? true : false;
}
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="search.php?category=<?php echo $product['category_id']; ?>"><?php echo e($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo e($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Gambar Produk -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <?php if ($product['image']): ?>
                    <img src="<?php echo $baseUrl; ?>/src/uploads/products/<?php echo e($product['image']); ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>" style="max-height:400px; object-fit:cover;">
                <?php else: ?>
                    <div class="no-image" style="height:400px;"><i class="fas fa-image"></i></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Produk -->
        <div class="col-md-7">
            <h2 class="fw-bold"><?php echo e($product['name']); ?></h2>
            <p class="text-muted mb-1">
                <?php echo e($product['icon']); ?> <?php echo e($product['category_name']); ?> &bull; 
                <i class="fas fa-store"></i> <?php echo e($product['seller_name']); ?>
            </p>
            <div class="mb-3">
                <span class="stars me-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= round($rating['avg_rating']) ? '' : 'text-muted'; ?>"></i>
                    <?php endfor; ?>
                </span>
                <small class="text-muted">(<?php echo $rating['total']; ?> review)</small>
            </div>
            <h3 class="text-danger fw-bold mb-3"><?php echo formatRupiah($product['price']); ?></h3>
            <p><?php echo nl2br(e($product['description'])); ?></p>
            
            <div class="row mb-3">
                <div class="col-auto">
                    <span class="badge bg-success p-2">Stok: <?php echo $product['stock']; ?></span>
                </div>
                <div class="col-auto">
                    <span class="badge bg-light text-dark p-2">Berat: <?php echo $product['weight_kg']; ?> kg</span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <?php if ($product['stock'] > 0): ?>
                <?php if (isLoggedIn() && $_SESSION['user_role'] === 'buyer'): ?>
                <div class="d-flex gap-2 mb-3">
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-green btn-lg">
                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                    </button>
                </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-success btn-lg"><i class="fas fa-sign-in-alt"></i> Login untuk Beli</a>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>Stok Habis</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section Review -->
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="fw-bold mb-3">Review & Rating</h4>
            
            <?php if (isLoggedIn() && $_SESSION['user_role'] === 'buyer' && !$hasReviewed && $hasOrdered): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Tulis Review</h6>
                    <form action="submit_review.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="mb-2">
                            <div id="starRating" class="stars" style="font-size:1.5rem; cursor:pointer;">
                                <i class="far fa-star text-muted"></i>
                                <i class="far fa-star text-muted"></i>
                                <i class="far fa-star text-muted"></i>
                                <i class="far fa-star text-muted"></i>
                                <i class="far fa-star text-muted"></i>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="0">
                        </div>
                        <div class="mb-2">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                        </div>
                        <div class="mb-2">
                            <input type="file" name="review_image" class="form-control" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-green">Kirim Review</button>
                    </form>
                </div>
            </div>
            <script>initStarRating('starRating', 'ratingInput');</script>
            <?php endif; ?>

            <!-- List Review -->
            <?php if (empty($reviews)): ?>
                <p class="text-muted">Belum ada review untuk produk ini.</p>
            <?php else: foreach ($reviews as $rev): ?>
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <strong><?php echo e($rev['reviewer_name']); ?></strong>
                        <small class="text-muted"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></small>
                    </div>
                    <div class="stars mb-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $rev['rating'] ? '' : 'text-muted'; ?>" style="font-size:0.8rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mb-0"><?php echo e($rev['comment']); ?></p>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
