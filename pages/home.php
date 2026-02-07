    <?php
    require_once __DIR__ . '/../includes/database.php';

    // Get recipes for different sections (use image-aware helpers)
    $featuredRecipes = getRecipesWithImages(6, 0);
    
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
                        
                        <a class="recipe-image-container" href="?page=resep&id=<?= $recipe['id'] ?>">
                            <img src="<?= htmlspecialchars($recipe['image_url']) ?>" 
                                alt="<?= htmlspecialchars($recipe['judul']) ?>" 
                                class="recipe-image"
                                loading="lazy">
                            <div class="recipe-overlay">
                                <div class="recipe-time"><?= ($recipe['waktu'] ?? 0) ?> menit</div>
                            </div>
                        </a>
                        
                        <div class="recipe-content">
                            <h3 class="recipe-title"><?= htmlspecialchars($recipe['judul']) ?></h3>
                            <p class="recipe-description">
                                <?= htmlspecialchars(substr($recipe['deskripsi'], 0, 120)) ?>
                                <?= strlen($recipe['deskripsi']) > 120 ? '...' : '' ?>
                            </p>
                            
                            <div class="recipe-meta actions-row">
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
                                    <a href="?page=resep&id=<?= $recipe['id'] ?>" 
                                    class="view-btn">
                                        <i class="fas fa-eye"></i> Lihat Resep
                                    </a>

                                    <?php $is_fav = isset($_SESSION['user']) ? isFavorited($_SESSION['user']['uid'], $recipe['id']) : false; ?>
                                    <button class="favorite-btn <?= $is_fav ? 'active' : '' ?>"
                                            data-recipe-id="<?= $recipe['id'] ?>">
                                        <i class="<?= $is_fav ? 'fas' : 'far' ?> fa-heart"></i>
                                    </button>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const heroSearch = document.getElementById('heroSearch');
        const heroSearchBtn = document.getElementById('heroSearchBtn');
        const favoriteBtns = document.querySelectorAll('.favorite-btn');
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
        
        // Favorite functionality
        function updateFavoriteButton(btn, favorited) {
            const heartIcon = btn.querySelector('i');
            btn.classList.toggle('active', favorited);
            if (favorited) {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas');
                btn.setAttribute('data-tooltip', 'Hapus dari Favorit');
            } else {
                heartIcon.classList.remove('fas');
                heartIcon.classList.add('far');
                btn.setAttribute('data-tooltip', 'Tambahkan ke Favorit');
            }
        }

        favoriteBtns.forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.stopPropagation();

                const isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
                if (!isLoggedIn) {
                    alert('Silakan login terlebih dahulu untuk menambahkan ke favorit!');
                    return;
                }

                const recipeId = this.dataset.recipeId;
                const heartIcon = this.querySelector('i');
                const tooltip = this.querySelector('.tooltip');

                this.disabled = true;
                const originalCursor = this.style.cursor;
                this.style.cursor = 'wait';

                try {
                    const response = await fetch('api/toggle_favorite.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ recipeId: recipeId })
                    });
                    const result = await response.json();

                    if (result.success) {
                        const favorited = !!result.favorited;
                        updateFavoriteButton(this, favorited);

                        this.classList.remove('animate-heart');
                        void this.offsetWidth;
                        this.classList.add('animate-heart');
                        setTimeout(() => this.classList.remove('animate-heart'), 600);

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
                    this.disabled = false;
                    this.style.cursor = originalCursor;
                }
            });
        });

        // Keep all buttons in sync within the page
        document.addEventListener('favorite:changed', function(e) {
            const { recipeId, favorited } = e.detail;
            document.querySelectorAll(`.favorite-btn[data-recipe-id="${recipeId}"]`).forEach(btn => {
                updateFavoriteButton(btn, favorited);
            });
        });
        
        // CTA buttons
        ctaExplore?.addEventListener('click', () => {
            window.location.href = '?page=all_resep';
        });
        
        ctaRegister?.addEventListener('click', () => {
            window.location.href = '?page=register';
        });
        
        // Create recipe card element
        function createRecipeCard(recipe) {
            const difficultyClass = getDifficultyClass(recipe.tingkat_kesulitan);
            
            const card = document.createElement('div');
            card.className = 'recipe-card';
            card.dataset.category = recipe.kategori.toLowerCase();
            card.dataset.difficulty = recipe.tingkat_kesulitan;
            
            card.innerHTML = `
                <a class="recipe-image-container" href="?page=resep&id=${recipe.id}">
                    <img src="${recipe.image_url || 'assets/images/default-recipe.jpg'}" 
                        alt="${recipe.judul}" 
                        class="recipe-image"
                        loading="lazy" onerror="this.onerror=null;this.src='assets/images/default-recipe.jpg';">
                    <div class="recipe-overlay">
                        <div class="recipe-time">${recipe.waktu || 0} menit</div>
                    </div>
                </a>
                
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
                                <i class="fas fa-user-friends"></i>
                                <span>${recipe.porsi} porsi</span>
                            </div>
                        </div>
                        
                        <span class="difficulty-badge ${difficultyClass}">
                            ${recipe.tingkat_kesulitan}
                        </span>
                    </div>
                    
                    <div class="recipe-meta">
                        <div class="recipe-actions">
                            <a href="?page=resep&id=${recipe.id}" class="view-btn">
                                <i class="fas fa-eye"></i> Lihat Resep
                            </a>
                            
                            <button class="favorite-btn" data-recipe-id="${recipe.id}">
                                <i class="far fa-heart"></i>
                            </button>
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
        
        // Tooltips disabled per request
        
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
        document.querySelectorAll('.recipe-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    });
    </script>
