<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.href = "?page=login";</script>';
    exit;
}

$user_uid = $_SESSION['user']['uid'];
$favorites = getUserFavorites($user_uid, 20);
$user = $_SESSION['user'];

// Get stats for display
$stats = [
    'total' => $favorites->num_rows,
    'easy' => 0,
    'medium' => 0,
    'hard' => 0,
    'total_time' => 0
];

if ($favorites->num_rows > 0) {
    $favorites->data_seek(0); // Reset pointer
    while ($recipe = $favorites->fetch_assoc()) {
        switch(strtolower($recipe['tingkat_kesulitan'])) {
            case 'mudah': $stats['easy']++; break;
            case 'sedang': $stats['medium']++; break;
            case 'sulit': $stats['hard']++; break;
        }
        $stats['total_time'] += ($recipe['waktu'] ?? 0);
    }
    $favorites->data_seek(0); // Reset pointer again for display
}
?>

<!-- Favorites Page -->
<section class="favorites-page">
    <div class="container">
        <!-- Hero Header -->
        <div class="favorites-hero">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-heart"></i> Resep Favorit Saya
                </h1>
                <p class="hero-subtitle">
                    Koleksi resep spesial yang telah Anda simpan untuk dimasak nanti
                </p>
            </div>
            
            <div class="user-profile">
                <div class="profile-avatar">
                    <img src="<?= htmlspecialchars($user['photoURL'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['displayName'] ?? 'User') . '&background=ff6b6b&color=fff') ?>" 
                         alt="<?= htmlspecialchars($user['displayName'] ?? 'User') ?>">
                </div>
                <div class="profile-info">
                    <h3><?= htmlspecialchars($user['displayName'] ?? 'User') ?></h3>
                    <p class="profile-email">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email'] ?? '') ?>
                    </p>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <i class="fas fa-heart"></i>
                            <span id="favoriteCount"><?= $stats['total'] ?></span> Favorit
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-calendar-alt"></i>
                            Bergabung <?= date('M Y', strtotime($user['created_at'] ?? '-1 month')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-smile"></i>
                </div>
                <div class="stat-content">
                    <h3 id="easyCount"><?= $stats['easy'] ?></h3>
                    <p>Mudah</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-meh"></i>
                </div>
                <div class="stat-content">
                    <h3 id="mediumCount"><?= $stats['medium'] ?></h3>
                    <p>Sedang</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="stat-content">
                    <h3 id="hardCount"><?= $stats['hard'] ?></h3>
                    <p>Sulit</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3 id="totalTime"><?= floor($stats['total_time'] / 60) ?>j <?= $stats['total_time'] % 60 ?>m</h3>
                    <p>Total Waktu</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="filters-section">
            <div class="favorites-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchFavorites" placeholder="Cari resep favorit...">
                <button class="clear-search" id="clearSearch">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="filter-controls">
                <select id="categoryFilter" class="filter-select">
                    <option value="all">Semua Kategori</option>
                    <option value="Makanan Utama">Makanan Utama</option>
                    <option value="Sup">Sup</option>
                    <option value="Sayuran">Sayuran</option>
                    <option value="Snack">Makanan Ringan</option>
                    <option value="Salad">Salad</option>
                    <option value="Dessert">Makanan Penutup</option>
                    <option value="Minuman">Minuman</option>
                </select>
                
                <select id="difficultyFilter" class="filter-select">
                    <option value="all">Semua Tingkat</option>
                    <option value="mudah">Mudah</option>
                    <option value="sedang">Sedang</option>
                    <option value="sulit">Sulit</option>
                </select>
                
                <select id="timeFilter" class="filter-select">
                    <option value="all">Semua Waktu</option>
                    <option value="30">≤ 30 menit</option>
                    <option value="60">≤ 60 menit</option>
                    <option value="90">≤ 90 menit</option>
                </select>
                
                <button class="btn btn-outline" id="sortBtn">
                    <i class="fas fa-sort-amount-down"></i> Urutkan
                </button>
            </div>
        </div>

        <!-- Favorites Grid -->
        <div class="favorites-container">
            <?php if ($favorites->num_rows > 0): ?>
                <div class="favorites-grid" id="favoritesGrid">
                    <?php while ($recipe = $favorites->fetch_assoc()): 
                        $difficultyClass = '';
                        switch(strtolower($recipe['tingkat_kesulitan'])) {
                            case 'mudah': $difficultyClass = 'difficulty-easy'; break;
                            case 'sedang': $difficultyClass = 'difficulty-medium'; break;
                            case 'sulit': $difficultyClass = 'difficulty-hard'; break;
                        }
                        
                        $total_time = ($recipe['waktu'] ?? 0);
                        $image_path = getRecipeImage($recipe, 'thumb');
                        $added_date = strtotime($recipe['favorited_at'] ?? $recipe['created_at'] ?? 'now');
                    ?>
                        <div class="recipe-card" 
                             data-id="<?= $recipe['id'] ?>"
                             data-category="<?= htmlspecialchars($recipe['kategori'] ?? '') ?>"
                             data-difficulty="<?= htmlspecialchars($recipe['tingkat_kesulitan']) ?>"
                             data-time="<?= $total_time ?>"
                             data-added="<?= $added_date ?>">
                            
                            <a class="recipe-image-container" href="?page=resep&id=<?= $recipe['id'] ?>&from=favorit">
                                <img src="<?= htmlspecialchars($image_path) ?>" 
                                     alt="<?= htmlspecialchars($recipe['judul']) ?>"
                                     loading="lazy">
                                <div class="recipe-overlay">
                                    <span class="recipe-time"><?= $total_time ?> mnt</span>
                                </div>
                                <div class="quick-view-overlay">
                                    <button class="quick-view-btn" data-recipe-id="<?= $recipe['id'] ?>">
                                        <i class="fas fa-eye"></i> Lihat Cepat
                                    </button>
                                </div>
                            </a>
                            
                            <div class="recipe-content">
                                <h3 class="recipe-title">
                                    <a href="?page=resep&id=<?= $recipe['id'] ?>&from=favorit">
                                        <?= htmlspecialchars($recipe['judul']) ?>
                                    </a>
                                </h3>
                                
                                <p class="recipe-description">
                                    <?= htmlspecialchars(substr($recipe['deskripsi'] ?? '', 0, 100)) ?>
                                    <?= strlen($recipe['deskripsi'] ?? '') > 100 ? '...' : '' ?>
                                </p>
                                
                                <div class="recipe-meta">
                                    <div class="meta-info">
                                        <div class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?= $total_time ?> mnt</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-user-friends"></i>
                                            <span><?= $recipe['porsi'] ?> porsi</span>
                                        </div>
                                    </div>
                                    
                                    <span class="difficulty-badge <?= $difficultyClass ?>">
                                        <?= ucfirst($recipe['tingkat_kesulitan']) ?>
                                    </span>
                                </div>
                                
                                <div class="recipe-actions">
                                <a href="?page=resep&id=<?= $recipe['id'] ?>&from=favorit" class="view-btn">
                                        <i class="fas fa-eye"></i> Lihat Resep
                                    </a>
                                    <button class="favorite-btn active" data-recipe-id="<?= $recipe['id'] ?>">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Empty Search State -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <div class="empty-icon">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3>Tidak Ada Resep yang Cocok</h3>
                    <p>Coba ubah filter pencarian Anda</p>
                    <button class="btn btn-primary" id="clearAllFilters">
                        <i class="fas fa-times"></i> Hapus Semua Filter
                    </button>
                </div>
            <?php else: ?>
                <!-- No Favorites State -->
                <div class="no-favorites">
                    <div class="empty-icon">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3>Belum Ada Resep Favorit</h3>
                    <p>Mulai jelajahi resep dan tambahkan ke favorit Anda</p>
                    <div class="action-buttons">
                        <a href="?page=home" class="btn btn-primary">
                            <i class="fas fa-search"></i> Jelajahi Resep
                        </a>
                        <a href="?page=resep" class="btn btn-outline">
                            <i class="fas fa-fire"></i> Resep Populer
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Share Button -->
        <?php if ($favorites->num_rows > 0): ?>
            <div class="share-section">
                <button class="btn btn-outline" id="shareListBtn">
                    <i class="fas fa-share-alt"></i> Bagikan Daftar Favorit
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Quick View Modal -->
<div class="modal" id="quickViewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Resep</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body" id="quickViewContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal" id="shareModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bagikan Daftar Favorit</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="share-options">
                <button class="share-option" data-platform="copy">
                    <i class="fas fa-copy"></i>
                    <span>Salin Link</span>
                </button>
                <button class="share-option" data-platform="whatsapp">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </button>
                <button class="share-option" data-platform="facebook">
                    <i class="fab fa-facebook"></i>
                    <span>Facebook</span>
                </button>
                <button class="share-option" data-platform="twitter">
                    <i class="fab fa-twitter"></i>
                    <span>Twitter</span>
                </button>
            </div>
            <div class="share-link">
                <input type="text" id="shareUrl" readonly 
                       value="<?= htmlspecialchars("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>">
                <button class="btn btn-primary" id="copyLink">
                    <i class="fas fa-copy"></i> Salin
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   FAVORITES PAGE STYLES - Consistent with Homepage
   ============================================ */

.favorites-page {
    padding: 2rem 0 4rem;
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.05) 0%, rgba(255, 107, 107, 0.02) 100%);
    min-height: 100vh;
}

/* Hero Header */
.favorites-hero {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 2rem;
}

.hero-content .hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 15px;
}

.hero-content .hero-title i {
    color: #ff6b6b;
}

.hero-subtitle {
    font-size: 1.1rem;
    color: #666;
    max-width: 500px;
    line-height: 1.6;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: rgba(255, 107, 107, 0.05);
    padding: 1.5rem;
    border-radius: 15px;
    border: 1px solid rgba(255, 107, 107, 0.1);
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid rgba(255, 107, 107, 0.2);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info h3 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.3rem;
}

.profile-email {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-stats {
    display: flex;
    gap: 1.5rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.stat-item i {
    color: #ff6b6b;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(255, 107, 107, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
    color: white;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
    color: #333;
}

.stat-content p {
    color: #666;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.favorites-search {
    position: relative;
    margin-bottom: 1.5rem;
}

.favorites-search i {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 1.1rem;
}

.favorites-search input {
    width: 100%;
    padding: 15px 20px 15px 50px;
    border: 2px solid #eee;
    border-radius: 12px;
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.favorites-search input:focus {
    outline: none;
    border-color: #ff6b6b;
    background: white;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.clear-search {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    display: none;
    font-size: 1rem;
}

.favorites-search input:not(:placeholder-shown) + .clear-search {
    display: block;
}

.filter-controls {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-select {
    padding: 12px 20px;
    border: 2px solid #eee;
    border-radius: 10px;
    background: white;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 180px;
}

.filter-select:focus {
    outline: none;
    border-color: #ff6b6b;
}

.favorites-page .btn {
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
}

.favorites-page .btn-primary {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
    color: white;
}

.favorites-page .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);
}

.favorites-page .btn-outline {
    background: white;
    border: 2px solid #ff6b6b;
    color: #ff6b6b;
}

.favorites-page .btn-outline:hover {
    background: #ff6b6b;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.2);
}

/* Favorites Grid */
.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.recipe-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.recipe-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(255, 107, 107, 0.15);
}


.recipe-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
    display: block;
    text-decoration: none;
}

.recipe-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.recipe-card:hover .recipe-image-container img {
    transform: scale(1.05);
}

.recipe-overlay {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 15px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
    color: #fff;
    font-weight: 600;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.quick-view-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    display: none;
}

.recipe-card:hover .recipe-overlay {
    opacity: 1;
}

.quick-view-btn {
    background: white;
    color: #ff6b6b;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.quick-view-btn:hover {
    background: #ff6b6b;
    color: white;
    transform: translateY(-2px);
}

.recipe-content {
    padding: 1.5rem;
}

.recipe-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 0.8rem;
    line-height: 1.3;
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
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 1.2rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.recipe-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.2rem;
}

.meta-info {
    display: flex;
    gap: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
    font-size: 0.9rem;
}

.meta-item i {
    color: #ff6b6b;
    font-size: 0.9rem;
}

.difficulty-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.difficulty-easy {
    background: rgba(46, 213, 115, 0.1);
    color: #2ed573;
}

.difficulty-medium {
    background: rgba(255, 165, 2, 0.1);
    color: #ffa502;
}

.difficulty-hard {
    background: rgba(255, 107, 107, 0.1);
    color: #ff6b6b;
}

.recipe-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.view-btn {
    flex: 1;
    padding: 10px 15px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 0.95rem;
}

.view-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
}

.favorite-btn {
    width: 40px;
    height: 40px;
    background: none;
    border: none;
    color: #ff6b6b;
    font-size: 1.2rem;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.favorite-btn.active {
    color: #ff6b6b;
}

.favorite-btn:hover {
    background: rgba(255, 107, 107, 0.1);
}

/* Empty States */
.empty-state,
.no-favorites {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    margin: 2rem 0;
}

.empty-icon {
    width: 100px;
    height: 100px;
    background: rgba(255, 107, 107, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 3rem;
    color: #ff6b6b;
}

.empty-state h3,
.no-favorites h3 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: #333;
}

.empty-state p,
.no-favorites p {
    color: #666;
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
    font-size: 1.1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Share Section */
.share-section {
    text-align: center;
    padding: 2rem 0;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal.active {
    display: flex;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.5rem;
    color: #333;
    margin: 0;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.8rem;
    color: #999;
    cursor: pointer;
    padding: 5px;
    line-height: 1;
    transition: color 0.3s ease;
}

.close-modal:hover {
    color: #ff6b6b;
}

.modal-body {
    padding: 1.5rem;
}

/* Share Modal */
.share-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.share-option {
    background: white;
    border: 2px solid #eee;
    border-radius: 12px;
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.share-option:hover {
    border-color: #ff6b6b;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(255, 107, 107, 0.1);
}

.share-option i {
    font-size: 2rem;
    margin-bottom: 5px;
}

.share-option[data-platform="copy"] i { color: #ff6b6b; }
.share-option[data-platform="whatsapp"] i { color: #25D366; }
.share-option[data-platform="facebook"] i { color: #1877F2; }
.share-option[data-platform="twitter"] i { color: #1DA1F2; }

.share-option span {
    font-weight: 600;
    color: #333;
}

.share-link {
    display: flex;
    gap: 10px;
}

.share-link input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #eee;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.9rem;
    color: #333;
    background: #f8f9fa;
}

.share-link input:focus {
    outline: none;
    border-color: #ff6b6b;
}

/* Quick View Modal Content */
.quick-view-content {
    line-height: 1.6;
    color: #333;
}

.quick-view-content h4 {
    color: #333;
    margin: 1.5rem 0 1rem;
    font-size: 1.2rem;
    border-bottom: 2px solid #eee;
    padding-bottom: 0.5rem;
}

.quick-view-content ul {
    padding-left: 1.5rem;
    margin-bottom: 1.5rem;
}

.quick-view-content li {
    margin-bottom: 0.5rem;
    color: #555;
}

/* Responsive Design */
@media (max-width: 992px) {
    .favorites-hero {
        flex-direction: column;
        text-align: center;
    }
    
    .user-profile {
        flex-direction: column;
        text-align: center;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    
    .profile-stats {
        justify-content: center;
    }
    
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-select {
        min-width: 100%;
    }
}

@media (max-width: 768px) {
    .favorites-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .share-options {
        grid-template-columns: 1fr;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .modal-content {
        margin: 1rem;
    }
}

@media (max-width: 576px) {
    .favorites-hero,
    .filters-section {
        padding: 1.5rem;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .share-link {
        flex-direction: column;
    }
    
    .recipe-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .recipe-actions {
        width: 100%;
    }
    
    .filter-controls .btn {
        width: 100%;
    }
}

/* Toast Notification */
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #ff6b6b;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Loading Animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spinner {
    animation: spin 1s linear infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const favoritesGrid = document.getElementById('favoritesGrid');
    const emptyState = document.getElementById('emptyState');
    const noFavorites = document.querySelector('.no-favorites');
    const searchInput = document.getElementById('searchFavorites');
    const clearSearch = document.getElementById('clearSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const timeFilter = document.getElementById('timeFilter');
    const sortBtn = document.getElementById('sortBtn');
    const shareListBtn = document.getElementById('shareListBtn');
    const clearAllFilters = document.getElementById('clearAllFilters');
    const quickViewBtns = document.querySelectorAll('.quick-view-btn');
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    const quickViewModal = document.getElementById('quickViewModal');
    const shareModal = document.getElementById('shareModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const shareOptions = document.querySelectorAll('.share-option');
    const copyLinkBtn = document.getElementById('copyLink');
    const shareUrlInput = document.getElementById('shareUrl');
    
    // Stats elements
    const favoriteCount = document.getElementById('favoriteCount');
    const easyCount = document.getElementById('easyCount');
    const mediumCount = document.getElementById('mediumCount');
    const hardCount = document.getElementById('hardCount');
    const totalTime = document.getElementById('totalTime');
    
    // Filter functionality
    const normalizeText = (value) => {
        return String(value || '')
            .toLowerCase()
            .replace(/&amp;/g, '&')
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();
    };
    
    searchInput?.addEventListener('input', filterFavorites);
    
    clearSearch?.addEventListener('click', function() {
        if (searchInput) searchInput.value = '';
        filterFavorites();
        this.style.display = 'none';
    });
    
    categoryFilter?.addEventListener('change', filterFavorites);
    difficultyFilter?.addEventListener('change', filterFavorites);
    timeFilter?.addEventListener('change', filterFavorites);
    
    if (clearAllFilters) {
        clearAllFilters.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            categoryFilter.value = 'all';
            difficultyFilter.value = 'all';
            timeFilter.value = 'all';
            filterFavorites();
            if (clearSearch) clearSearch.style.display = 'none';
        });
    }
    
    // Sort functionality
    sortBtn?.addEventListener('click', function() {
        const sortOptions = [
            { text: 'Terbaru Ditambahkan', value: 'newest' },
            { text: 'Terlama Ditambahkan', value: 'oldest' },
            { text: 'Nama A-Z', value: 'name-asc' },
            { text: 'Nama Z-A', value: 'name-desc' },
            { text: 'Waktu Terpendek', value: 'time-asc' },
            { text: 'Waktu Terpanjang', value: 'time-desc' }
        ];
        
        // Create sort menu
        const sortMenu = document.createElement('div');
        sortMenu.className = 'sort-menu';
        sortMenu.style.cssText = `
            position: absolute;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 10px 0;
            min-width: 200px;
            z-index: 100;
            border: 1px solid #eee;
        `;
        
        sortOptions.forEach(option => {
            const item = document.createElement('button');
            item.className = 'sort-item';
            item.textContent = option.text;
            item.style.cssText = `
                display: block;
                width: 100%;
                padding: 12px 20px;
                border: none;
                background: none;
                text-align: left;
                cursor: pointer;
                font-family: 'Poppins', sans-serif;
                font-size: 0.95rem;
                color: #333;
                transition: all 0.2s ease;
            `;
            
            item.addEventListener('mouseenter', () => {
                item.style.background = 'rgba(255, 107, 107, 0.05)';
                item.style.color = '#ff6b6b';
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.background = 'none';
                item.style.color = '#333';
            });
            
            item.addEventListener('click', () => {
                sortFavorites(option.value);
                sortMenu.remove();
            });
            
            sortMenu.appendChild(item);
        });
        
        // Position and show menu
        const rect = sortBtn.getBoundingClientRect();
        sortMenu.style.top = rect.bottom + 5 + 'px';
        sortMenu.style.left = rect.left + 'px';
        
        document.body.appendChild(sortMenu);
        
        // Close menu when clicking outside
        function closeMenu(e) {
            if (!sortMenu.contains(e.target) && e.target !== sortBtn) {
                sortMenu.remove();
                document.removeEventListener('click', closeMenu);
            }
        }
        
        setTimeout(() => document.addEventListener('click', closeMenu), 10);
    });
    
    // Share functionality
    shareListBtn?.addEventListener('click', function() {
        shareModal.classList.add('active');
    });
    
    // Quick view
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const recipeId = this.dataset.recipeId;
            
            // Show loading
            const modalContent = document.getElementById('quickViewContent');
            modalContent.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin fa-2x" style="color: #ff6b6b;"></i><p>Memuat resep...</p></div>';
            quickViewModal.classList.add('active');
            
            try {
                const response = await fetch(`api/get_recipe.php?id=${recipeId}`);
                const recipe = await response.json();
                
                if (recipe) {
                    modalContent.innerHTML = `
                        <div class="quick-view-content">
                            <h4>${escapeHTML(recipe.judul)}</h4>
                            <p>${escapeHTML(recipe.deskripsi || '')}</p>
                            
                            <h4><i class="fas fa-list"></i> Bahan-bahan</h4>
                            <div style="white-space: pre-line; background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                                ${escapeHTML(recipe.bahan || '')}
                            </div>
                            
                            <h4><i class="fas fa-utensils"></i> Cara Masak</h4>
                            <div style="white-space: pre-line; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                                ${escapeHTML(recipe.cara_masak || '')}
                            </div>
                            
                            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #eee;">
                                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <span class="difficulty-badge ${getDifficultyClass(recipe.tingkat_kesulitan)}">
                                        ${recipe.tingkat_kesulitan}
                                    </span>
                                    <span style="color: #666;">
                                        <i class="fas fa-clock"></i> ${recipe.waktu} menit
                                    </span>
                                    <span style="color: #666;">
                                        <i class="fas fa-user-friends"></i> ${recipe.porsi} porsi
                                    </span>
                                    <span style="color: #666;">
                                        <i class="fas fa-fire"></i> ${recipe.kategori}
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    modalContent.innerHTML = '<p style="text-align: center; color: #666;">Gagal memuat resep</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                modalContent.innerHTML = '<p style="text-align: center; color: #666;">Terjadi kesalahan</p>';
            }
        });
    });
    
    // Favorite toggle in grid
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            
            const recipeId = this.dataset.recipeId;
            const recipeCard = this.closest('.recipe-card');
            
            if (this.classList.contains('active')) {
                // Already favorited, remove it
                // Remove without confirmation popup
                
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                try {
                    const response = await fetch('api/toggle_favorite.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ recipeId: recipeId, action: 'remove' })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Remove card from grid
                        recipeCard.style.transform = 'scale(0.95)';
                        recipeCard.style.opacity = '0';
                        setTimeout(() => {
                            recipeCard.remove();
                            updateStats();
                            checkEmptyState();
                        }, 300);
                        
                        // Toast removed per request
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.innerHTML = '<i class="fas fa-heart"></i>';
                }
            }
        });
    });
    
    // Share options
    shareOptions.forEach(option => {
        option.addEventListener('click', function() {
            const platform = this.dataset.platform;
            const url = shareUrlInput.value;
            const title = 'Daftar Resep Favorit Saya - Resep Nusantara';
            
            switch(platform) {
                case 'copy':
                    copyToClipboard(url);
                    showToast('Link berhasil disalin!');
                    break;
                case 'whatsapp':
                    window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
                    break;
                case 'facebook':
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
                    break;
                case 'twitter':
                    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, '_blank');
                    break;
            }
        });
    });
    
    // Copy link button
    copyLinkBtn?.addEventListener('click', function() {
        copyToClipboard(shareUrlInput.value);
        showToast('Link berhasil disalin!');
    });
    
    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').classList.remove('active');
        });
    });
    
    // Close modals on background click
    [quickViewModal, shareModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
    
    // Functions
    function filterFavorites() {
        const searchTerm = normalizeText(searchInput?.value);
        const category = normalizeText(categoryFilter?.value);
        const difficulty = normalizeText(difficultyFilter?.value);
        const maxTime = timeFilter?.value ?? 'all';
        
        if (!favoritesGrid) return;
        
        const cards = favoritesGrid.querySelectorAll('.recipe-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const title = normalizeText(card.querySelector('.recipe-title')?.textContent);
            const desc = normalizeText(card.querySelector('.recipe-description')?.textContent);
            const cardCategory = normalizeText(card.dataset.category);
            const cardDifficulty = normalizeText(card.dataset.difficulty);
            const rawTime = parseInt(card.dataset.time, 10);
            const cardTime = Number.isNaN(rawTime) ? 0 : rawTime;
            
            const matchesSearch = !searchTerm ||
                title.includes(searchTerm) ||
                desc.includes(searchTerm);
            
            const matchesCategory = category === 'all' || cardCategory === category;
            const matchesDifficulty = difficulty === 'all' || cardDifficulty === difficulty;
            const matchesTime = maxTime === 'all' || cardTime <= parseInt(maxTime);
            
            if (matchesSearch && matchesCategory && matchesDifficulty && matchesTime) {
                card.style.display = 'block';
                visibleCount++;
                
                // Add animation
                card.style.animation = 'none';
                requestAnimationFrame(() => {
                    card.style.animation = 'fadeIn 0.5s ease';
                });
            } else {
                card.style.display = 'none';
            }
        });
        
        checkEmptyState(visibleCount);
        if (clearSearch) clearSearch.style.display = searchTerm ? 'block' : 'none';
    }
    
    function sortFavorites(sortBy) {
        if (!favoritesGrid) return;
        
        const container = favoritesGrid;
        const cards = Array.from(container.querySelectorAll('.recipe-card'));
        
        cards.sort((a, b) => {
            switch(sortBy) {
                case 'newest':
                    return b.dataset.added - a.dataset.added;
                case 'oldest':
                    return a.dataset.added - b.dataset.added;
                case 'name-asc':
                    return a.querySelector('.recipe-title').textContent.localeCompare(
                        b.querySelector('.recipe-title').textContent
                    );
                case 'name-desc':
                    return b.querySelector('.recipe-title').textContent.localeCompare(
                        a.querySelector('.recipe-title').textContent
                    );
                case 'time-asc':
                    return parseInt(a.dataset.time) - parseInt(b.dataset.time);
                case 'time-desc':
                    return parseInt(b.dataset.time) - parseInt(a.dataset.time);
                default:
                    return 0;
            }
        });
        
        // Reappend cards in new order
        cards.forEach(card => container.appendChild(card));
        
        // Update button text
        const sortText = {
            'newest': 'Terbaru',
            'oldest': 'Terlama',
            'name-asc': 'A-Z',
            'name-desc': 'Z-A',
            'time-asc': 'Waktu ↑',
            'time-desc': 'Waktu ↓'
        };
        
        if (sortBtn) {
            sortBtn.innerHTML = `<i class="fas fa-sort-amount-down"></i> ${sortText[sortBy]}`;
        }
    }
    
    function updateStats() {
        if (!favoritesGrid) return;
        
        const cards = favoritesGrid.querySelectorAll('.recipe-card');
        let easy = 0, medium = 0, hard = 0, total = 0;
        
        cards.forEach(card => {
            const difficulty = card.dataset.difficulty;
            const time = parseInt(card.dataset.time);
            
            switch(difficulty) {
                case 'mudah': easy++; break;
                case 'sedang': medium++; break;
                case 'sulit': hard++; break;
            }
            
            total += time;
        });
        
        // Update stats
        if (easyCount) animateNumber(easyCount, easy);
        if (mediumCount) animateNumber(mediumCount, medium);
        if (hardCount) animateNumber(hardCount, hard);
        if (favoriteCount) favoriteCount.textContent = cards.length;
        
        // Format total time
        if (totalTime) {
            const hours = Math.floor(total / 60);
            const minutes = total % 60;
            let timeText = '';
            
            if (hours > 0) {
                timeText += `${hours}j `;
            }
            if (minutes > 0 || hours === 0) {
                timeText += `${minutes}m`;
            }
            
            totalTime.textContent = timeText.trim();
        }
    }
    
    function checkEmptyState(visibleCount = null) {
        if (!favoritesGrid) return;
        
        const cards = favoritesGrid.querySelectorAll('.recipe-card');
        const totalCards = cards.length;
        const visibleCards = visibleCount !== null ? visibleCount : Array.from(cards).filter(c => c.style.display !== 'none').length;
        
        if (emptyState) {
            emptyState.style.display = (totalCards > 0 && visibleCards === 0) ? 'block' : 'none';
        }
    }
    
    function animateNumber(element, target) {
        if (!element) return;
        
        const current = parseInt(element.textContent) || 0;
        if (current === target) return;
        
        const diff = target - current;
        const steps = 20;
        const increment = diff / steps;
        let currentStep = 0;
        
        const timer = setInterval(() => {
            currentStep++;
            const value = Math.round(current + (increment * currentStep));
            element.textContent = value;
            
            if (currentStep >= steps) {
                element.textContent = target;
                clearInterval(timer);
            }
        }, 20);
    }
    
    function getDifficultyClass(level) {
        switch(level.toLowerCase()) {
            case 'mudah': return 'difficulty-easy';
            case 'sedang': return 'difficulty-medium';
            case 'sulit': return 'difficulty-hard';
            default: return 'difficulty-medium';
        }
    }
    
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            console.log('Copied to clipboard');
        }).catch(err => {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        });
    }
    
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    function escapeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
    // Initialize
    checkEmptyState();
    
    // Listen for favorite changes from other pages
    document.addEventListener('favorite:changed', function(e) {
        const { recipeId, favorited } = e.detail;
        
        // Update favorite button if it exists
        document.querySelectorAll(`.favorite-btn[data-recipe-id="${recipeId}"]`).forEach(btn => {
            if (favorited) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-heart"></i>';
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i class="far fa-heart"></i>';
            }
        });
        
        // If a card was unfavorited and we're on favorites page, remove it
        if (!favorited && favoritesGrid) {
            const card = favoritesGrid.querySelector(`[data-id="${recipeId}"]`);
            if (card) {
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    updateStats();
                    checkEmptyState();
                }, 300);
            }
        }
    });
    
    // Handle escape key for modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
});
</script>
