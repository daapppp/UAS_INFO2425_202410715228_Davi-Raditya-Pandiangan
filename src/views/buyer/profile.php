<?php
// =============================================
// FreshMart Online - Buyer Profile
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';
checkRole('buyer');
require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $address, $userId]);
    $_SESSION['user_name'] = $name;
    $success = 'Profil berhasil diperbarui!';
}
?>

<div class="container py-4">
    <h3 class="fw-bold mb-3"><i class="fas fa-user"></i> Profil Saya</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($_POST['name'] ?? $user['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo e($user['email']); ?>" disabled>
                            <div class="form-text">Email tidak dapat diubah.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo e($_POST['phone'] ?? $user['phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo e($_POST['address'] ?? $user['address']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-green fw-bold"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-user-circle text-success" style="font-size:5rem;"></i>
                    <h5 class="mt-2 fw-bold"><?php echo e($user['name']); ?></h5>
                    <p class="text-muted"><?php echo e($user['email']); ?></p>
                    <span class="badge bg-green">Pembeli</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
