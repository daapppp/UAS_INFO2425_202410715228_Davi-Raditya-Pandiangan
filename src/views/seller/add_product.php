<?php
// =============================================
// FreshMart Online - Seller Add Product
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('seller');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $weight_kg = floatval($_POST['weight_kg'] ?? 0);

    if (empty($name) || $category_id <= 0 || $price <= 0 || $stock < 0) {
        $error = 'Nama, kategori, harga, dan stok wajib diisi!';
    } else {
        // Upload gambar
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $image = 'product_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../uploads/products/' . $image);
            }
        }

        $stmt = $db->prepare("INSERT INTO products (seller_id, category_id, name, description, price, stock, weight_kg, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $category_id, $name, $description, $price, $stock, $weight_kg, $image]);

        redirect($baseUrl . '/src/views/seller/products.php', 'Produk berhasil ditambahkan!', 'success');
    }
}
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-plus-circle"></i> Tambah Produk Baru</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" min="0" step="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berat (kg)</label>
                        <input type="number" name="weight_kg" class="form-control" min="0" step="0.1" value="0.5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar Produk</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'imgPreview')">
                        <div class="form-text">Format: JPG, PNG, GIF</div>
                        <img id="imgPreview" class="mt-2 rounded" style="max-width:200px; display:none;">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Deskripsi produk..."></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-green fw-bold"><i class="fas fa-save"></i> Simpan Produk</button>
                        <a href="products.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
