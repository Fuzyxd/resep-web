<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/database.php';

$recipe_id = $_GET['id'] ?? null;
$search_query = $_GET['search'] ?? '';
$from = $_GET['from'] ?? '';

// Normalize source for breadcrumb
$sourceMap = [
    'home' => ['label' => 'Beranda', 'href' => '?page=home'],
    'all_resep' => ['label' => 'Resep', 'href' => '?page=all_resep'],
    'favorit' => ['label' => 'Favorit', 'href' => '?page=favorit']
];
$fromKey = isset($sourceMap[$from]) ? $from : '';

// Validate recipe id to avoid invalid queries
if ($recipe_id && !is_numeric($recipe_id)) {
    header('Location: ?page=home');
    exit;
}
$recipe_id = $recipe_id ? (int)$recipe_id : null; 

if (!$recipe_id && !$search_query) {
    header('Location: ?page=home');
    exit;
}

// Jika ada search query, redirect ke halaman search
if ($search_query) {
    // Search functionality akan diimplementasikan nanti
    header('Location: ?page=search&q=' . urlencode($search_query));
    exit;
}

// Get recipe dengan gambar
$recipe = getRecipeWithImage($recipe_id);
if (!$recipe) {
    echo '<script>window.location.href = "?page=home";</script>';
    exit;
}

// Check if recipe is favorited by current user
$is_favorited = false;
if (isset($_SESSION['user'])) {
    $is_favorited = isFavorited($_SESSION['user']['uid'], $recipe_id);
}

// Parse bahan dan langkah (DB uses langkah with \n)
$bahan_list = preg_split("/\\r?\\n/", $recipe['bahan'] ?? '');
$langkah_raw = $recipe['langkah'] ?? ($recipe['cara_masak'] ?? '');
$langkah_raw = str_replace('/n', "\n", $langkah_raw);
$cara_masak_list = preg_split("/\\r?\\n/", $langkah_raw);

// Get similar recipes
$similar_recipes = getRecipesByCategory($recipe['kategori'], 3);

if (isset($_SESSION['user'])) {
    syncUserProfileFromSession($_SESSION['user']);
}

if (!function_exists('format_kcal')) {
    function format_kcal($value) {
        if ($value === null || $value === '') return '-';
        return '~' . number_format((float)$value, 0, '.', '') . ' kcal';
    }
}

if (!function_exists('format_gram')) {
    function format_gram($value) {
        if ($value === null || $value === '') return '-';
        $num = rtrim(rtrim(number_format((float)$value, 1, '.', ''), '0'), '.');
        return '~' . $num . 'g';
    }
}

// Handle comment submission
$comment_notice = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    if (!isset($_SESSION['user'])) {
        $comment_notice = ['type' => 'error', 'text' => 'Silakan login terlebih dahulu untuk menulis komentar.'];
    } else {
        $comment_text = trim($_POST['comment_text']);
        if ($comment_text === '') {
            $comment_notice = ['type' => 'error', 'text' => 'Komentar tidak boleh kosong.'];
        } else {
            $ok = addRecipeComment($_SESSION['user'], $recipe_id, $comment_text);
            if ($ok) {
                header('Location: ?page=resep&id=' . $recipe_id . '&comment=success#comments');
                exit;
            }
            $comment_notice = ['type' => 'error', 'text' => 'Gagal menyimpan komentar. Silakan coba lagi.'];
        }
    }
}

if (isset($_GET['comment']) && $_GET['comment'] === 'success') {
    $comment_notice = ['type' => 'success', 'text' => 'Komentar berhasil ditambahkan.'];
}

$comments = getRecipeComments($recipe_id, 50);
?>

<section class="recipe-detail-section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <?php if ($fromKey !== ''): ?>
                <a href="<?= $sourceMap[$fromKey]['href'] ?>"><i class="fas fa-home"></i> <?= $sourceMap[$fromKey]['label'] ?></a>
            <?php else: ?>
                <a href="?page=home"><i class="fas fa-home"></i> Beranda</a>
            <?php endif; ?>
            <i class="fas fa-chevron-right"></i>
            <span class="current"><?= htmlspecialchars($recipe['judul']) ?></span>
        </nav>

        <!-- Recipe Header -->
        <div class="recipe-header">
            <div class="recipe-hero">
                <!-- Recipe Image -->
                <div class="recipe-main-image">
                    <img src="<?= htmlspecialchars($recipe['image_url']) ?>" 
                         alt="<?= htmlspecialchars($recipe['judul']) ?>"
                         class="recipe-image"
                         onerror="this.onerror=null;this.src='assets/images/default-recipe.jpg';">
                    
                    <!-- Recipe Badges -->
                    <div class="recipe-badges">
                        <span class="badge difficulty-badge <?= 
                            strtolower($recipe['tingkat_kesulitan']) == 'mudah' ? 'difficulty-easy' : 
                            (strtolower($recipe['tingkat_kesulitan']) == 'sedang' ? 'difficulty-medium' : 'difficulty-hard')
                        ?>">
                            <i class="fas fa-<?= 
                                strtolower($recipe['tingkat_kesulitan']) == 'mudah' ? 'smile' : 
                                (strtolower($recipe['tingkat_kesulitan']) == 'sedang' ? 'meh' : 'fire')
                            ?>"></i>
                            <?= ucfirst($recipe['tingkat_kesulitan']) ?>
                        </span>
                        
                        <span class="badge time-badge">
                            <i class="fas fa-clock"></i>
                            <?= ($recipe['waktu'] ?? 0) ?> menit
                        </span>
                        
                        <span class="badge category-badge">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($recipe['kategori']) ?>
                        </span>
                    </div>
                </div>

                <!-- Recipe Info Sidebar -->
                <div class="recipe-sidebar">
                    <div class="sidebar-card">
                        <h3>Informasi Resep</h3>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon prep">
                                    <i class="fas fa-hourglass-start"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Waktu Memasak</span>
                                    <span class="info-value"><?= ($recipe['waktu'] ?? 0) ?> menit</span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon cook">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Total Waktu</span>
                                    <span class="info-value"><?= ($recipe['waktu'] ?? 0) ?> menit</span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon serving">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Porsi</span>
                                    <span class="info-value"><?= $recipe['porsi'] ?> orang</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-favorite <?= $is_favorited ? 'active' : '' ?>" 
                                    id="favoriteBtn" 
                                    data-recipe-id="<?= $recipe['id'] ?>">
                                <i class="<?= $is_favorited ? 'fas' : 'far' ?> fa-heart"></i>
                                <span><?= $is_favorited ? 'Favorited' : 'Tambahkan ke Favorit' ?></span>
                            </button>
                            
                            <button class="btn btn-outline" id="shareBtn">
                                <i class="fas fa-share-alt"></i> Bagikan
                            </button>
                            
                            <button class="btn btn-outline" id="printBtn">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- Nutrition Info (Full Width) -->
            <div class="sidebar-card nutrition-card nutrition-card-full">
                <h3>Informasi Gizi (per porsi)</h3>
                <div class="nutrition-grid">
                    <div class="nutrition-item">
                        <span class="nutrition-label"><i class="fas fa-fire"></i> Kalori</span>
                        <span class="nutrition-value"><?= format_kcal($recipe['kalori'] ?? null) ?></span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label"><i class="fas fa-dumbbell"></i> Protein</span>
                        <span class="nutrition-value"><?= format_gram($recipe['protein'] ?? null) ?></span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label"><i class="fas fa-bread-slice"></i> Karbohidrat</span>
                        <span class="nutrition-value"><?= format_gram($recipe['karbohidrat'] ?? null) ?></span>
                    </div>
                    <div class="nutrition-item">
                        <span class="nutrition-label"><i class="fas fa-tint"></i> Lemak</span>
                        <span class="nutrition-value"><?= format_gram($recipe['lemak'] ?? null) ?></span>
                    </div>
                </div>
                <p class="nutrition-note">* Perkiraan per porsi berdasarkan bahan yang digunakan</p>
            </div>

            <!-- Recipe Title and Description -->
            <div class="recipe-title-section">
                <h1><?= htmlspecialchars($recipe['judul']) ?></h1>
                <p class="recipe-description">
                    <?= nl2br(htmlspecialchars($recipe['deskripsi'])) ?>
                </p>
                
                <div class="recipe-meta">
                    <div class="meta-item">
                        <i class="far fa-calendar"></i>
                        <span>Ditambahkan: <?= date('d M Y', strtotime($recipe['created_at'])) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span>By: Admin Resep Nusantara</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-eye"></i>
                        <span>Dilihat: <?= rand(100, 1000) ?> kali</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipe Content -->
        <div class="recipe-content-grid">
            <!-- Ingredients Section -->
            <div class="ingredients-section">
                <div class="section-header-row">
                    <h2><i class="fas fa-shopping-basket"></i> Bahan-bahan</h2>
                    <button class="btn btn-outline btn-sm" id="copyIngredients">
                        <i class="fas fa-copy"></i> Salin Bahan
                    </button>
                </div>
                
                <div class="ingredients-list" id="ingredientsList">
                    <?php foreach ($bahan_list as $index => $bahan): 
                        if (trim($bahan)): ?>
                            <div class="ingredient-item">
                                <input type="checkbox" id="ingredient-<?= $index ?>" class="ingredient-checkbox">
                                <label for="ingredient-<?= $index ?>">
                                    <span class="checkmark"></span>
                                    <span class="ingredient-text"><?= htmlspecialchars(trim($bahan)) ?></span>
                                </label>
                            </div>
                        <?php endif;
                    endforeach; ?>
                </div>
                
                <div class="ingredients-actions">
                    <button class="btn btn-outline btn-sm" id="checkAll">
                        <i class="fas fa-check-square"></i> Centang Semua
                    </button>
                    <button class="btn btn-outline btn-sm" id="uncheckAll">
                        <i class="fas fa-square"></i> Hapus Centang
                    </button>
                </div>
            </div>

            <!-- Instructions Section -->
            <div class="instructions-section">
                <div class="section-header-row">
                    <h2><i class="fas fa-mortar-pestle"></i> Cara Membuat</h2>
                    <div class="step-counter">
                        <span id="currentStep">1</span> / <span id="totalSteps"><?= count(array_filter($cara_masak_list)) ?></span> Langkah
                    </div>
                </div>
                
                <div class="instructions-list" id="instructionsList">
                    <?php 
                    $step_count = 0;
                    foreach ($cara_masak_list as $index => $cara): 
                        if (trim($cara)): 
                            $step_count++;
                    ?>
                            <div class="instruction-step" data-step="<?= $step_count ?>" <?= $step_count === 1 ? '' : 'style="display:none;"' ?>>
                                <div class="step-number"><?= $step_count ?></div>
                                <div class="step-content">
                                    <?php
                                        $step_text = trim($cara);
                                        $step_text = preg_replace('/^\s*\d+[\.\)\-]?\s*/', '', $step_text);
                                        $step_text = preg_replace('/\s+/', ' ', $step_text);
                                    ?>
                                    <p><?= htmlspecialchars($step_text) ?></p>
                                    <?php if (preg_match('/\bgoreng\b|di\s*goreng|menggoreng|gorengan/i', $cara)): ?>
                                        <div class="step-tip">
                                            <i class="fas fa-lightbulb"></i>
                                            <strong>Tips:</strong> Pastikan api tidak terlalu besar agar bumbu tidak gosong.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="step-actions"></div>
                            </div>
                        <?php endif;
                    endforeach; ?>
                </div>
                
                <div class="instructions-actions">
                    <button class="btn btn-outline" id="prevStep">
                        <i class="fas fa-chevron-left"></i> Sebelumnya
                    </button>
                    <button class="btn btn-primary" id="nextStep">
                        Selanjutnya <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tips Section -->
        <div class="tips-section">
            <h3><i class="fas fa-lightbulb"></i> Tips & Trik</h3>
            <div class="tips-grid">
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-temperature-high"></i>
                    </div>
                    <div class="tip-content">
                        <h4>Pengaturan Api</h4>
                        <p>Gunakan api sedang saat menumis bumbu agar tidak cepat gosong.</p>
                    </div>
                </div>
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="tip-content">
                        <h4>Waktu Marinasi</h4>
                        <p>Marinasi daging minimal 30 menit agar bumbu meresap sempurna.</p>
                    </div>
                </div>
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="tip-content">
                        <h4>Penyajian</h4>
                        <p>Sajikan segera setelah matang untuk mendapatkan cita rasa terbaik.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Recipes -->
        <?php if ($similar_recipes->num_rows > 0): ?>
        <div class="similar-recipes-section">
            <div class="section-header">
                <h2 class="section-title">Resep Serupa</h2>
                <p class="section-subtitle">Resep lain dalam kategori <?= htmlspecialchars($recipe['kategori']) ?></p>
            </div>
            
            <div class="similar-recipes-grid">
                <?php while ($similar = $similar_recipes->fetch_assoc()): 
                    if ($similar['id'] == $recipe_id) continue; // Skip current recipe
                    $similar_image = getRecipeImage($similar, 'thumb');
                ?>
                    <a href="?page=resep&id=<?= $similar['id'] ?>" class="similar-recipe-card">
                        <img src="<?= htmlspecialchars($similar_image) ?>" 
                             alt="<?= htmlspecialchars($similar['judul']) ?>"
                             class="similar-image"
                             onerror="this.onerror=null;this.src='assets/images/default-recipe.jpg';">
                        <div class="similar-content">
                            <h4><?= htmlspecialchars($similar['judul']) ?></h4>
                            <div class="similar-meta">
                                <span><i class="fas fa-clock"></i> <?= ($similar['waktu'] ?? 0) ?>m</span>
                                <span><i class="fas fa-user-friends"></i> <?= $similar['porsi'] ?> porsi</span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comments Section -->
        <div class="comments-section" id="comments">
            <div class="section-header">
                <h2 class="section-title">Komentar</h2>
                <p class="section-subtitle">Bagikan pengalaman Anda memasak resep ini (<?= count($comments) ?> komentar)</p>
            </div>

            <?php if ($comment_notice): ?>
                <div class="comment-alert <?= $comment_notice['type'] ?>">
                    <i class="fas <?= $comment_notice['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <span><?= htmlspecialchars($comment_notice['text']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user'])): ?>
                <form class="comment-form" method="POST" action="?page=resep&id=<?= $recipe_id ?>#comments">
                    <div class="comment-input">
                        <textarea name="comment_text" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                    </div>
                    <div class="comment-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Komentar
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="comment-login">
                    <p>Silakan login untuk menulis komentar.</p>
                    <a href="?page=login" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</a>
                </div>
            <?php endif; ?>

            <?php if (count($comments) > 0): ?>
                <div class="comment-list">
                    <?php foreach ($comments as $c): ?>
                        <div class="comment-card">
                            <div class="comment-avatar">
                                <?php if (!empty($c['photo_url'])): ?>
                                    <img src="<?= htmlspecialchars($c['photo_url']) ?>" alt="<?= htmlspecialchars($c['display_name']) ?>">
                                <?php else: ?>
                                    <span><?= strtoupper(substr($c['display_name'], 0, 1)) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="comment-body">
                                <div class="comment-meta">
                                    <strong><?= htmlspecialchars($c['display_name']) ?></strong>
                                    <span><?= date('d M Y H:i', strtotime($c['created_at'])) ?></span>
                                </div>
                                <p><?= nl2br(htmlspecialchars($c['komentar'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="comment-empty">
                    <div class="placeholder-icon">
                        <i class="far fa-comments"></i>
                    </div>
                    <h3>Belum ada komentar</h3>
                    <p>Jadilah yang pertama memberikan komentar untuk resep ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="modal" id="shareModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Bagikan Resep</h3>
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
</section>

<style>
/* ============================================
   RECIPE DETAIL PAGE STYLES
   ============================================ */

.recipe-detail-section {
    padding: 2rem 0 4rem;
    background: #f9f9f9;
    min-height: 100vh;
}

/* Breadcrumb */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 2rem;
    padding: 15px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    font-size: 0.9rem;
}

.breadcrumb a {
    color: var(--gray);
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: var(--primary);
}

.breadcrumb .current {
    color: var(--primary);
    font-weight: 600;
}

.breadcrumb i {
    font-size: 0.8rem;
    color: #ccc;
}

/* Recipe Header */
.recipe-header {
    margin-bottom: 1.5rem;
}

.recipe-hero {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 1.2rem;
}

@media (max-width: 992px) {
    .recipe-hero {
        grid-template-columns: 1fr;
    }
}

/* Recipe Main Image */
.recipe-main-image {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    height: 430px;
}

.recipe-main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.recipe-main-image:hover img {
    transform: scale(1.02);
}

.recipe-badges {
    position: absolute;
    top: 20px;
    left: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 2;
}

.badge {
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.difficulty-badge {
    color: white;
    border: none;
}

.difficulty-easy {
    background: linear-gradient(135deg, #2ed573, #20bf6b);
}

.difficulty-medium {
    background: linear-gradient(135deg, #ffa502, #ff7f00);
}

.difficulty-hard {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
}

.time-badge {
    background: rgba(78, 205, 196, 0.9);
    color: white;
}

.category-badge {
    background: rgba(45, 52, 54, 0.9);
    color: white;
}

/* Recipe Sidebar */
.recipe-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.sidebar-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.sidebar-card h3 {
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.info-icon.prep {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.info-icon.cook {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.info-icon.total {
    background: rgba(46, 204, 113, 0.1);
    color: #2ecc71;
}

.info-icon.serving {
    background: rgba(155, 89, 182, 0.1);
    color: #9b59b6;
}

.info-content {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.85rem;
    color: var(--gray);
    margin-bottom: 2px;
}

.info-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-favorite {
    position: relative;
    overflow: hidden;
}

.btn-favorite.active {
    background: var(--primary);
    color: white;
}

.btn-favorite i {
    transition: transform 0.3s ease;
}

.btn-favorite:hover i {
    transform: scale(1.1);
}

/* Nutrition Card */
.nutrition-card h3 {
    color: #2c3e50;
}

.nutrition-card-full {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
}

.nutrition-grid {
    display: flex;
    align-items: stretch;
    gap: 0;
    margin: 0 0 1rem 0;
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.nutrition-item {
    flex: 1 1 25%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    padding: 14px 16px;
    background: transparent;
    border-right: 1px solid #f0f0f0;
    min-width: 0;
}

.nutrition-item:last-child {
    border-right: none;
}

.nutrition-label {
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8a8f98;
    font-weight: 600;
    margin-bottom: 6px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.nutrition-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #ff6b6b;
    line-height: 1.1;
}

@media (max-width: 900px) {
    .nutrition-grid {
        flex-wrap: wrap;
    }

    .nutrition-item {
        flex: 1 1 50%;
        border-right: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
    }

    .nutrition-item:nth-child(2) {
        border-right: none;
    }

    .nutrition-item:nth-child(3),
    .nutrition-item:nth-child(4) {
        border-bottom: none;
    }
}

.nutrition-note {
    font-size: 0.8rem;
    color: var(--gray);
    text-align: center;
    margin-top: 1rem;
    font-style: italic;
}

/* Recipe Title Section */
.recipe-title-section {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.recipe-title-section h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    color: var(--dark);
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.recipe-description {
    font-size: 1.1rem;
    line-height: 1.6;
    color: var(--gray);
    margin-bottom: 2rem;
}

.recipe-meta {
    display: flex;
    gap: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--light-gray);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gray);
    font-size: 0.9rem;
}

.meta-item i {
    color: var(--primary);
}

/* Recipe Content Grid */
.recipe-content-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

@media (max-width: 768px) {
    .recipe-content-grid {
        grid-template-columns: 1fr;
    }
}

/* Ingredients Section */
.ingredients-section,
.instructions-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.section-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--light-gray);
}

.section-header-row h2 {
    font-size: 1.6rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.step-counter {
    background: var(--primary);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Ingredients List */
.ingredients-list {
    margin-bottom: 1.5rem;
}

.ingredient-item {
    margin-bottom: 12px;
}

.ingredient-item:last-child {
    margin-bottom: 0;
}

.ingredient-checkbox {
    display: none;
}

.ingredient-item label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    padding: 12px;
    border-radius: 10px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.ingredient-item label:hover {
    background: rgba(255, 107, 107, 0.05);
    border-color: rgba(255, 107, 107, 0.2);
}

.ingredient-checkbox:checked + label {
    background: rgba(46, 213, 115, 0.1);
    border-color: rgba(46, 213, 115, 0.3);
}

.ingredient-checkbox:checked + label .ingredient-text {
    text-decoration: line-through;
    color: #95a5a6;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid var(--light-gray);
    border-radius: 4px;
    margin-right: 12px;
    margin-top: 2px;
    flex-shrink: 0;
    transition: all 0.3s ease;
    position: relative;
}

.ingredient-checkbox:checked + label .checkmark {
    background: var(--primary);
    border-color: var(--primary);
}

.ingredient-checkbox:checked + label .checkmark::after {
    content: '';
    position: absolute;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.ingredient-text {
    flex: 1;
    line-height: 1.5;
    color: var(--dark);
    font-size: 1rem;
}

.ingredients-actions {
    display: flex;
    gap: 10px;
}

/* Instructions Section */
.instructions-list {
    margin-bottom: 2rem;
}

.instruction-step {
    display: grid;
    grid-template-columns: 46px 1fr;
    column-gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem 1.6rem;
    background: #fff3f3;
    border-radius: 18px;
    border-left: 6px solid var(--primary);
    transition: all 0.3s ease;
    align-items: center;
}

.instruction-step:last-child {
    margin-bottom: 0;
}

.instruction-step:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.instruction-step.completed {
    background: rgba(46, 213, 115, 0.1);
    border-color: #2ed573;
}

.step-number {
    width: 46px;
    height: 46px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: 0 8px 16px rgba(255, 107, 107, 0.25);
}

.instruction-step.completed .step-number {
    background: #2ed573;
}

.step-content {
    display: block;
}

.step-content p {
    line-height: 1.6;
    color: var(--dark);
    margin: 0;
    font-size: 1.05rem;
}

.step-tip {
    background: rgba(255, 165, 2, 0.1);
    border-left: 3px solid #ffa502;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #d35400;
}

.step-tip i {
    margin-right: 8px;
}

.step-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-shrink: 0;
}

.step-btn {
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.note-btn {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.note-btn:hover {
    background: rgba(52, 152, 219, 0.2);
}

.instructions-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Tips Section */
.tips-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 3rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.tips-section h3 {
    font-size: 1.6rem;
    color: var(--dark);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.tip-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 15px;
    border-left: 4px solid var(--primary);
    transition: all 0.3s ease;
}

.tip-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.tip-icon {
    width: 50px;
    height: 50px;
    background: var(--primary);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.tip-content h4 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: var(--dark);
}

.tip-content p {
    font-size: 0.9rem;
    color: var(--gray);
    line-height: 1.5;
}

/* Similar Recipes */
.similar-recipes-section {
    margin-bottom: 3rem;
}

.similar-recipes-section .section-header {
    margin-bottom: 2rem;
}

.similar-recipes-section .section-header h2 {
    font-size: 1.8rem;
    color: var(--dark);
}

.similar-recipes-section .section-header p {
    color: var(--gray);
    font-size: 1rem;
}

.similar-recipes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.similar-recipe-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
}

.similar-recipe-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.similar-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.similar-content {
    padding: 1.5rem;
}

.similar-content h4 {
    font-size: 1.1rem;
    margin-bottom: 0.8rem;
    color: var(--dark);
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.similar-meta {
    display: flex;
    justify-content: space-between;
    color: var(--gray);
    font-size: 0.9rem;
}

.similar-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Comments Section */
.comments-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.comments-section .section-header h2 {
    font-size: 1.8rem;
    color: var(--dark);
}

.comments-section .section-header p {
    color: var(--gray);
    font-size: 1rem;
}

.comment-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.comment-alert.success {
    background: rgba(46, 213, 115, 0.15);
    color: #1e8f50;
    border: 1px solid rgba(46, 213, 115, 0.3);
}

.comment-alert.error {
    background: rgba(255, 107, 107, 0.12);
    color: #c0392b;
    border: 1px solid rgba(255, 107, 107, 0.3);
}

.comment-form {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.8rem;
    border: 1px solid var(--light-gray);
}

.comment-input textarea {
    width: 100%;
    resize: vertical;
    border: 2px solid var(--light-gray);
    border-radius: 12px;
    padding: 12px 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    outline: none;
    min-height: 90px;
}

.comment-input textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.15);
}

.comment-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 12px;
}

.comment-login {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.2rem 1.4rem;
    background: #fff3f3;
    border-radius: 14px;
    border: 1px solid rgba(255, 107, 107, 0.2);
    margin-bottom: 1.5rem;
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.comment-card {
    display: grid;
    grid-template-columns: 48px 1fr;
    gap: 1rem;
    padding: 1.2rem 1.4rem;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid var(--light-gray);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.comment-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.05rem;
    overflow: hidden;
}

.comment-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.comment-body p {
    margin: 0.5rem 0 0;
    color: var(--dark);
    line-height: 1.6;
}

.comment-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.85rem;
    color: var(--gray);
}

.comment-meta strong {
    color: var(--dark);
    font-size: 0.95rem;
}

.comment-empty {
    text-align: center;
    padding: 2.5rem 2rem;
}

.comment-empty .placeholder-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 107, 107, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.2rem;
    font-size: 2.4rem;
    color: var(--primary);
}

.comment-empty h3 {
    font-size: 1.4rem;
    margin-bottom: 0.6rem;
    color: var(--dark);
}

.comment-empty p {
    color: var(--gray);
    max-width: 420px;
    margin: 0 auto;
}

/* Modal */
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
    font-size: 1.3rem;
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

/* Share Options */
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

/* Responsive Design */
@media (max-width: 992px) {
    .recipe-title-section h1 {
        font-size: 2.2rem;
    }
    
    .recipe-hero {
        grid-template-columns: 1fr;
    }
    
    .recipe-main-image {
        height: 300px;
    }
}

@media (max-width: 768px) {
    .recipe-content-grid {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
    }
    
    .share-options {
        grid-template-columns: 1fr;
    }
    
    .recipe-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .instructions-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .ingredients-actions {
        flex-direction: column;
    }

    .comment-login {
        flex-direction: column;
        align-items: flex-start;
    }

    .comment-card {
        grid-template-columns: 40px 1fr;
    }
}

@media (max-width: 576px) {
    .recipe-title-section {
        padding: 1.5rem;
    }
    
    .recipe-title-section h1 {
        font-size: 1.8rem;
    }
    
    .section-header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .instruction-step {
        flex-direction: column;
        gap: 1rem;
    }
    
    .step-actions {
        flex-direction: row;
        justify-content: flex-start;
    }
    
    .share-link {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const favoriteBtn = document.getElementById('favoriteBtn');
    const shareBtn = document.getElementById('shareBtn');
    const printBtn = document.getElementById('printBtn');
    const copyIngredientsBtn = document.getElementById('copyIngredients');
    const checkAllBtn = document.getElementById('checkAll');
    const uncheckAllBtn = document.getElementById('uncheckAll');
    const ingredientsList = document.getElementById('ingredientsList');
    const instructionsList = document.getElementById('instructionsList');
    const prevStepBtn = document.getElementById('prevStep');
    const nextStepBtn = document.getElementById('nextStep');
    const currentStepSpan = document.getElementById('currentStep');
    const totalStepsSpan = document.getElementById('totalSteps');
    const shareModal = document.getElementById('shareModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const shareOptions = document.querySelectorAll('.share-option');
    const copyLinkBtn = document.getElementById('copyLink');
    const shareUrlInput = document.getElementById('shareUrl');
    
    // State
    let currentStep = 1;
    const totalSteps = parseInt(totalStepsSpan.textContent);
    
    // Initialize
    updateStepNavigation();
    highlightCurrentStep();
    
    // Favorite functionality
    favoriteBtn?.addEventListener('click', async function() {
        const recipeId = this.dataset.recipeId;
        const heartIcon = this.querySelector('i');
        const textSpan = this.querySelector('span');
        
        // Check if user is logged in
        <?php if (!isset($_SESSION['user'])): ?>
            alert('Silakan login terlebih dahulu untuk menambahkan ke favorit!');
            window.location.href = '?page=login';
            return;
        <?php endif; ?>
        
        // Toggle state
        const isActive = this.classList.contains('active');
        
        // Show loading
        const originalHTML = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        this.disabled = true;
        
        try {
            const response = await fetch('api/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipeId: recipeId,
                    action: isActive ? 'remove' : 'add'
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update UI according to server truth
                const favorited = !!result.favorited;

                if (favorited) {
                    this.classList.add('active');
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    textSpan.textContent = 'Favorited';
                } else {
                    this.classList.remove('active');
                    heartIcon.classList.remove('fas');
                    heartIcon.classList.add('far');
                    textSpan.textContent = 'Tambahkan ke Favorit';
                }

                // Add animation via class to avoid affecting other elements
                this.classList.remove('animate-heart');
                void this.offsetWidth;
                this.classList.add('animate-heart');
                this.addEventListener('animationend', function handler() {
                    this.classList.remove('animate-heart');
                    this.removeEventListener('animationend', handler);
                });

                // Show toast
                showToast(favorited ? 'Resep ditambahkan ke favorit!' : 'Resep dihapus dari favorit');

                // Notify other pages to refresh favorites
                document.dispatchEvent(new CustomEvent('favorite:changed', {
                    detail: { recipeId: recipeId, favorited: favorited }
                }));

                this.disabled = false;
                this.innerHTML = originalHTML;
            } else {
                alert('Gagal memperbarui favorit: ' + (result.error || 'Unknown error'));
                this.innerHTML = originalHTML;
                this.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
            this.innerHTML = originalHTML;
            this.disabled = false;
        } finally {
            // Reset button after animation
            setTimeout(() => {
                if (!this.disabled) {
                    this.innerHTML = originalHTML;
                }
            }, 1000);
        }
    });
    
    // Share functionality
    shareBtn?.addEventListener('click', function() {
        shareModal.classList.add('active');
    });
    
    // Print functionality
    printBtn?.addEventListener('click', function() {
        window.print();
    });
    
    // Copy ingredients
    copyIngredientsBtn?.addEventListener('click', function() {
        const ingredients = [];
        document.querySelectorAll('.ingredient-text').forEach(el => {
            ingredients.push(el.textContent.trim());
        });
        
        const textToCopy = `Bahan-bahan untuk ${document.querySelector('h1').textContent}:\n\n` +
                          ingredients.map((ing, i) => `${i + 1}. ${ing}`).join('\n');
        
        copyToClipboard(textToCopy);
        showToast('Bahan berhasil disalin!');
    });
    
    // Check all ingredients
    checkAllBtn?.addEventListener('click', function() {
        document.querySelectorAll('.ingredient-checkbox').forEach(checkbox => {
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));
        });
    });
    
    // Uncheck all ingredients
    uncheckAllBtn?.addEventListener('click', function() {
        document.querySelectorAll('.ingredient-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change'));
        });
    });
    
    // Step navigation
    prevStepBtn?.addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateStepNavigation();
            highlightCurrentStep();
            scrollToStep(currentStep);
        }
    });
    
    nextStepBtn?.addEventListener('click', function() {
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepNavigation();
            highlightCurrentStep();
            scrollToStep(currentStep);
        }
    });
    
    // Share options
    shareOptions.forEach(option => {
        option.addEventListener('click', function() {
            const platform = this.dataset.platform;
            const url = shareUrlInput.value;
            const title = document.querySelector('h1').textContent;
            const text = 'Lihat resep ' + title + ' di Resep Nusantara';
            
            switch(platform) {
                case 'copy':
                    copyToClipboard(url);
                    showToast('Link berhasil disalin!');
                    break;
                case 'whatsapp':
                    window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(text + ': ' + url)}`, '_blank');
                    break;
                case 'facebook':
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(text)}`, '_blank');
                    break;
                case 'twitter':
                    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
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
    
    // Close modal on background click
    shareModal?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
    
    // Keyboard navigation for steps
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && currentStep > 1) {
            currentStep--;
            updateStepNavigation();
            highlightCurrentStep();
            scrollToStep(currentStep);
        } else if (e.key === 'ArrowRight' && currentStep < totalSteps) {
            currentStep++;
            updateStepNavigation();
            highlightCurrentStep();
            scrollToStep(currentStep);
        }
    });
    
    // Functions
    function updateStepNavigation() {
        currentStepSpan.textContent = currentStep;
        prevStepBtn.disabled = currentStep === 1;
        nextStepBtn.disabled = currentStep === totalSteps;
        showOnlyStep(currentStep);
        
        if (prevStepBtn) {
            prevStepBtn.innerHTML = prevStepBtn.disabled 
                ? '<i class="fas fa-chevron-left"></i> Sebelumnya'
                : '<i class="fas fa-chevron-left"></i> Sebelumnya';
        }
        
        if (nextStepBtn) {
            nextStepBtn.innerHTML = nextStepBtn.disabled 
                ? 'Selesai <i class="fas fa-chevron-right"></i>'
                : 'Selanjutnya <i class="fas fa-chevron-right"></i>';
            
            if (nextStepBtn.disabled) {
                nextStepBtn.addEventListener('click', function() {
                    showToast('Selamat! Anda telah menyelesaikan semua langkah.');
                });
            }
        }
    }
    
    function highlightCurrentStep() {
        // Remove highlight from all steps
        document.querySelectorAll('.instruction-step').forEach(step => {
            step.style.background = '#f8f9fa';
            step.style.borderColor = 'var(--primary)';
        });
        
        // Highlight current step
        const currentStepElement = document.querySelector(`.instruction-step[data-step="${currentStep}"]`);
        if (currentStepElement) {
            currentStepElement.style.display = 'block';
            currentStepElement.style.background = 'rgba(255, 107, 107, 0.1)';
            currentStepElement.style.borderColor = 'var(--primary)';
        }
    }
    
    function scrollToStep(step) {
        const stepElement = document.querySelector(`.instruction-step[data-step="${step}"]`);
        if (stepElement) {
            stepElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    function showOnlyStep(step) {
        document.querySelectorAll('.instruction-step').forEach(el => {
            el.style.display = (parseInt(el.dataset.step) === step) ? 'block' : 'none';
        });
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
        // Remove existing toast
        const existingToast = document.querySelector('.toast');
        if (existingToast) existingToast.remove();
        
        // Create new toast
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
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.2); }
            50% { transform: scale(0.95); }
            75% { transform: scale(1.1); }
        }
    `;
    document.head.appendChild(style);
});
</script>
