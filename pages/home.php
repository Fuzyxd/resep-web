<?php
require_once __DIR__ . '/../includes/database.php';

// Get recipes for different sections (use image-aware helpers)
$featuredRecipes = getRecipesWithImages(6, 0);
$trendingRecipes = getRecipesWithImages(6, 6);
$categories = [
    ['name' => 'Makanan Berat', 'icon' => 'fas fa-utensils', 'count' => 12],
    ['name' => 'Makanan Ringan', 'icon' => 'fas fa-cookie-bite', 'count' => 8],
    ['name' => 'Kue & Roti', 'icon' => 'fas fa-birthday-cake', 'count' => 15],
    ['name' => 'Minuman', 'icon' => 'fas fa-glass-whiskey', 'count' => 10],
    ['name' => 'Sarapan', 'icon' => 'fas fa-egg', 'count' => 7],
    ['name' => 'Makanan Penutup', 'icon' => 'fas fa-ice-cream', 'count' => 9],
    ['name' => 'Makanan Sehat', 'icon' => 'fas fa-heartbeat', 'count' => 11],
    ['name' => 'Cepat Saji', 'icon' => 'fas fa-hamburger', 'count' => 5]
];

// Get stats (in real app, these would come from database)
$totalRecipes = 156;
$totalUsers = 2345;
$recipesTried = 7890;
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-container">
            <h1 class="hero-title">Temukan Resep Terbaik Nusantara</h1>
            <p class="hero-subtitle">
                Jelajahi ribuan resep masakan tradisional Indonesia dengan cita rasa autentik. 
                Dari makanan berat hingga minuman segar, semua ada di sini!
            </p>
            
            <div class="hero-search-container">
                <div class="hero-search">
                    <input type="text" 
                           id="heroSearch" 
                           placeholder="Cari resep (contoh: nasi goreng, sate, rendang...)"
                           aria-label="Cari resep">
                    <button id="heroSearchBtn">
                        <i class="fas fa-search"></i> Cari Resep
                    </button>
                </div>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number" id="recipeCount"><?= $totalRecipes ?></span>
                    <span class="stat-label">Resep</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="userCount"><?= number_format($totalUsers) ?></span>
                    <span class="stat-label">Pengguna</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="triedCount"><?= number_format($recipesTried) ?>+</span>
                    <span class="stat-label">Resep Dicoba</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Kenapa Memilih Kami?</h2>
            <p class="section-subtitle">
                Platform resep terlengkap dengan fitur-fitur terbaik untuk pengalaman memasak yang menyenangkan
            </p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3>Resep Teruji</h3>
                <p>Semua resep telah diuji oleh chef profesional untuk memastikan hasil yang sempurna</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Waktu Masak</h3>
                <p>Estimasi waktu masak yang akurat untuk membantu Anda merencanakan waktu dengan baik</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Favoritkan Resep</h3>
                <p>Simpan resep favorit Anda untuk akses cepat dan mudah kapan saja</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Akses resep dari smartphone atau tablet dengan tampilan yang optimal</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Jelajahi Kategori</h2>
            <p class="section-subtitle">
                Temukan resep berdasarkan kategori favorit Anda
            </p>
        </div>
        
        <div class="categories-grid" id="categoriesGrid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card" 
                     data-category="<?= strtolower(str_replace(' ', '-', $category['name'])) ?>">
                    <div class="category-icon">
                        <i class="<?= $category['icon'] ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                    <div class="category-count"><?= $category['count'] ?> Resep</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Recipes -->
<section class="trending-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Resep Pilihan Hari Ini</h2>
            <p class="section-subtitle">
                Resep terbaik yang direkomendasikan untuk Anda coba
            </p>
        </div>
        
        <div class="recipe-grid" id="featuredGrid">
            <?php 
            $count = 0;
            foreach ($featuredRecipes as $recipe): 
                $count++;
                $difficultyClass = '';
                switch(strtolower($recipe['tingkat_kesulitan'])) {
                    case 'mudah': $difficultyClass = 'difficulty-easy'; break;
                    case 'sedang': $difficultyClass = 'difficulty-medium'; break;
                    case 'sulit': $difficultyClass = 'difficulty-hard'; break;
                    default: $difficultyClass = 'difficulty-medium';
                }
            ?>
                <div class="recipe-card" 
                     data-category="<?= strtolower($recipe['kategori'] ?? 'lainnya') ?>"
                     data-difficulty="<?= $recipe['tingkat_kesulitan'] ?>">
                    
                    <?php if ($count <= 3): ?>
                        <div class="recipe-badge">
                            <i class="fas fa-star"></i> Pilihan Editor
                        </div>
                    <?php endif; ?>
                    
                    <div class="recipe-image-container">
                            <img src="<?= htmlspecialchars($recipe['image_url']) ?>" 
                                alt="<?= htmlspecialchars($recipe['judul']) ?>" 
                                class="recipe-image"
                                loading="lazy">
                        <div class="recipe-overlay">
                            <div class="recipe-time"><?= ($recipe['waktu'] ?? 0) ?> menit</div>
                        </div>
                    </div>
                    
                    <div class="recipe-content">
                        <h3 class="recipe-title"><?= htmlspecialchars($recipe['judul']) ?></h3>
                        <p class="recipe-description">
                            <?= htmlspecialchars(substr($recipe['deskripsi'], 0, 120)) ?>
                            <?= strlen($recipe['deskripsi']) > 120 ? '...' : '' ?>
                        </p>
                        
                        <div class="recipe-meta">
                            <div class="recipe-info">
                                <div class="recipe-info-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?= ($recipe['waktu'] ?? 0) ?> mnt</span>
                                </div>
                                <div class="recipe-info-item">
                                    <i class="fas fa-user-friends"></i>
                                    <span><?= $recipe['porsi'] ?> porsi</span>
                                </div>
                            </div>
                            
                            <span class="difficulty-badge <?= $difficultyClass ?>">
                                <?= ucfirst($recipe['tingkat_kesulitan']) ?>
                            </span>
                        </div>
                        
                        <div class="recipe-meta">
                            <div class="recipe-actions">
                                <button class="favorite-btn" 
                                        data-recipe-id="<?= $recipe['id'] ?>"
                                        data-tooltip="Tambahkan ke Favorit">
                                    <i class="far fa-heart"></i>
                                    <span class="tooltip">Tambahkan ke Favorit</span>
                                </button>
                                
                                <a href="?page=resep&id=<?= $recipe['id'] ?>" 
                                   class="view-btn">
                                    <i class="fas fa-eye"></i> Lihat Resep
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Mulai Petualangan Kuliner Anda</h2>
            <p class="cta-text">
                Bergabung dengan ribuan pengguna lainnya dan temukan dunia rasa baru. 
                Gratis, mudah, dan menyenangkan!
            </p>
            
            <div class="cta-buttons">
                <button class="cta-btn-primary" id="ctaExplore">
                    <i class="fas fa-utensils"></i> Jelajahi Resep
                </button>
                
                <?php if (!isset($_SESSION['user'])): ?>
                    <button class="cta-btn-secondary" id="ctaRegister">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Trending Recipes -->
<section class="trending-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Sedang Tren</h2>
            <p class="section-subtitle">
                Resep yang sedang populer di kalangan pengguna
            </p>
        </div>
        
        <div class="recipe-grid" id="trendingGrid">
            <?php foreach ($trendingRecipes as $recipe): 
                $difficultyClass = '';
                switch(strtolower($recipe['tingkat_kesulitan'])) {
                    case 'mudah': $difficultyClass = 'difficulty-easy'; break;
                    case 'sedang': $difficultyClass = 'difficulty-medium'; break;
                    case 'sulit': $difficultyClass = 'difficulty-hard'; break;
                }
            ?>
                <div class="recipe-card" 
                     data-category="<?= strtolower($recipe['kategori'] ?? 'lainnya') ?>">
                    
                    <div class="recipe-image-container">
                            <img src="<?= htmlspecialchars($recipe['image_url']) ?>" 
                                alt="<?= htmlspecialchars($recipe['judul']) ?>" 
                                class="recipe-image"
                                loading="lazy">
                    </div>
                    
                    <div class="recipe-content">
                        <h3 class="recipe-title"><?= htmlspecialchars($recipe['judul']) ?></h3>
                        <p class="recipe-description">
                            <?= htmlspecialchars(substr($recipe['deskripsi'], 0, 120)) ?>
                            <?= strlen($recipe['deskripsi']) > 120 ? '...' : '' ?>
                        </p>
                        
                        <div class="recipe-meta">
                            <div class="recipe-info">
                                <div class="recipe-info-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?= ($recipe['waktu'] ?? 0) ?> mnt</span>
                                </div>
                                <div class="recipe-info-item">
                                    <i class="fas fa-fire"></i>
                                    <span>32x Dicoba</span>
                                </div>
                            </div>
                            
                            <span class="difficulty-badge <?= $difficultyClass ?>">
                                <?= ucfirst($recipe['tingkat_kesulitan']) ?>
                            </span>
                        </div>
                        
                        <div class="recipe-meta">
                            <div class="recipe-actions">
                                <?php $is_fav = isset($_SESSION['user']) ? isFavorited($_SESSION['user']['uid'], $recipe['id']) : false; ?>
                                <button class="favorite-btn <?= $is_fav ? 'active' : '' ?>" data-recipe-id="<?= $recipe['id'] ?>">
                                    <i class="<?= $is_fav ? 'fas' : 'far' ?> fa-heart"></i>
                                    <span class="tooltip"><?= $is_fav ? 'Hapus dari Favorit' : 'Tambahkan ke Favorit' ?></span>
                                </button>
                                
                                <a href="?page=resep&id=<?= $recipe['id'] ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> Lihat Resep
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="load-more-section">
            <button class="load-more-btn" id="loadMore">
                <i class="fas fa-plus"></i> Muat Lebih Banyak Resep
            </button>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const heroSearch = document.getElementById('heroSearch');
    const heroSearchBtn = document.getElementById('heroSearchBtn');
    const categoriesGrid = document.getElementById('categoriesGrid');
    const categoryCards = document.querySelectorAll('.category-card');
    const recipeCards = document.querySelectorAll('.recipe-card');
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    const loadMoreBtn = document.getElementById('loadMore');
    const ctaExplore = document.getElementById('ctaExplore');
    const ctaRegister = document.getElementById('ctaRegister');
    
    // Animate numbers
    function animateNumber(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }
    
    // Initialize number animations
    const recipeCount = document.getElementById('recipeCount');
    const userCount = document.getElementById('userCount');
    const triedCount = document.getElementById('triedCount');
    
    if (recipeCount && userCount && triedCount) {
        // Wait for page to load
        setTimeout(() => {
            animateNumber(recipeCount, 156);
            animateNumber(userCount, 2345);
            animateNumber(triedCount, 7890);
        }, 500);
    }
    
    // Search functionality
    function performSearch() {
        const query = heroSearch.value.trim();
        if (query) {
            window.location.href = `?page=resep&search=${encodeURIComponent(query)}`;
        } else {
            heroSearch.focus();
        }
    }
    
    heroSearchBtn.addEventListener('click', performSearch);
    heroSearch.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') performSearch();
    });
    
    // Category filtering
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all cards
            categoryCards.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked card
            this.classList.add('active');
            
            const category = this.dataset.category;
            
            // Show all cards if 'all' category
            if (category === 'all') {
                recipeCards.forEach(card => {
                    card.style.display = 'block';
                });
                return;
            }
            
            // Filter recipes
            recipeCards.forEach(card => {
                if (card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // Favorite functionality
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            
            const recipeId = this.dataset.recipeId;
            const heartIcon = this.querySelector('i');
            const tooltip = this.querySelector('.tooltip');
            
            // Check if user is logged in
            <?php if (!isset($_SESSION['user'])): ?>
                alert('Silakan login terlebih dahulu untuk menambahkan ke favorit!');
                window.location.href = '?page=login';
                return;
            <?php endif; ?>

            // Disable button temporarily
            this.disabled = true;
            const originalCursor = this.style.cursor;
            this.style.cursor = 'wait';

            // Determine desired action based on current state
            const wantsToAdd = !this.classList.contains('active');

            // Show brief loading state
            const originalInner = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch('api/toggle_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        recipeId: recipeId,
                        action: wantsToAdd ? 'add' : 'remove'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    const favorited = !!result.favorited;

                    // Update UI based on server truth
                    if (favorited) {
                        this.classList.add('active');
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas');
                        if (tooltip) tooltip.textContent = 'Hapus dari Favorit';
                    } else {
                        this.classList.remove('active');
                        heartIcon.classList.remove('fas');
                        heartIcon.classList.add('far');
                        if (tooltip) tooltip.textContent = 'Tambahkan ke Favorit';
                    }

                    // Add animation via class
                    this.classList.remove('animate-heart');
                    void this.offsetWidth;
                    this.classList.add('animate-heart');
                    this.addEventListener('animationend', function handler() {
                        this.classList.remove('animate-heart');
                        this.removeEventListener('animationend', handler);
                    });

                    // Optionally refresh favorits list if present (e.g., favorits page open)
                    // We'll trigger a custom event so other scripts can handle updating
                    document.dispatchEvent(new CustomEvent('favorite:changed', {
                        detail: { recipeId: recipeId, favorited: favorited }
                    }));
                } else {
                    alert('Gagal memperbarui favorit. Silakan coba lagi.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memperbarui favorit. Silakan coba lagi.');
            } finally {
                // Restore button
                this.disabled = false;
                this.style.cursor = originalCursor;
                this.innerHTML = originalInner;
            }
        });
    });
    
    // Load more functionality
    let currentPage = 1;
    let isLoading = false;
    
    loadMoreBtn?.addEventListener('click', async function() {
        if (isLoading) return;
        
        isLoading = true;
        currentPage++;
        
        // Show loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
        this.disabled = true;
        
        try {
            const response = await fetch(`api/get_recipes.php?page=${currentPage}&limit=3`);
            const recipes = await response.json();
            
            if (recipes.length > 0) {
                // Add new recipes to grid
                const grid = document.getElementById('trendingGrid');
                recipes.forEach(recipe => {
                    const card = createRecipeCard(recipe);
                    grid.appendChild(card);
                });
                
                // Re-attach event listeners to new favorite buttons
                document.querySelectorAll('.recipe-card .favorite-btn').forEach(btn => {
                    btn.addEventListener('click', handleFavoriteClick);
                });
            } else {
                // No more recipes
                this.innerHTML = '<i class="fas fa-check"></i> Semua resep telah dimuat';
                this.style.opacity = '0.6';
                this.style.cursor = 'default';
                return;
            }
        } catch (error) {
            console.error('Error loading recipes:', error);
            alert('Gagal memuat resep. Silakan coba lagi.');
        } finally {
            // Reset button state
            this.innerHTML = originalText;
            this.disabled = false;
            isLoading = false;
        }
    });
    
    // CTA buttons
    ctaExplore?.addEventListener('click', () => {
        window.scrollTo({
            top: document.querySelector('.categories-section').offsetTop - 100,
            behavior: 'smooth'
        });
    });
    
    ctaRegister?.addEventListener('click', () => {
        window.location.href = '?page=register';
    });
    
    // Hover effects for recipe cards
    recipeCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '';
        });
    });
    
    // Handle favorite button click (for dynamically added cards)
    function handleFavoriteClick(e) {
        e.stopPropagation();
        
        const recipeId = this.dataset.recipeId;
        const heartIcon = this.querySelector('i');
        
        <?php if (!isset($_SESSION['user'])): ?>
            alert('Silakan login terlebih dahulu untuk menambahkan ke favorit!');
            window.location.href = '?page=login';
            return;
        <?php endif; ?>
        
        // Toggle favorite state
        this.classList.toggle('active');
        heartIcon.classList.toggle('far');
        heartIcon.classList.toggle('fas');
        
        // Add animation via class
        this.classList.remove('animate-heart');
        void this.offsetWidth;
        this.classList.add('animate-heart');
        this.addEventListener('animationend', function handler() {
            this.classList.remove('animate-heart');
            this.removeEventListener('animationend', handler);
        });
        
        // Send to server
        toggleFavoriteOnServer(recipeId, this.classList.contains('active'));
    }
    
    async function toggleFavoriteOnServer(recipeId, isFavorite) {
        try {
            const response = await fetch('api/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipeId: recipeId,
                    action: isFavorite ? 'add' : 'remove'
                })
            });
            
            const result = await response.json();
            
            if (!result.success) {
                console.error('Failed to update favorite:', result.error);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
    
    // Create recipe card element
    function createRecipeCard(recipe) {
        const difficultyClass = getDifficultyClass(recipe.tingkat_kesulitan);
        
        const card = document.createElement('div');
        card.className = 'recipe-card';
        card.dataset.category = recipe.kategori.toLowerCase();
        card.dataset.difficulty = recipe.tingkat_kesulitan;
        
        card.innerHTML = `
            <div class="recipe-image-container">
                 <img src="${recipe.image_url || 'assets/images/default-recipe.jpg'}" 
                     alt="${recipe.judul}" 
                     class="recipe-image"
                     loading="lazy" onerror="this.onerror=null;this.src='assets/images/default-recipe.jpg';">
            </div>
            
            <div class="recipe-content">
                <h3 class="recipe-title">${recipe.judul}</h3>
                <p class="recipe-description">
                    ${recipe.deskripsi.substring(0, 120)}${recipe.deskripsi.length > 120 ? '...' : ''}
                </p>
                
                <div class="recipe-meta">
                    <div class="recipe-info">
                        <div class="recipe-info-item">
                            <i class="fas fa-clock"></i>
                            <span>${recipe.waktu} mnt</span>
                        </div>
                        <div class="recipe-info-item">
                            <i class="fas fa-fire"></i>
                            <span>${Math.floor(Math.random() * 50) + 1}x Dicoba</span>
                        </div>
                    </div>
                    
                    <span class="difficulty-badge ${difficultyClass}">
                        ${recipe.tingkat_kesulitan}
                    </span>
                </div>
                
                <div class="recipe-meta">
                    <div class="recipe-actions">
                        <button class="favorite-btn" data-recipe-id="${recipe.id}">
                            <i class="far fa-heart"></i>
                            <span class="tooltip">Tambahkan ke Favorit</span>
                        </button>
                        
                        <a href="?page=resep&id=${recipe.id}" class="view-btn">
                            <i class="fas fa-eye"></i> Lihat Resep
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }
    
    function getDifficultyClass(level) {
        switch(level.toLowerCase()) {
            case 'mudah': return 'difficulty-easy';
            case 'sedang': return 'difficulty-medium';
            case 'sulit': return 'difficulty-hard';
            default: return 'difficulty-medium';
        }
    }
    
    // Initialize tooltips
    favoriteBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) {
                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) {
                tooltip.style.opacity = '0';
                tooltip.style.visibility = 'hidden';
            }
        });
    });
    
    // Add scroll animation
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.feature-card, .category-card, .recipe-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});
</script>