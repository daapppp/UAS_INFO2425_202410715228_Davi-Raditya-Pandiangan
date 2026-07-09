<?php
// =============================================
// FreshMart Online - Seller Products (CRUD List)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('seller');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$sellerId = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = $db->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$delId, $sellerId]);
    $product = $stmt->fetch();
    if ($product) {
        // Hapus gambar
        if ($product['image'] && file_exists(__DIR__ . '/../../uploads/products/' . $product['image'])) {
            unlink(__DIR__ . '/../../uploads/products/' . $product['image']);
        }
        $stmt = $db->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$delId, $sellerId]);
        redirect($baseUrl . '/src/views/seller/products.php', 'Produk berhasil dihapus!', 'success');
    }
}

// Ambil produk seller
$stmt = $db->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$sellerId]);
$products = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3 class="fw-bold"><i class="fas fa-box"></i> Kelola Produk</h3>
        <a href="add_product.php" class="btn btn-green fw-bold"><i class="fas fa-plus"></i> Tambah Produk</a>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">Belum ada produk. Klik "Tambah Produk" untuk mulai.</div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <?php if ($p['image']): ?>
                                    <img src="<?php echo $baseUrl; ?>/src/uploads/products/<?php echo e($p['image']); ?>" style="width:50px;height:50px;object-fit:cover;" class="rounded">
                                <?php else: ?>
                                    <div class="no-image d-inline-flex" style="width:50px;height:50px;font-size:1rem;"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?php echo e($p['name']); ?></td>
                            <td><?php echo e($p['category_name']); ?></td>
                            <td class="fw-bold"><?php echo formatRupiah($p['price']); ?></td>
                            <td>
                                <span class="badge <?php echo $p['stock'] > 10 ? 'bg-success' : ($p['stock'] > 0 ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                    <?php echo $p['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $p['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $p['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
