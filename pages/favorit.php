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
?>
<section class="favorites-section">
    <div class="container">
        <!-- Header Section -->
        <div class="favorites-header">
            <div class="header-content">
                <h1><i class="fas fa-heart"></i> Resep Favorit Saya</h1>
                <p class="subtitle">Kumpulan resep yang telah Anda simpan untuk dimasak nanti</p>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <img src="<?= $_SESSION['user']['photoURL'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['displayName'] ?? 'User') ?>" 
                             alt="<?= htmlspecialchars($_SESSION['user']['displayName'] ?? 'User') ?>">
                    </div>
                    <div class="user-details">
                        <h3><?= htmlspecialchars($_SESSION['user']['displayName'] ?? 'User') ?></h3>
                        <p class="email"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></p>
                        <div class="stats">
                            <span class="stat-item">
                                <i class="fas fa-heart"></i>
                                <span id="favoriteCount"><?= $favorites->num_rows ?></span> Resep
                            </span>
                            <span class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span id="joinedDate">Bergabung <?= date('M Y', strtotime('-1 month')) ?></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="header-actions">
                <button class="btn btn-outline" id="sortBtn">
                    <i class="fas fa-sort-amount-down"></i> Urutkan
                </button>
                <button class="btn btn-primary" id="shareListBtn">
                    <i class="fas fa-share-alt"></i> Bagikan
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon easy">
                    <i class="fas fa-smile"></i>
                </div>
                <div class="stat-content">
                    <h3 id="easyCount">0</h3>
                    <p>Resep Mudah</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon medium">
                    <i class="fas fa-meh"></i>
                </div>
                <div class="stat-content">
                    <h3 id="mediumCount">0</h3>
                    <p>Resep Sedang</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon hard">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="stat-content">
                    <h3 id="hardCount">0</h3>
                    <p>Resep Sulit</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3 id="totalTime">0</h3>
                    <p>Total Waktu Masak</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filters-section">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchFavorites" placeholder="Cari resep favorit...">
                <button class="clear-search" id="clearSearch">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="filter-options">
                <div class="filter-group">
                    <label for="categoryFilter"><i class="fas fa-filter"></i> Kategori:</label>
                    <select id="categoryFilter" class="filter-select">
                        <option value="all">Semua Kategori</option>
                        <option value="Makanan Berat">Makanan Berat</option>
                        <option value="Makanan Ringan">Makanan Ringan</option>
                        <option value="Kue">Kue</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Sarapan">Sarapan</option>
                        <option value="Makanan Penutup">Makanan Penutup</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="difficultyFilter"><i class="fas fa-chart-line"></i> Tingkat:</label>
                    <select id="difficultyFilter" class="filter-select">
                        <option value="all">Semua Tingkat</option>
                        <option value="mudah">Mudah</option>
                        <option value="sedang">Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="timeFilter"><i class="fas fa-clock"></i> Waktu:</label>
                    <select id="timeFilter" class="filter-select">
                        <option value="all">Semua Waktu</option>
                        <option value="30">≤ 30 menit</option>
                        <option value="60">≤ 60 menit</option>
                        <option value="90">≤ 90 menit</option>
                    </select>
                </div>
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
                        $image_path = getRecipeImage($recipe);
                    ?>
                        <div class="favorite-card" 
                             data-id="<?= $recipe['id'] ?>"
                             data-category="<?= htmlspecialchars($recipe['kategori']) ?>"
                             data-difficulty="<?= htmlspecialchars($recipe['tingkat_kesulitan']) ?>"
                             data-time="<?= $total_time ?>"
                             data-added="<?= strtotime($recipe['created_at'] ?? 'now') ?>">
                            
                            <!-- Remove from favorites button -->
                            <button class="remove-favorite" data-recipe-id="<?= $recipe['id'] ?>">
                                <i class="fas fa-times"></i>
                            </button>
                            
                            <!-- Recipe Image -->
                            <div class="favorite-image">
                                <img src="<?= htmlspecialchars($image_path) ?>" 
                                     alt="<?= htmlspecialchars($recipe['judul']) ?>"
                                     loading="lazy">
                                <div class="favorite-overlay">
                                    <button class="quick-view" data-recipe-id="<?= $recipe['id'] ?>">
                                        <i class="fas fa-eye"></i> Lihat Cepat
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Recipe Info -->
                            <div class="favorite-info">
                                <div class="recipe-header">
                                    <h3 class="recipe-title"><?= htmlspecialchars($recipe['judul']) ?></h3>
                                    <span class="favorite-date">
                                        <i class="far fa-calendar"></i>
                                        <?= date('d M Y', strtotime($recipe['created_at'] ?? 'now')) ?>
                                    </span>
                                </div>
                                
                                <p class="recipe-description">
                                    <?= htmlspecialchars(substr($recipe['deskripsi'] ?? '', 0, 80)) ?>
                                    <?= strlen($recipe['deskripsi'] ?? '') > 80 ? '...' : '' ?>
                                </p>
                                
                                <div class="recipe-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?= $total_time ?> menit</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-user-friends"></i>
                                        <span><?= $recipe['porsi'] ?> porsi</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-fire"></i>
                                        <span><?= $recipe['kategori'] ?></span>
                                    </div>
                                </div>
                                
                                <div class="difficulty-time">
                                    <span class="difficulty-badge <?= $difficultyClass ?>">
                                        <?= ucfirst($recipe['tingkat_kesulitan']) ?>
                                    </span>
                                    <span class="time-badge">
                                        <i class="far fa-clock"></i> <?= $total_time ?>m
                                    </span>
                                </div>
                                
                                <div class="recipe-actions">
                                    <a href="?page=resep&id=<?= $recipe['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-utensils"></i> Masak Sekarang
                                    </a>
                                    <button class="btn btn-outline btn-sm add-to-cart" data-recipe-id="<?= $recipe['id'] ?>">
                                        <i class="fas fa-shopping-cart"></i> Bahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Empty State (hidden by default) -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <div class="empty-icon">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3>Tidak Ada Resep yang Cocok</h3>
                    <p>Coba ubah filter pencarian atau tambahkan resep baru ke favorit</p>
                    <a href="?page=home" class="btn btn-primary">
                        <i class="fas fa-search"></i> Jelajahi Resep
                    </a>
                </div>
                
                <!-- No Favorites State -->
                <div class="no-favorites" id="noFavorites" style="<?= $favorites->num_rows > 0 ? 'display: none;' : '' ?>">
                    <div class="empty-icon">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3>Belum Ada Resep Favorit</h3>
                    <p>Tambahkan resep favorit Anda untuk melihatnya di sini</p>
                    <div class="action-buttons">
                        <a href="?page=home" class="btn btn-primary">
                            <i class="fas fa-search"></i> Jelajahi Resep
                        </a>
                        <a href="?page=resep" class="btn btn-outline">
                            <i class="fas fa-fire"></i> Resep Populer
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- No Favorites State -->
                <div class="no-favorites">
                    <div class="empty-icon">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3>Belum Ada Resep Favorit</h3>
                    <p>Tambahkan resep favorit Anda untuk melihatnya di sini</p>
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
                        <input type="text" id="shareUrl" readonly value="<?= htmlspecialchars("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>">
                        <button class="btn btn-primary" id="copyLink">
                            <i class="fas fa-copy"></i> Salin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ============================================
   FAVORITES PAGE STYLES
   ============================================ */

.favorites-section {
    padding: 2rem 0 4rem;
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.05) 0%, rgba(78, 205, 196, 0.05) 100%);
    min-height: 100vh;
}

/* Header Section */
.favorites-header {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 2rem;
}

.header-content h1 {
    color: var(--primary);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-content h1 i {
    color: #ff6b6b;
    font-size: 2.2rem;
}

.subtitle {
    color: var(--gray);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--light-gray);
}

.user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--light);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-details h3 {
    font-size: 1.5rem;
    margin-bottom: 0.3rem;
    color: var(--dark);
}

.user-details .email {
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.stats {
    display: flex;
    gap: 2rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gray);
    font-size: 0.9rem;
}

.stat-item i {
    color: var(--primary);
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stat-icon.easy {
    background: rgba(46, 213, 115, 0.1);
    color: #2ed573;
}

.stat-icon.medium {
    background: rgba(255, 165, 2, 0.1);
    color: #ffa502;
}

.stat-icon.hard {
    background: rgba(255, 107, 107, 0.1);
    color: #ff6b6b;
}

.stat-icon.total {
    background: rgba(78, 205, 196, 0.1);
    color: #4ecdc4;
}

.stat-content h3 {
    font-size: 1.8rem;
    margin-bottom: 0.3rem;
    color: var(--dark);
}

.stat-content p {
    color: var(--gray);
    font-size: 0.9rem;
}

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
}

.search-box {
    position: relative;
    margin-bottom: 1.5rem;
}

.search-box i {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 1.1rem;
}

.search-box input {
    width: 100%;
    padding: 15px 20px 15px 50px;
    border: 2px solid var(--light-gray);
    border-radius: 12px;
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.clear-search {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray);
    cursor: pointer;
    display: none;
}

.search-box input:not(:placeholder-shown) + .clear-search {
    display: block;
}

.filter-options {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-group label {
    color: var(--dark);
    font-weight: 500;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-select {
    padding: 10px 15px;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    background: white;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    color: var(--dark);
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
}

/* Favorites Grid */
.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.favorite-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
    position: relative;
}

.favorite-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.remove-favorite {
    position: absolute;
    top: 15px;
    right: 15px;
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
    z-index: 2;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.favorite-card:hover .remove-favorite {
    opacity: 1;
    transform: scale(1);
}

.remove-favorite:hover {
    background: #ff6b6b;
    color: white;
    transform: scale(1.1) !important;
}

.favorite-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.favorite-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.favorite-card:hover .favorite-image img {
    transform: scale(1.05);
}

.favorite-overlay {
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
}

.favorite-card:hover .favorite-overlay {
    opacity: 1;
}

.quick-view {
    background: white;
    color: var(--primary);
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.quick-view:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

.favorite-info {
    padding: 1.5rem;
}

.recipe-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.recipe-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
    line-height: 1.3;
    flex: 1;
}

.favorite-date {
    font-size: 0.8rem;
    color: var(--gray);
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    margin-left: 10px;
}

.recipe-description {
    color: var(--gray);
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.recipe-meta {
    display: flex;
    gap: 1.2rem;
    margin-bottom: 1.2rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--gray);
    font-size: 0.85rem;
}

.meta-item i {
    color: var(--primary);
    font-size: 0.9rem;
}

.difficulty-time {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-top: 1.2rem;
    border-top: 1px solid var(--light-gray);
}

.difficulty-badge {
    padding: 6px 15px;
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

.recipe-actions {
    display: flex;
    gap: 10px;
}

.btn-sm {
    padding: 10px 18px;
    font-size: 0.9rem;
    flex: 1;
    justify-content: center;
}

/* Empty States */
.empty-state,
.no-favorites {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
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
    color: var(--dark);
}

.empty-state p,
.no-favorites p {
    color: var(--gray);
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
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
}

.modal-content {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalSlideUp 0.3s ease;
}

@keyframes modalSlideUp {
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
    border-bottom: 1px solid var(--light-gray);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.5rem;
    color: var(--dark);
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.8rem;
    color: var(--gray);
    cursor: pointer;
    padding: 5px;
    line-height: 1;
}

.close-modal:hover {
    color: var(--primary);
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
    border: 2px solid var(--light-gray);
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
    border-color: var(--primary);
    transform: translateY(-3px);
}

.share-option i {
    font-size: 2rem;
    margin-bottom: 5px;
}

.share-option[data-platform="copy"] i { color: #4ecdc4; }
.share-option[data-platform="whatsapp"] i { color: #25D366; }
.share-option[data-platform="facebook"] i { color: #1877F2; }
.share-option[data-platform="twitter"] i { color: #1DA1F2; }

.share-link {
    display: flex;
    gap: 10px;
}

.share-link input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.9rem;
}

/* Quick View Modal Content */
.quick-view-content {
    line-height: 1.6;
}

.quick-view-content h4 {
    color: var(--dark);
    margin: 1.5rem 0 1rem;
    font-size: 1.2rem;
    border-bottom: 2px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.quick-view-content ul {
    padding-left: 1.5rem;
    margin-bottom: 1.5rem;
}

.quick-view-content li {
    margin-bottom: 0.5rem;
    color: var(--gray);
}

/* Responsive Design */
@media (max-width: 992px) {
    .favorites-header {
        flex-direction: column;
        text-align: center;
    }
    
    .user-info {
        justify-content: center;
        flex-direction: column;
        text-align: center;
    }
    
    .stats {
        justify-content: center;
    }
    
    .filter-options {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-select {
        width: 100%;
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
    
    .header-content h1 {
        font-size: 2rem;
    }
    
    .recipe-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .favorite-date {
        margin-left: 0;
    }
}

@media (max-width: 576px) {
    .favorites-header,
    .filters-section,
    .favorite-card {
        padding: 1.5rem;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .recipe-actions {
        flex-direction: column;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .share-link {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const favoritesGrid = document.getElementById('favoritesGrid');
    const emptyState = document.getElementById('emptyState');
    const noFavorites = document.getElementById('noFavorites');
    const searchInput = document.getElementById('searchFavorites');
    const clearSearch = document.getElementById('clearSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const timeFilter = document.getElementById('timeFilter');
    const sortBtn = document.getElementById('sortBtn');
    const shareListBtn = document.getElementById('shareListBtn');
    const removeFavoriteBtns = document.querySelectorAll('.remove-favorite');
    const quickViewBtns = document.querySelectorAll('.quick-view');
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
    
    // Initialize stats
    updateStats();
    
    // Search functionality
    searchInput.addEventListener('input', filterFavorites);
    
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        filterFavorites();
        this.style.display = 'none';
    });
    
    // Filter functionality
    categoryFilter.addEventListener('change', filterFavorites);
    difficultyFilter.addEventListener('change', filterFavorites);
    timeFilter.addEventListener('change', filterFavorites);
    
    // Sort functionality
    sortBtn.addEventListener('click', function() {
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
            display: none;
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
                color: var(--dark);
                transition: all 0.2s ease;
            `;
            
            item.addEventListener('mouseenter', () => {
                item.style.background = 'rgba(255, 107, 107, 0.05)';
                item.style.color = 'var(--primary)';
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.background = 'none';
                item.style.color = 'var(--dark)';
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
        sortMenu.style.display = 'block';
        
        document.body.appendChild(sortMenu);
        
        // Close menu when clicking outside
        document.addEventListener('click', function closeMenu(e) {
            if (!sortMenu.contains(e.target) && e.target !== sortBtn) {
                sortMenu.remove();
                document.removeEventListener('click', closeMenu);
            }
        });
    });
    
    // Share functionality
    shareListBtn.addEventListener('click', function() {
        shareModal.classList.add('active');
    });
    
    // Remove from favorites
    removeFavoriteBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const recipeId = this.dataset.recipeId;
            const recipeCard = this.closest('.favorite-card');
            
            if (!confirm('Hapus resep dari favorit?')) return;
            
            // Show loading on button
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            try {
                // Remove from server
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
                    // Remove from UI with animation
                    recipeCard.style.transform = 'scale(0.95)';
                    recipeCard.style.opacity = '0';
                    setTimeout(() => {
                        recipeCard.remove();
                        updateStats();
                        checkEmptyState();
                    }, 300);
                } else {
                    alert('Gagal menghapus dari favorit');
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
                this.innerHTML = originalHTML;
                this.disabled = false;
            }
        });
    });
    
    // Quick view
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            const recipeId = this.dataset.recipeId;
            
            // Show loading
            const modalContent = document.getElementById('quickViewContent');
            modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
            quickViewModal.classList.add('active');
            
            try {
                // Fetch recipe details
                const response = await fetch(`api/get_recipe.php?id=${recipeId}`);
                const recipe = await response.json();
                
                if (recipe) {
                    modalContent.innerHTML = `
                        <div class="quick-view-content">
                            <h4>${recipe.judul}</h4>
                            <p>${recipe.deskripsi}</p>
                            
                            <h4>📋 Bahan-bahan</h4>
                            <div style="white-space: pre-line;">${recipe.bahan}</div>
                            
                            <h4>👩‍🍳 Cara Masak</h4>
                            <div style="white-space: pre-line;">${recipe.cara_masak}</div>
                            
                            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #eee;">
                                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <span class="difficulty-badge ${getDifficultyClass(recipe.tingkat_kesulitan)}">
                                        ${recipe.tingkat_kesulitan}
                                    </span>
                                    <span style="color: var(--gray);">
                                        <i class="fas fa-clock"></i> ${recipe.waktu} menit
                                    </span>
                                    <span style="color: var(--gray);">
                                        <i class="fas fa-user-friends"></i> ${recipe.porsi} porsi
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    modalContent.innerHTML = '<p class="error">Gagal memuat resep</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                modalContent.innerHTML = '<p class="error">Terjadi kesalahan</p>';
            }
        });
    });
    
    // Share options
    shareOptions.forEach(option => {
        option.addEventListener('click', function() {
            const platform = this.dataset.platform;
            const url = shareUrlInput.value;
            const title = 'Daftar Resep Favorit Saya';
            
            switch(platform) {
                case 'copy':
                    copyToClipboard(url);
                    showToast('Link berhasil disalin!');
                    break;
                case 'whatsapp':
                    window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(title + ': ' + url)}`, '_blank');
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
    copyLinkBtn.addEventListener('click', function() {
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
        const searchTerm = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        const difficulty = difficultyFilter.value;
        const maxTime = timeFilter.value;
        
        const cards = document.querySelectorAll('.favorite-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const title = card.querySelector('.recipe-title').textContent.toLowerCase();
            const desc = card.querySelector('.recipe-description').textContent.toLowerCase();
            const cardCategory = card.dataset.category;
            const cardDifficulty = card.dataset.difficulty;
            const cardTime = parseInt(card.dataset.time);
            
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
                setTimeout(() => {
                    card.style.animation = 'fadeIn 0.5s ease';
                }, 10);
            } else {
                card.style.display = 'none';
            }
        });
        
        checkEmptyState(visibleCount);
    }
    
    function sortFavorites(sortBy) {
        const container = document.getElementById('favoritesGrid');
        const cards = Array.from(document.querySelectorAll('.favorite-card'));
        
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
        
        sortBtn.innerHTML = `<i class="fas fa-sort-amount-down"></i> ${sortText[sortBy]}`;
    }
    
    function updateStats() {
        const cards = document.querySelectorAll('.favorite-card');
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
        
        // Animate numbers
        animateNumber(easyCount, easy);
        animateNumber(mediumCount, medium);
        animateNumber(hardCount, hard);
        
        // Format total time
        const hours = Math.floor(total / 60);
        const minutes = total % 60;
        let timeText = '';
        
        if (hours > 0) {
            timeText += `${hours} jam `;
        }
        if (minutes > 0 || hours === 0) {
            timeText += `${minutes} menit`;
        }
        
        totalTime.textContent = timeText.trim();
        favoriteCount.textContent = cards.length;
    }
    
    function checkEmptyState(visibleCount = null) {
        const cards = document.querySelectorAll('.favorite-card');
        const totalCards = cards.length;
        const visibleCards = visibleCount !== null ? visibleCount : cards.length;
        
        if (totalCards === 0) {
            noFavorites.style.display = 'block';
            emptyState.style.display = 'none';
        } else if (visibleCards === 0) {
            noFavorites.style.display = 'none';
            emptyState.style.display = 'block';
        } else {
            noFavorites.style.display = 'none';
            emptyState.style.display = 'none';
        }
    }
    
    function animateNumber(element, target) {
        const current = parseInt(element.textContent) || 0;
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
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Add to cart functionality (placeholder)
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const recipeId = this.dataset.recipeId;
            alert('Fitur daftar belanja akan tersedia segera!');
        });
    });
    
    // Initial check
    checkEmptyState();

    // Refresh favorites list (can be triggered after a favorite is changed elsewhere)
    async function refreshFavorites(limit = 50) {
        if (!document.querySelector('.favorites-container')) return;
        try {
            const res = await fetch('api/get_favorites.php?limit=' + encodeURIComponent(limit));
            const data = await res.json();
            if (!data.success) return;

            let grid = document.getElementById('favoritesGrid');
            const container = document.querySelector('.favorites-container');
            const noFav = document.getElementById('noFavorites');
            const emptyStateEl = document.getElementById('emptyState');

            if (!grid) {
                grid = document.createElement('div');
                grid.className = 'favorites-grid';
                grid.id = 'favoritesGrid';
                // Insert at top of container
                container.insertBefore(grid, container.firstChild);
            }

            grid.innerHTML = '';

            if (data.count === 0) {
                if (noFav) noFav.style.display = 'block';
                if (emptyStateEl) emptyStateEl.style.display = 'none';
                updateStats();
                return;
            }

            data.favorites.forEach(recipe => {
                const card = document.createElement('div');
                card.className = 'favorite-card';
                card.dataset.category = recipe.kategori || 'all';
                card.dataset.difficulty = (recipe.tingkat_kesulitan || 'sedang');
                card.dataset.time = recipe.waktu || 0;

                card.innerHTML = `
                    <button class="remove-favorite" data-recipe-id="${recipe.id}"><i class="fas fa-times"></i></button>
                    <div class="favorite-image">
                        <img src="${recipe.image_url || 'assets/images/default-recipe.jpg'}" alt="${escapeHTML(recipe.judul)}">
                    </div>
                    <div class="favorite-info">
                        <div class="recipe-header">
                            <div class="recipe-title">${escapeHTML(recipe.judul)}</div>
                            <div class="favorite-date">${recipe.waktu} menit</div>
                        </div>
                        <p class="recipe-description">${escapeHTML(recipe.excerpt || '')}</p>
                        <div class="recipe-actions">
                            <a href="?page=resep&id=${recipe.id}" class="btn btn-outline btn-sm">Lihat</a>
                            <button class="btn btn-primary btn-sm quick-view" data-recipe-id="${recipe.id}">Quick View</button>
                        </div>
                    </div>
                `;

                grid.appendChild(card);
            });

            // Attach remove handlers
            document.querySelectorAll('.remove-favorite').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.stopPropagation();
                    if (!confirm('Hapus resep dari favorit?')) return;
                    const recipeId = this.dataset.recipeId;
                    try {
                        const res = await fetch('api/toggle_favorite.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ recipeId: recipeId, action: 'remove' })
                        });
                        const result = await res.json();
                        if (result.success) {
                            showToast('Resep dihapus dari favorit');
                            refreshFavorites(limit);
                        } else {
                            alert('Gagal menghapus favorit');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Terjadi kesalahan');
                    }
                });
            });

            updateStats();
            checkEmptyState();
        } catch (err) {
            console.error('Failed to refresh favorites:', err);
        }
    }

    // Small helper
    function escapeHTML(str) {
        return String(str).replace(/[&<>"]/g, function (s) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[s];
        });
    }

    // Listen for favorite changes across pages
    document.addEventListener('favorite:changed', function(e) {
        refreshFavorites();
    });

});
</script>