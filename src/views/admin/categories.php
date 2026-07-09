<?php
// =============================================
// FreshMart Online - Admin Manage Categories
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('admin');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$delId]);
    redirect($baseUrl . '/src/views/admin/categories.php', 'Kategori berhasil dihapus!', 'success');
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '📦');
    $description = trim($_POST['description'] ?? '');
    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO categories (name, icon, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $icon, $description]);
        redirect($baseUrl . '/src/views/admin/categories.php', 'Kategori berhasil ditambahkan!', 'success');
    }
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '📦');
    $description = trim($_POST['description'] ?? '');
    if (!empty($name) && $id > 0) {
        $stmt = $db->prepare("UPDATE categories SET name = ?, icon = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $icon, $description, $id]);
        redirect($baseUrl . '/src/views/admin/categories.php', 'Kategori berhasil diperbarui!', 'success');
    }
}

$categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name")->fetchAll();
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-tags"></i> Kelola Kategori</h3>

    <div class="row g-4">
        <!-- Form Tambah -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Tambah Kategori</h5>
                    <form method="POST">
                        <input type="hidden" name="add" value="1">
                        <div class="mb-2">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Icon (Emoji)</label>
                            <input type="text" name="icon" class="form-control" value="📦">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-green w-100"><i class="fas fa-plus"></i> Tambah</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Kategori -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="bg-green text-white">
                            <tr>
                                <th>Icon</th>
                                <th>Nama</th>
                                <th>Produk</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                            <tr>
                                <td style="font-size:1.5rem;"><?php echo e($c['icon']); ?></td>
                                <td class="fw-bold"><?php echo e($c['name']); ?></td>
                                <td><?php echo $c['product_count']; ?></td>
                                <td class="text-muted"><?php echo e(substr($c['description'] ?? '', 0, 50)); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $c['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals -->
<?php foreach ($categories as $c): ?>
<div class="modal fade" id="editModal<?php echo $c['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit" value="1">
                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                    <div class="mb-2">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($c['name']); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" class="form-control" value="<?php echo e($c['icon']); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"><?php echo e($c['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-green">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
