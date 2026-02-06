<?php
require_once __DIR__ . '/../includes/database.php';

// Filters
$selectedCategory = $_GET['kategori'] ?? '';
$selectedDifficulty = $_GET['kesulitan'] ?? '';
$selectedTime = $_GET['waktu'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');

// Pagination
$pageNum = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$pageNum = max(1, $pageNum);
$limit = 12;
$offset = ($pageNum - 1) * $limit;

// Build query
$where = [];
$params = [];
$types = '';

if ($selectedCategory !== '') {
    $where[] = 'kategori = ?';
    $params[] = $selectedCategory;
    $types .= 's';
}

if ($selectedDifficulty !== '') {
    $where[] = 'tingkat_kesulitan = ?';
    $params[] = $selectedDifficulty;
    $types .= 's';
}

if ($selectedTime !== '') {
    if ($selectedTime === 'cepat') {
        $where[] = 'waktu <= 30';
    } elseif ($selectedTime === 'sedang') {
        $where[] = 'waktu > 30 AND waktu <= 60';
    } elseif ($selectedTime === 'lama') {
        $where[] = 'waktu > 60';
    }
}

if ($searchQuery !== '') {
    $where[] = '(judul LIKE ? OR deskripsi LIKE ?)';
    $like = '%' . $searchQuery . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$whereSql = count($where) > 0 ? (' WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT * FROM resep" . $whereSql . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$recipes = getFilteredRecipes($sql, $params, $types);

// Count total with same filters (without limit/offset)
$countSql = "SELECT COUNT(*) as total FROM resep" . $whereSql;
$totalRecipes = 0;
$stmt = $conn->prepare($countSql);
if ($stmt) {
    if (!empty($types)) {
        // Remove last two types (limit, offset)
        $countTypes = substr($types, 0, max(0, strlen($types) - 2));
        $countParams = array_slice($params, 0, max(0, count($params) - 2));
        if ($countTypes !== '' && !empty($countParams)) {
            $stmt->bind_param($countTypes, ...$countParams);
        }
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $totalRecipes = (int) $row['total'];
    }
}
$totalPages = (int) ceil($totalRecipes / $limit);

$categories = getUniqueCategories();
?>

<section class="all-resep-page">
    <div class="container">
        <div class="all-resep-header">
            <form class="all-resep-search" method="get">
                <input type="hidden" name="page" value="all_resep">
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari resep, bahan, atau daerah...">
                <button type="submit"><i class="fas fa-search"></i> Cari</button>
            </form>
        </div>

        <div class="all-resep-layout">
            <aside class="filter-sidebar">
                <form class="filter-form" method="get" id="filterForm">
                    <input type="hidden" name="page" value="all_resep">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">

                    <div class="filter-block">
                        <div class="filter-title"><i class="fas fa-tag"></i> Kategori</div>
                        <div class="filter-list">
                            <label class="filter-item">
                                <input type="radio" name="kategori" value="" <?= $selectedCategory === '' ? 'checked' : '' ?>>
                                <span>Semua</span>
                                <span class="filter-count"><?= getTotalRecipesCount() ?></span>
                            </label>
                            <?php foreach ($categories as $category): ?>
                                <label class="filter-item">
                                    <input type="radio" name="kategori" value="<?= htmlspecialchars($category) ?>" <?= $selectedCategory === $category ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars(ucwords($category)) ?></span>
                                    <span class="filter-count"><?= getRecipeCountByCategory($category) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-block">
                        <div class="filter-title"><i class="fas fa-signal"></i> Tingkat Kesulitan</div>
                        <div class="filter-list">
                            <label class="filter-item">
                                <input type="radio" name="kesulitan" value="" <?= $selectedDifficulty === '' ? 'checked' : '' ?>>
                                <span>Semua</span>
                                <span class="filter-count"><?= getTotalRecipesCount() ?></span>
                            </label>
                            <label class="filter-item">
                                <input type="radio" name="kesulitan" value="mudah" <?= $selectedDifficulty === 'mudah' ? 'checked' : '' ?>>
                                <span>Mudah</span>
                                <span class="filter-count"><?= getRecipeCountByDifficulty('mudah') ?></span>
                            </label>
                            <label class="filter-item">
                                <input type="radio" name="kesulitan" value="sedang" <?= $selectedDifficulty === 'sedang' ? 'checked' : '' ?>>
                                <span>Sedang</span>
                                <span class="filter-count"><?= getRecipeCountByDifficulty('sedang') ?></span>
                            </label>
                            <label class="filter-item">
                                <input type="radio" name="kesulitan" value="sulit" <?= $selectedDifficulty === 'sulit' ? 'checked' : '' ?>>
                                <span>Sulit</span>
                                <span class="filter-count"><?= getRecipeCountByDifficulty('sulit') ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-block">
                        <div class="filter-title"><i class="fas fa-clock"></i> Waktu Memasak</div>
                        <div class="filter-list">
                            <label class="filter-item">
                                <input type="radio" name="waktu" value="" <?= $selectedTime === '' ? 'checked' : '' ?>>
                                <span>Semua</span>
                                <span class="filter-count"><?= getTotalRecipesCount() ?></span>
                            </label>
                            <label class="filter-item">
                                <input type="radio" name="waktu" value="cepat" <?= $selectedTime === 'cepat' ? 'checked' : '' ?>>
                                <span>Cepat (≤ 30 menit)</span>
                                <span class="filter-count"><?= getRecipeCountByTime('cepat') ?></span>
                            </label>
                            <label class="filter-item">
                                <input type="radio" name="waktu" value="sedang" <?= $selectedTime === 'sedang' ? 'checked' : '' ?>>
                                <span>Sedang (30-60 menit)</span>
                                <span class="filter-count"><?= getRecipeCountByTime('sedang') ?></span>
                            </label>
                            <label class="filter-item">
                                <input type="radio" name="waktu" value="lama" <?= $selectedTime === 'lama' ? 'checked' : '' ?>>
                                <span>Lama (> 60 menit)</span>
                                <span class="filter-count"><?= getRecipeCountByTime('lama') ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <a class="filter-reset" href="?page=all_resep">Reset</a>
                    </div>
                </form>
            </aside>

            <section class="all-resep-content">
                <div class="all-resep-meta">
                    <span><strong><?= $totalRecipes ?></strong> resep ditemukan</span>
                    <?php if ($searchQuery !== '' || $selectedCategory !== '' || $selectedDifficulty !== '' || $selectedTime !== ''): ?>
                        <span class="meta-note">Menampilkan hasil filter</span>
                    <?php else: ?>
                        <span class="meta-note">Menampilkan semua resep terbaru</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($recipes)): ?>
                    <div class="recipe-grid">
                        <?php foreach ($recipes as $recipe): 
                            $difficultyClass = '';
                            switch (strtolower($recipe['tingkat_kesulitan'])) {
                                case 'mudah': $difficultyClass = 'difficulty-easy'; break;
                                case 'sedang': $difficultyClass = 'difficulty-medium'; break;
                                case 'sulit': $difficultyClass = 'difficulty-hard'; break;
                                default: $difficultyClass = 'difficulty-medium'; break;
                            }
                        ?>
                        <div class="recipe-card" data-category="<?= strtolower($recipe['kategori'] ?? 'lainnya') ?>">
                            <div class="recipe-image-container">
                                <?php $imageSrc = !empty($recipe['gambar_url']) ? $recipe['gambar_url'] : getRecipeImage($recipe); ?>
                                <img src="<?= htmlspecialchars($imageSrc) ?>"
                                    alt="<?= htmlspecialchars($recipe['judul']) ?>"
                                    class="recipe-image"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='assets/images/default-recipe.jpg';">
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
                                        <?php $is_fav = isset($_SESSION['user']) ? isFavorited($_SESSION['user']['uid'], $recipe['id']) : false; ?>
                                        <button class="favorite-btn <?= $is_fav ? 'active' : '' ?>"
                                                data-recipe-id="<?= $recipe['id'] ?>"
                                                data-tooltip="<?= $is_fav ? 'Hapus dari Favorit' : 'Tambahkan ke Favorit' ?>">
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

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                                $baseParams = [
                                    'page' => 'all_resep',
                                    'kategori' => $selectedCategory,
                                    'kesulitan' => $selectedDifficulty,
                                    'waktu' => $selectedTime,
                                    'q' => $searchQuery
                                ];
                            ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php $baseParams['p'] = $i; ?>
                                <a class="page-link <?= $i === $pageNum ? 'active' : '' ?>"
                                    href="?<?= http_build_query($baseParams) ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>Resep tidak ditemukan</h3>
                        <p>Coba ubah filter atau kata kunci pencarian.</p>
                        <a class="filter-reset" href="?page=all_resep">Lihat Semua Resep</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    filterForm.querySelectorAll('input[type="radio"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            filterForm.submit();
        });
    });
});
</script>
