<?php
// =============================================
// FreshMart Online - Header Template
// =============================================

$current_page = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['user_role'] ?? '';
$userName = $_SESSION['user_name'] ?? '';
$userId = $_SESSION['user_id'] ?? null;
$unreadNotif = $userId ? countUnreadNotifications($userId) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshMart Online - Produk Segar Berkualitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo $baseUrl; ?>/src/assets/css/style.css" rel="stylesheet">
    <script>
        const baseUrl = '<?php echo $baseUrl; ?>';
    </script>
</head>
<body>

<!-- Navbar -->
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-green" href="<?php echo $baseUrl; ?>/src/views/public/index.php" style="font-size: 1.4rem;">
            <i class="fas fa-leaf text-success me-1"></i> FreshMart
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Search Bar -->
            <?php if ($current_page !== 'login.php' && $current_page !== 'register.php'): ?>
            <form class="d-flex mx-auto" style="max-width:400px;" action="<?php echo $baseUrl; ?>/src/views/public/search.php" method="GET">
                <input class="form-control me-2" type="search" name="q" placeholder="Cari produk segar..." aria-label="Search" required style="border: 1px solid #ced4da;">
                <button class="btn btn-green text-white" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <?php endif; ?>
            <!-- Menu -->
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link text-green fw-bold me-2" href="<?php echo $baseUrl; ?>/src/views/public/index.php"><i class="fas fa-home"></i> Beranda</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <!-- Keranjang (hanya buyer) -->
                    <?php if ($userRole === 'buyer'): ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-green btn-sm px-3 text-white fw-bold me-2 mt-1 mt-lg-0" href="<?php echo $baseUrl; ?>/src/views/buyer/cart.php">
                            <i class="fas fa-shopping-cart"></i> Keranjang
                            <?php
                            $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                            if ($cartCount > 0) echo '<span class="badge bg-danger ms-1">' . $cartCount . '</span>';
                            ?>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Notifikasi -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle btn btn-green btn-sm px-3 text-white fw-bold me-2 mt-1 mt-lg-0" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if ($unreadNotif > 0): ?>
                                <span class="badge bg-warning text-dark ms-1"><?php echo $unreadNotif; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width:300px;">
                            <?php
                            $db = getDBConnection();
                            $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                            $stmt->execute([$userId]);
                            $notifs = $stmt->fetchAll();
                            if (empty($notifs)): ?>
                                <li><span class="dropdown-item text-muted">Tidak ada notifikasi</span></li>
                            <?php else: foreach ($notifs as $n): ?>
                                <li>
                                    <a class="dropdown-item <?php echo $n['is_read'] ? '' : 'fw-bold'; ?>" href="<?php echo $baseUrl; ?>/src/views/buyer/notifications.php?read=<?php echo $n['id']; ?>">
                                        <small class="text-muted d-block"><?php echo e($n['title']); ?></small>
                                        <?php echo e(substr($n['message'], 0, 50)); ?>...
                                    </a>
                                </li>
                            <?php endforeach; endif; ?>
                        </ul>
                    </li>

                    <!-- Menu berdasarkan role -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle btn btn-green btn-sm px-3 text-white fw-bold mt-1 mt-lg-0" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo e($userName); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($userRole === 'buyer'): ?>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/buyer/dashboard.php">Dashboard Pembeli</a></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/buyer/orders.php">Pesanan Saya</a></li>
                            <?php elseif ($userRole === 'seller'): ?>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/seller/dashboard.php">Dashboard Penjual</a></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/seller/products.php">Kelola Produk</a></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/seller/orders.php">Pesanan Masuk</a></li>
                            <?php elseif ($userRole === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/admin/dashboard.php">Dashboard Admin</a></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/admin/users.php">Kelola User</a></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/admin/categories.php">Kelola Kategori</a></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/src/views/admin/orders.php">Kelola Pesanan</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $baseUrl; ?>/src/views/public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-success btn-sm px-3 fw-bold me-2 mt-1 mt-lg-0" href="<?php echo $baseUrl; ?>/src/views/public/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-green btn-sm px-3 fw-bold text-white mt-1 mt-lg-0" href="<?php echo $baseUrl; ?>/src/views/public/register.php">
                            <i class="fas fa-user-plus me-1"></i> Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Message -->
<?php echo getFlashMessage(); ?>
