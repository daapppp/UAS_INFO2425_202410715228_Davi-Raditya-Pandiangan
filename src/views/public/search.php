<?php
// =============================================
// FreshMart Online - Search & Filter Page
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$searchQuery = trim($_GET['q'] ?? '');
$categoryFilter = intval($_GET['category'] ?? 0);

// Bangun query pencarian
$where = ["p.is_active = 1"];
$params = [];

if (!empty($searchQuery)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if ($categoryFilter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

$whereSQL = implode(' AND ', $where);
$sql = "SELECT p.*, c.name AS category_name, u.name AS seller_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN users u ON p.seller_id = u.id 
        WHERE $whereSQL ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Ambil kategori untuk filter
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3">
        <?php echo $searchQuery ? 'Hasil Pencarian: "' . e($searchQuery) . '"' : 'Semua Produk'; ?>
    </h3>

    <!-- Filter Kategori -->
    <div class="mb-4">
        <form method="GET" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="q" value="<?php echo e($searchQuery); ?>">
            <a href="search.php?<?php echo $searchQuery ? 'q=' . urlencode($searchQuery) : ''; ?>" 
               class="btn <?php echo $categoryFilter === 0 ? 'btn-green' : 'btn-outline-secondary'; ?> btn-sm">Semua</a>
            <?php foreach ($categories as $cat): ?>
                <a href="search.php?<?php echo 'category=' . $cat['id'] . ($searchQuery ? '&q=' . urlencode($searchQuery) : ''); ?>" 
                   class="btn <?php echo $categoryFilter === $cat['id'] ? 'btn-green' : 'btn-outline-secondary'; ?> btn-sm">
                    <?php echo e($cat['icon'] . ' ' . $cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </form>
    </div>

    <!-- Hasil -->
    <p class="text-muted mb-3"><?php echo count($products); ?> produk ditemukan</p>

    <div class="row g-3">
        <?php if (empty($products)): ?>
            <div class="col-12"><div class="alert alert-info">Tidak ada produk yang ditemukan.</div></div>
        <?php else: foreach ($products as $p): ?>
        <div class="col-6 col-md-3">
            <div class="card product-card">
                <?php if ($p['image']): ?>
                    <img src="<?php echo $baseUrl; ?>/src/uploads/products/<?php echo e($p['image']); ?>" class="card-img-top" alt="<?php echo e($p['name']); ?>">
                <?php else: ?>
                    <div class="no-image"><i class="fas fa-image"></i></div>
                <?php endif; ?>
                <div class="card-body">
                    <small class="text-muted"><?php echo e($p['category_name']); ?></small>
                    <h6 class="fw-bold mt-1" style="font-size:0.9rem;"><?php echo e($p['name']); ?></h6>
                    <div class="price"><?php echo formatRupiah($p['price']); ?></div>
                    <small class="badge bg-<?php echo $p['stock'] > 10 ? 'success' : ($p['stock'] > 0 ? 'warning text-dark' : 'danger'); ?>">
                        Stok: <?php echo $p['stock']; ?>
                    </small>
                    <div class="d-flex gap-2 mt-2">
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">Detail</a>
                        <?php if ($p['stock'] > 0 && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'buyer'): ?>
                        <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn btn-sm btn-green flex-grow-1"><i class="fas fa-cart-plus"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
