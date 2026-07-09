<?php
// =============================================
// FreshMart Online - Notifications (Buyer)
// =============================================
$baseUrl = '/freshmart';
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn()) {
    redirect($baseUrl . '/src/views/public/login.php');
}

// Tandai notifikasi sebagai dibaca
$readId = intval($_GET['read'] ?? 0);
if ($readId > 0) {
    $db = getDBConnection();
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$readId, $_SESSION['user_id']]);
}

// Juga tandai semua sebagai dibaca
if (isset($_GET['mark_all'])) {
    $db = getDBConnection();
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

require_once __DIR__ . '/../partials/header.php';

$db = getDBConnection();
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3 class="fw-bold"><i class="fas fa-bell"></i> Notifikasi</h3>
        <a href="?mark_all=1" class="btn btn-sm btn-outline-success">Tandai Semua Dibaca</a>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">Tidak ada notifikasi.</div>
    <?php else: foreach ($notifications as $n): ?>
    <div class="card mb-2 border-start border-3 <?php echo $n['is_read'] ? 'border-secondary' : 'border-success'; ?>">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <strong><?php echo e($n['title']); ?></strong>
                <small class="text-muted"><?php echo date('d M Y H:i', strtotime($n['created_at'])); ?></small>
            </div>
            <p class="mb-0 mt-1"><?php echo e($n['message']); ?></p>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
