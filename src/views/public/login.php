<?php
// =============================================
// FreshMart Online - Login Page
// =============================================
$baseUrl = '/freshmart'; // Sesuaikan path
require_once __DIR__ . '/../../config/database.php';

// Jika sudah login, redirect sesuai role
if (isLoggedIn()) {
    switch ($_SESSION['user_role']) {
        case 'admin': redirect($baseUrl . '/src/views/admin/dashboard.php'); break;
        case 'seller': redirect($baseUrl . '/src/views/seller/dashboard.php'); break;
        case 'buyer': redirect($baseUrl . '/src/views/buyer/dashboard.php'); break;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi!';
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Cek apakah penjual sudah diverifikasi
            if ($user['role'] === 'seller' && !$user['is_verified']) {
                $error = 'Akun penjual Anda belum diverifikasi oleh admin. Silakan hubungi admin.';
            } else {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Remember me
                if (isset($_POST['remember'])) {
                    setcookie('remember_email', $email, time() + 86400 * 30, '/');
                }

                // Kirim notifikasi selamat datang
                sendNotification($user['id'], 'Login Berhasil', 'Anda berhasil login ke FreshMart Online.', 'system');

                // Redirect sesuai role
                switch ($user['role']) {
                    case 'admin': redirect($baseUrl . '/src/views/admin/dashboard.php'); break;
                    case 'seller': redirect($baseUrl . '/src/views/seller/dashboard.php'); break;
                    case 'buyer': redirect($baseUrl . '/src/views/buyer/dashboard.php'); break;
                }
            }
        } else {
            $error = 'Email atau password salah!';
        }
    }
}

$rememberEmail = $_COOKIE['remember_email'] ?? '';
?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="text-center fw-bold text-green mb-2">
                        <i class="fas fa-leaf"></i> FreshMart
                    </h3>
                    <p class="text-center text-muted mb-4">Masuk ke akun Anda</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo e($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="<?php echo e($rememberEmail); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        <button type="submit" class="btn btn-green w-100 fw-bold">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </button>
                    </form>

                    <hr>
                    <p class="text-center mb-0">
                        Belum punya akun? <a href="register.php" class="text-green fw-bold">Daftar sekarang</a>
                    </p>

                    <div class="mt-3 text-center">
                        <small class="text-muted">Akun Demo:</small><br>
                        <small class="text-muted">Admin: admin@freshmart.com</small><br>
                        <small class="text-muted">Penjual: seller1@freshmart.com</small><br>
                        <small class="text-muted">Pembeli: buyer1@freshmart.com</small><br>
                        <small class="text-muted">Password: password</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
