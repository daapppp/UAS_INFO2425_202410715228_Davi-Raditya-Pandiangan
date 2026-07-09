<?php
// =============================================
// FreshMart Online - Admin Manage Users
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('admin');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();

// Handle verifikasi seller
if (isset($_POST['verify_seller'])) {
    $userId = intval($_POST['user_id'] ?? 0);
    $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE id = ? AND role = 'seller'");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() > 0) {
        sendNotification($userId, 'Akun Diverifikasi', 'Selamat! Akun penjual Anda telah diverifikasi. Anda sudah bisa berjualan.', 'system');
        redirect($baseUrl . '/src/views/admin/users.php', 'Penjual berhasil diverifikasi!', 'success');
    }
}

// Handle hapus user
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId !== 1) { // Tidak bisa hapu admin utama
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$delId]);
        redirect($baseUrl . '/src/views/admin/users.php', 'User berhasil dihapus!', 'success');
    }
}

// Filter
$roleFilter = $_GET['role'] ?? '';

$where = "role != 'admin'";
$params = [];
if (!empty($roleFilter) && in_array($roleFilter, ['seller', 'buyer'])) {
    $where .= " AND role = ?";
    $params[] = $roleFilter;
}

$stmt = $db->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3 class="fw-bold"><i class="fas fa-users"></i> Kelola User</h3>
    </div>

    <!-- Filter -->
    <div class="mb-3">
        <a href="users.php" class="btn <?php echo empty($roleFilter) ? 'btn-green' : 'btn-outline-secondary'; ?> btn-sm">Semua</a>
        <a href="users.php?role=seller" class="btn <?php echo $roleFilter === 'seller' ? 'btn-green' : 'btn-outline-secondary'; ?> btn-sm">Penjual</a>
        <a href="users.php?role=buyer" class="btn <?php echo $roleFilter === 'buyer' ? 'btn-green' : 'btn-outline-secondary'; ?> btn-sm">Pembeli</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-green text-white">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Role</th>
                            <th>Verifikasi</th>
                            <th>Tgl Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td class="fw-bold"><?php echo e($u['name']); ?></td>
                            <td><?php echo e($u['email']); ?></td>
                            <td><?php echo e($u['phone'] ?? '-'); ?></td>
                            <td><span class="badge <?php echo $u['role'] === 'seller' ? 'bg-success' : 'bg-primary'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                            <td>
                                <?php if ($u['role'] === 'seller'): ?>
                                    <?php if ($u['is_verified']): ?>
                                        <span class="badge bg-success">Terverifikasi</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Belum</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['role'] === 'seller' && !$u['is_verified']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" name="verify_seller" class="btn btn-sm btn-green">Verifikasi</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($u['id'] !== 1): ?>
                                <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
