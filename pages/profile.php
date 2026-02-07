<?php
require_once __DIR__ . '/../includes/database.php';

// Redirect jika belum login
if (!isset($_SESSION['user']) || !$_SESSION['user']) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: ?page=login');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['uid'];

// Get user stats from database
$userStats = getUserStats($user_id);
$favoriteRecipes = getUserFavoriteRecipes($user_id, 6);
$userRecipes = getUserRecipes($user_id, 6);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullname = trim($_POST['fullname'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        if (updateUserProfile($user_id, $fullname, $bio)) {
            $_SESSION['success_message'] = 'Profil berhasil diperbarui!';
            
            // Refresh user data
            $user = getUserById($user_id);
            $_SESSION['user'] = $user;
            
            header('Location: ?page=profile');
            exit;
        } else {
            $error_message = 'Gagal memperbarui profil. Silakan coba lagi.';
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($new_password !== $confirm_password) {
            $error_message = 'Password baru tidak cocok.';
        } elseif (changeUserPassword($user_id, $current_password, $new_password)) {
            $_SESSION['success_message'] = 'Password berhasil diubah!';
            header('Location: ?page=profile');
            exit;
        } else {
            $error_message = 'Password saat ini salah atau terjadi kesalahan.';
        }
    }
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadUserAvatar($user_id, $_FILES['avatar']);
        
        if ($upload_result['success']) {
            $_SESSION['success_message'] = 'Foto profil berhasil diperbarui!';
            
            // Update user session
            $user['photoURL'] = $upload_result['path'];
            $_SESSION['user'] = $user;
            
            header('Location: ?page=profile');
            exit;
        } else {
            $error_message = $upload_result['error'];
        }
    }
}
?>

<!-- Profile Section -->
<section class="profile-section">
    <div class="container">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar-container">
                <div class="avatar-wrapper">
                    <img src="<?= htmlspecialchars($user['photoURL'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['displayName'] ?? 'User') . '&background=ff6b6b&color=fff&size=200') ?>" 
                         alt="<?= htmlspecialchars($user['displayName'] ?? 'User') ?>" 
                         class="profile-avatar"
                         id="profileAvatar">
                    
                    <!-- Avatar Upload -->
                    <form method="POST" enctype="multipart/form-data" class="avatar-upload-form">
                        <label for="avatarUpload" class="avatar-upload-label" title="Ubah foto profil">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" 
                               id="avatarUpload" 
                               name="avatar" 
                               accept="image/*" 
                               style="display: none;"
                               onchange="this.form.submit()">
                    </form>
                </div>
                
                <div class="profile-info">
                    <h1 class="profile-name"><?= htmlspecialchars($user['displayName'] ?? 'User') ?></h1>
                    <p class="profile-email">
                        <i class="fas fa-envelope"></i>
                        <?= htmlspecialchars($user['email']) ?>
                    </p>

                    <p class="profile-fav-count">
                        <i class="fas fa-heart"></i>
                        <?= $userStats['total_favorites'] ?? 0 ?> Favorit
                    </p>
                    
                    <?php if (!empty($user['bio'])): ?>
                        <p class="profile-bio">
                            <i class="fas fa-quote-left"></i>
                            <?= htmlspecialchars($user['bio']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="profile-join-date">
                        <i class="fas fa-calendar-alt"></i>
                        Bergabung <?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div class="profile-content">
            <div class="tabs">
                <button class="tab-btn active" data-tab="profile">Profil</button>
                <button class="tab-btn" data-tab="favorites">Favorit</button>
                <button class="tab-btn" data-tab="security">Keamanan</button>
            </div>
            
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane active" id="profile">
                    <div class="profile-form">
                        <h3><i class="fas fa-user-edit"></i> Edit Profil</h3>
                        
                        <form method="POST" class="form">
                            <div class="form-group">
                                <label for="fullname">
                                    <i class="fas fa-user"></i> Nama Lengkap
                                </label>
                                <input type="text" 
                                       id="fullname" 
                                       name="fullname" 
                                       value="<?= htmlspecialchars($user['displayName'] ?? '') ?>"
                                       placeholder="Masukkan nama lengkap Anda"
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" 
                                       id="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>"
                                       disabled
                                       class="form-control disabled">
                                <small class="form-text">Email tidak dapat diubah</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio">
                                    <i class="fas fa-quote-right"></i> Bio
                                </label>
                                <textarea id="bio" 
                                          name="bio" 
                                          rows="3"
                                          placeholder="Ceritakan sedikit tentang diri Anda..."
                                          class="form-control"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Favorites Tab -->
                <div class="tab-pane" id="favorites">
                    <div class="section-header">
                        <h3><i class="fas fa-heart"></i> Resep Favorit</h3>
                        <p>Resep yang telah Anda tandai sebagai favorit</p>
                    </div>
                    
                    <?php if (!empty($favoriteRecipes)): ?>
                        <div class="favorites-grid">
                            <?php foreach ($favoriteRecipes as $recipe): ?>
                                <div class="favorite-card" data-recipe-id="<?= $recipe['id'] ?>" data-category="<?= htmlspecialchars(strtolower($recipe['kategori'] ?? '')) ?>" data-difficulty="<?= htmlspecialchars($recipe['tingkat_kesulitan'] ?? '') ?>" data-time="<?= intval($recipe['waktu'] ?? 0) ?>">
                                    
                                    <button class="remove-favorite" data-recipe-id="<?= $recipe['id'] ?>" title="Hapus dari favorit">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    
                                    <div class="favorite-image">
                                        <img src="<?= htmlspecialchars($recipe['image_url'] ?? 'assets/images/default-recipe.jpg') ?>" 
                                             alt="<?= htmlspecialchars($recipe['judul']) ?>"
                                             onerror="this.onerror=null;this.src='assets/images/default-recipe.jpg';">
                                        <div class="favorite-overlay">
                                            <a href="?page=resep&id=<?= $recipe['id'] ?>" class="quick-view">
                                                <i class="fas fa-eye"></i> Lihat Resep
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="favorite-info">
                                        <div class="recipe-header">
                                            <h5 class="recipe-title"><?= htmlspecialchars($recipe['judul']) ?></h5>
                                        </div>
                                        
                                        <p class="recipe-description">
                                            <?= htmlspecialchars(substr($recipe['deskripsi'], 0, 100)) ?>...
                                        </p>
                                        
                                        <div class="recipe-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-clock"></i>
                                                <span><?= intval($recipe['waktu'] ?? 0) ?> menit</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-user-friends"></i>
                                                <span><?= $recipe['porsi'] ?> porsi</span>
                                            </div>
                                        </div>
                                        
                                        <div class="difficulty-time">
                                            <span class="difficulty-badge <?= getDifficultyClass($recipe['tingkat_kesulitan']) ?>">
                                                <?= ucfirst($recipe['tingkat_kesulitan']) ?>
                                            </span>
                                            <span class="time-badge">
                                                <i class="fas fa-hourglass-end"></i> <?= intval($recipe['waktu'] ?? 0) ?> menit
                                            </span>
                                        </div>
                                        
                                        <div class="recipe-actions">
                                            <a href="?page=resep&id=<?= $recipe['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Lihat Resep
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($userStats['total_favorites'] > 6): ?>
                            <div class="text-center mt-3">
                                <a href="?page=favorites" class="btn btn-outline">
                                    <i class="fas fa-list"></i> Lihat Semua Favorit
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="far fa-heart"></i>
                            </div>
                            <h4>Belum ada resep favorit</h4>
                            <p>Mulai jelajahi resep dan tambahkan ke favorit untuk melihatnya di sini</p>
                            <a href="?page=resep" class="btn btn-primary">
                                <i class="fas fa-search"></i> Jelajahi Resep
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Security Tab -->
                <div class="tab-pane" id="security">
                    <div class="profile-form">
                        <h3><i class="fas fa-lock"></i> Keamanan Akun</h3>
                        
                        <!-- Change Password Form -->
                        <form method="POST" class="form">
                            <div class="form-group">
                                <label for="current_password">
                                    <i class="fas fa-key"></i> Password Saat Ini
                                </label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password"
                                       placeholder="Masukkan password saat ini"
                                       class="form-control"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">
                                    <i class="fas fa-key"></i> Password Baru
                                </label>
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password"
                                       placeholder="Masukkan password baru"
                                       class="form-control"
                                       required>
                                <small class="form-text">Minimal 8 karakter, mengandung huruf dan angka</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">
                                    <i class="fas fa-key"></i> Konfirmasi Password Baru
                                </label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password"
                                       placeholder="Ulangi password baru"
                                       class="form-control"
                                       required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Ubah Password
                            </button>
                        </form>
                        
                        <hr class="divider">
                        
                        <!-- Account Actions -->
                        <div class="account-actions">
                            <h4><i class="fas fa-cog"></i> Tindakan Akun</h4>
                            
                            <div class="action-buttons">
                                <button class="btn btn-outline" onclick="exportUserData()">
                                    <i class="fas fa-download"></i> Ekspor Data Akun
                                </button>
                                
                                <button class="btn btn-outline btn-danger" onclick="showDeleteAccountModal()">
                                    <i class="fas fa-trash"></i> Hapus Akun
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delete Account Modal -->
<div class="modal" id="deleteAccountModal">
    <div class="modal-content">
        <h3><i class="fas fa-exclamation-triangle"></i> Hapus Akun Permanen</h3>
        <div class="alert alert-danger">
            <strong>Peringatan:</strong> Tindakan ini akan menghapus semua data akun Anda secara permanen, termasuk semua resep, favorit, dan komentar.
        </div>
        
        <div class="form-group">
            <label for="deleteConfirm">
                Ketik "DELETE" untuk mengonfirmasi penghapusan akun:
            </label>
            <input type="text" 
                   id="deleteConfirm" 
                   class="form-control"
                   placeholder="DELETE">
        </div>
        
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal()">Batal</button>
            <button class="btn btn-danger" onclick="deleteAccount()" id="deleteAccountBtn" disabled>
                Hapus Akun Permanen
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Update active tab button
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding tab pane
            tabPanes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === tabId) {
                    pane.classList.add('active');
                }
            });
        });
    });
    
    // Preview avatar before upload
    const avatarUpload = document.getElementById('avatarUpload');
    const profileAvatar = document.getElementById('profileAvatar');
    
    avatarUpload?.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                profileAvatar.src = e.target.result;
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Password validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (newPassword && confirmPassword) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    newPassword?.addEventListener('input', validatePassword);
    confirmPassword?.addEventListener('input', validatePassword);
    
    // Delete account confirmation
    const deleteConfirm = document.getElementById('deleteConfirm');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    
    if (deleteConfirm) {
        deleteConfirm.addEventListener('input', function() {
            deleteAccountBtn.disabled = this.value !== 'DELETE';
        });
    }
    
    // Initialize tooltips
    tippy('[title]', {
        placement: 'top',
        animation: 'scale',
        duration: [200, 150],
    });
});

function showDeleteAccountModal() {
    showModal('deleteAccountModal');
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
    document.body.style.overflow = 'auto';
}

async function deleteAccount() {
    try {
        const response = await fetch('api/delete_account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = '?page=logout';
        } else {
            showNotification('Gagal menghapus akun', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
}

async function exportUserData() {
    try {
        const response = await fetch('api/export_user_data.php');
        const result = await response.json();
        
        if (result.success && result.data) {
            // Create and download JSON file
            const dataStr = JSON.stringify(result.data, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
            
            const exportFileDefaultName = `user-data-${new Date().toISOString().split('T')[0]}.json`;
            
            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();
            
            showNotification('Data berhasil diekspor', 'success');
        } else {
            showNotification('Gagal mengekspor data', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
}

function toggleFavorite(button, recipeId) {
    const heartIcon = button.querySelector('i');
    const isActive = button.classList.contains('active');
    
    // Update UI immediately for better UX
    button.classList.toggle('active');
    heartIcon.classList.toggle('far');
    heartIcon.classList.toggle('fas');
    
    // Send to server
    fetch('api/toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            recipeId: recipeId,
            action: isActive ? 'remove' : 'add'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            // Revert UI if failed
            button.classList.toggle('active');
            heartIcon.classList.toggle('far');
            heartIcon.classList.toggle('fas');
            showNotification('Gagal memperbarui favorit', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert UI
        button.classList.toggle('active');
        heartIcon.classList.toggle('far');
        heartIcon.classList.toggle('fas');
        showNotification('Terjadi kesalahan', 'error');
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function getDifficultyClass(level) {
    switch(level.toLowerCase()) {
        case 'mudah': return 'difficulty-easy';
        case 'sedang': return 'difficulty-medium';
        case 'sulit': return 'difficulty-hard';
        default: return 'difficulty-medium';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal();
    }
});

// Handle escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Remove favorite functionality for profile page
document.querySelectorAll('.remove-favorite').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        const recipeId = this.dataset.recipeId;
        const card = this.closest('.favorite-card');
        
        if (!confirm('Hapus resep dari favorit?')) return;
        
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        this.disabled = true;
        
        try {
            const response = await fetch('api/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipeId: recipeId,
                    action: 'remove'
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Animate out and remove
                card.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    card.remove();
                    showNotification('Dihapus dari favorit', 'success');
                }, 300);
            } else {
                this.innerHTML = '<i class="fas fa-times"></i>';
                this.disabled = false;
                showNotification('Gagal menghapus favorit', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.innerHTML = '<i class="fas fa-times"></i>';
            this.disabled = false;
            showNotification('Terjadi kesalahan', 'error');
        }
    });
});

// Listen for favorite changes from other pages
document.addEventListener('favorite:changed', function(e) {
    const { recipeId, favorited } = e.detail;
    if (!favorited) {
        // Remove from profile if unfavorited elsewhere
        const card = document.querySelector(`.favorite-card[data-recipe-id="${recipeId}"]`);
        if (card) {
            card.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => card.remove(), 300);
        }
    }
});
</script>

<style>
@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Profile Section Styles */
.profile-section {
    padding: 2rem 0;
}

.profile-header {
    margin-bottom: 3rem;
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
    border-radius: 15px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
}

.profile-avatar-container {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.avatar-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
}

.profile-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid rgba(255, 255, 255, 0.3);
    transition: transform 0.3s ease;
}

.profile-avatar:hover {
    transform: scale(1.05);
}

.avatar-upload-label {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: #ff6b6b;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid white;
}

.avatar-upload-label:hover {
    background: #ff5252;
    transform: scale(1.1);
}

.profile-info {
    flex: 1;
    min-width: 300px;
}

.profile-name {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.profile-email {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 1rem;
}

.profile-email i,
.profile-bio i,
.profile-join-date i {
    margin-right: 0.5rem;
    width: 20px;
}

.profile-bio {
    font-style: italic;
    margin: 1rem 0;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.profile-join-date {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Stats Section */
.profile-stats {
    margin-bottom: 3rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    max-width: 500px;
    margin: 0 auto;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 2px solid #ffebee;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.2);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #ff6b6b;
    margin: 0;
    line-height: 1;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

/* Tabs */
.tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 1rem 2rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 1rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 8px 8px 0 0;
}

.tab-btn:hover {
    color: #ff6b6b;
    background: rgba(255, 107, 107, 0.1);
}

.tab-btn.active {
    color: #ff6b6b;
    border-bottom-color: #ff6b6b;
    background: rgba(255, 107, 107, 0.1);
}

.tab-content {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Forms */
.profile-form h3 {
    margin-bottom: 1.5rem;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form {
    max-width: 600px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #555;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #ff6b6b;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.form-control.disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: #888;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(255, 107, 107, 0.4);
}

.btn-outline {
    background: white;
    border: 2px solid #ff6b6b;
    color: #ff6b6b;
}

.btn-outline:hover {
    background: #ff6b6b;
    color: white;
}

.btn-danger {
    background: #ff6b6b;
    color: white;
}

.btn-danger:hover {
    background: #ff5252;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

/* Favorites Grid */
.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.favorite-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid #f0f0f0;
}

.favorite-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(255, 107, 107, 0.15);
}

.remove-favorite {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    color: #ff6b6b;
    z-index: 10;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.favorite-card:hover .remove-favorite {
    opacity: 1;
    transform: scale(1);
}

.remove-favorite:hover {
    background: #ff6b6b;
    color: white;
}

.favorite-image {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.favorite-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.favorite-card:hover .favorite-image img {
    transform: scale(1.08);
}

.favorite-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.favorite-card:hover .favorite-overlay {
    opacity: 1;
}

.quick-view {
    background: white;
    color: #ff6b6b;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.quick-view:hover {
    background: #ff6b6b;
    color: white;
}

.favorite-info {
    padding: 1.5rem;
}

.recipe-header {
    margin-bottom: 0.75rem;
}

.favorite-card .recipe-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #333;
    margin: 0;
    line-height: 1.3;
}

.favorite-card .recipe-description {
    color: #666;
    font-size: 0.9rem;
    margin: 0.75rem 0 1rem 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.favorite-card .recipe-meta {
    display: flex;
    gap: 1.2rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
    font-size: 0.85rem;
}

.meta-item i {
    color: #ff6b6b;
    font-size: 0.9rem;
}

.difficulty-time {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #f0f0f0;
    margin-bottom: 1.2rem;
}

.time-badge {
    background: rgba(78, 205, 196, 0.1);
    color: #4ecdc4;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.favorite-card .recipe-actions {
    display: flex;
    gap: 0.75rem;
}

.favorite-card .recipe-actions .btn {
    flex: 1;
    justify-content: center;
}

/* Recipes Grid */
.recipes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.recipe-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #f0f0f0;
}

.recipe-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.15);
}

.recipe-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.recipe-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.recipe-card:hover .recipe-image {
    transform: scale(1.05);
}

.recipe-overlay {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 107, 107, 0.9);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.recipe-content {
    padding: 1.5rem;
}

.recipe-title {
    margin: 0 0 0.75rem 0;
    font-size: 1.25rem;
}

.recipe-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.recipe-title a:hover {
    color: #ff6b6b;
}

.recipe-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.recipe-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.recipe-info {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
    color: #666;
}

.recipe-info-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.difficulty-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.difficulty-easy {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.difficulty-medium {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.difficulty-hard {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.recipe-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.favorite-btn {
    background: none;
    border: none;
    color: #ff6b6b;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.favorite-btn:hover {
    background: rgba(255, 107, 107, 0.1);
}

.favorite-btn.active {
    color: #ff6b6b;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    font-size: 4rem;
    color: #ffcccc;
    margin-bottom: 1rem;
}

.empty-state h4 {
    color: #666;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.empty-state p {
    color: #888;
    margin-bottom: 1.5rem;
    font-size: 1rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Account Actions */
.account-actions {
    margin-top: 2rem;
}

.account-actions h4 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: #555;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Divider */
.divider {
    border: none;
    border-top: 1px solid #e0e0e0;
    margin: 2rem 0;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    animation: slideUp 0.3s ease;
    border-top: 5px solid #ff6b6b;
}

@keyframes slideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal h3 {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #333;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

/* Notifications */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 1001;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    min-width: 300px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.notification.show {
    opacity: 1;
    transform: translateX(0);
}

.notification.success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.notification.error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #ff6b6b;
}

.notification.info {
    background: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

/* Alerts */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    color: #155724;
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.alert-error {
    background: rgba(255, 107, 107, 0.1);
    color: #721c24;
    border: 1px solid rgba(255, 107, 107, 0.2);
}

.alert-danger {
    background: rgba(255, 107, 107, 0.1);
    color: #721c24;
    border: 1px solid rgba(255, 107, 107, 0.2);
}

.alert i {
    font-size: 1.2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-avatar-container {
        flex-direction: column;
        text-align: center;
    }
    
    .avatar-wrapper {
        margin: 0 auto;
    }
    
    .profile-name {
        font-size: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        max-width: 300px;
    }
    
    .tab-btn {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        flex: 1;
        min-width: 120px;
    }
    
    .recipes-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        padding: 1.5rem;
    }
    
    .notification {
        min-width: auto;
        left: 20px;
        right: 20px;
        transform: translateY(-100%);
    }
    
    .notification.show {
        transform: translateY(0);
    }
}

@media (max-width: 480px) {
    .profile-name {
        font-size: 1.75rem;
    }
    
    .tab-btn {
        min-width: 100%;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .profile-header {
        padding: 1.5rem;
    }
    
    .avatar-wrapper {
        width: 120px;
        height: 120px;
    }
}

/* Animation for stat cards */
@keyframes statPulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.stat-card {
    animation: statPulse 2s infinite;
}

/* Hover effect for recipe cards */
.recipe-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.1) 0%, rgba(255, 107, 107, 0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 12px;
    pointer-events: none;
}

.recipe-card:hover::after {
    opacity: 1;
}

/* Custom scrollbar for tab content */
.tab-content {
    max-height: 600px;
    overflow-y: auto;
}

.tab-content::-webkit-scrollbar {
    width: 8px;
}

.tab-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.tab-content::-webkit-scrollbar-thumb {
    background: #ff6b6b;
    border-radius: 4px;
}

.tab-content::-webkit-scrollbar-thumb:hover {
    background: #ff5252;
}
</style>
