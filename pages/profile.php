<?php
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user']) || !$_SESSION['user']) {
    header('Location: ?page=login');
    exit;
}

$user = $_SESSION['user'];
?>

<section class="profile-section">
    <div class="container">
        <div class="profile-header">
            <img src="<?= htmlspecialchars($user['photoURL'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['displayName'] ?? 'User')) ?>" 
                 alt="<?= htmlspecialchars($user['displayName'] ?? 'User') ?>" class="profile-avatar">
            <div class="profile-info">
                <h1><?= htmlspecialchars($user['displayName'] ?? 'User') ?></h1>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        
        <div class="profile-content">
            <h2>Informasi Akun</h2>
            <div class="info-box">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($user['displayName'] ?? '-') ?></p>
            </div>
        </div>
    </div>
</section>