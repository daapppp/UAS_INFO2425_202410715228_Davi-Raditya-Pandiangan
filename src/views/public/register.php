<?php
// =============================================
// FreshMart Online - Register Page
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';

if (isLoggedIn()) {
    redirect($baseUrl . '/src/views/public/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'buyer';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (!in_array($role, ['buyer', 'seller'])) {
        $error = 'Role tidak valid!';
    } else {
        $db = getDBConnection();
        // Cek email sudah ada
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password (bcrypt)
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert user baru
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, address, password, role, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $address, $hashedPassword, $role, $role === 'buyer' ? 1 : 0]);

            // Jika penjual, kirim notifikasi ke admin
            if ($role === 'seller') {
                // Kirim notifikasi ke admin (id = 1)
                sendNotification(1, 'Penjual Baru', "Penjual baru '$name' mendaftar dan menunggu verifikasi.", 'system');
                redirect($baseUrl . '/src/views/public/login.php', 'Pendaftaran berhasil! Akun penjual Anda menunggu verifikasi admin.', 'info');
            }

            redirect($baseUrl . '/src/views/public/login.php', 'Pendaftaran berhasil! Silakan login.', 'success');
        }
    }
}
?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="text-center fw-bold text-green mb-2">
                        <i class="fas fa-leaf"></i> Daftar Akun FreshMart
                    </h3>
                    <p class="text-center text-muted mb-4">Isi data di bawah untuk membuat akun baru</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo e($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo e($_POST['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Daftar Sebagai <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="roleBuyer" value="buyer" checked>
                                <label class="form-check-label" for="roleBuyer">Pembeli</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="roleSeller" value="seller">
                                <label class="form-check-label" for="roleSeller">Penjual (perlu verifikasi admin)</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-green w-100 fw-bold">
                            <i class="fas fa-user-plus"></i> Daftar
                        </button>
                    </form>

                    <hr>
                    <p class="text-center mb-0">
                        Sudah punya akun? <a href="login.php" class="text-green fw-bold">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
