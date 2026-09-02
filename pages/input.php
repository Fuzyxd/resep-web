<?php
$currentUser = $_SESSION['user'] ?? null;
if ($currentUser && function_exists('hydrateSessionUser')) {
    $currentUser = hydrateSessionUser($currentUser);
    $_SESSION['user'] = $currentUser;
}

$currentEmail = is_array($currentUser) ? strtolower(trim((string)($currentUser['email'] ?? ''))) : '';
$allowedInputEmails = [
    'rayhanfuzy@gmail.com',
    'andromedafap01@gmail.com',
    'andromedafap01@gmail.com'
];
$canAccessInput = in_array($currentEmail, $allowedInputEmails, true);

$formData = [
    'judul' => '',
    'deskripsi' => '',
    'bahan' => '',
    'langkah' => '',
    'waktu' => '',
    'porsi' => '',
    'tingkat_kesulitan' => 'Sedang',
    'kategori' => '',
    'gambar_url' => '',
    'kalori' => '',
    'protein' => '',
    'karbohidrat' => '',
    'lemak' => ''
];

function slugifyRecipeTitle($title) {
    $slug = strtolower(trim((string)$title));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim((string)$slug, '-');
    return $slug !== '' ? $slug : 'resep';
}

function uploadRecipeImageFromInput($judul, $file, &$errorMessage = '') {
    if (!isset($file) || !isset($file['error'])) {
        $errorMessage = 'File gambar tidak ditemukan.';
        return '';
    }

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Upload gambar gagal. Silakan coba lagi.';
        return '';
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errorMessage = 'File upload tidak valid.';
        return '';
    }

    $maxSize = 5 * 1024 * 1024;
    if ((int)$file['size'] <= 0 || (int)$file['size'] > $maxSize) {
        $errorMessage = 'Ukuran gambar harus antara 1 byte sampai 5MB.';
        return '';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif'
    ];

    if (!isset($allowedMimes[$mime])) {
        $errorMessage = 'Format gambar tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.';
        return '';
    }

    $uploadDirAbsolute = __DIR__ . '/../assets/images/uploads/recipes/';
    if (!is_dir($uploadDirAbsolute) && !mkdir($uploadDirAbsolute, 0755, true)) {
        $errorMessage = 'Folder upload tidak bisa dibuat.';
        return '';
    }

    $baseSlug = slugifyRecipeTitle($judul);
    $extension = $allowedMimes[$mime];
    $finalName = $baseSlug . '.' . $extension;
    $counter = 1;

    while (file_exists($uploadDirAbsolute . $finalName)) {
        $counter++;
        $finalName = $baseSlug . '-' . $counter . '.' . $extension;
    }

    $destination = $uploadDirAbsolute . $finalName;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errorMessage = 'Gagal menyimpan file gambar ke server.';
        return '';
    }

    return 'assets/images/uploads/recipes/' . $finalName;
}

$errors = [];
$success = '';

if ($canAccessInput && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim((string)($_POST[$key] ?? ''));
    }

    if ($formData['judul'] === '') $errors[] = 'Judul resep wajib diisi.';
    if ($formData['bahan'] === '') $errors[] = 'Bahan wajib diisi.';
    if ($formData['langkah'] === '') $errors[] = 'Langkah memasak wajib diisi.';
    if ($formData['kategori'] === '') $errors[] = 'Kategori wajib diisi.';
    if (!in_array($formData['tingkat_kesulitan'], ['Mudah', 'Sedang', 'Sulit'], true)) {
        $errors[] = 'Tingkat kesulitan tidak valid.';
    }

    if ($formData['waktu'] === '' || filter_var($formData['waktu'], FILTER_VALIDATE_INT) === false || (int)$formData['waktu'] <= 0) {
        $errors[] = 'Waktu memasak harus angka lebih dari 0.';
    }
    if ($formData['porsi'] === '' || filter_var($formData['porsi'], FILTER_VALIDATE_INT) === false || (int)$formData['porsi'] <= 0) {
        $errors[] = 'Porsi harus angka lebih dari 0.';
    }
    if ($formData['kalori'] === '' || filter_var($formData['kalori'], FILTER_VALIDATE_INT) === false || (int)$formData['kalori'] < 0) {
        $errors[] = 'Kalori harus berupa angka 0 atau lebih.';
    }

    foreach (['protein', 'karbohidrat', 'lemak'] as $nutrient) {
        if ($formData[$nutrient] === '' || !is_numeric($formData[$nutrient]) || (float)$formData[$nutrient] < 0) {
            $errors[] = ucfirst($nutrient) . ' harus berupa angka 0 atau lebih.';
        }
    }

    if (!isset($_FILES['gambar_file']) || (int)($_FILES['gambar_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Gambar resep wajib diupload.';
    }

    if (empty($errors)) {
        $judul = $formData['judul'];
        $deskripsi = $formData['deskripsi'];
        $bahan = $formData['bahan'];
        $langkah = $formData['langkah'];
        $waktu = (int)$formData['waktu'];
        $porsi = (int)$formData['porsi'];
        $tingkat_kesulitan = $formData['tingkat_kesulitan'];
        $kategori = $formData['kategori'];
        $gambar_url = '';
        $kalori = (int)$formData['kalori'];
        $protein = (float)$formData['protein'];
        $karbohidrat = (float)$formData['karbohidrat'];
        $lemak = (float)$formData['lemak'];

        $uploadError = '';
        $uploadedImagePath = uploadRecipeImageFromInput($judul, $_FILES['gambar_file'], $uploadError);
        if ($uploadedImagePath === '') {
            $errors[] = $uploadError !== '' ? $uploadError : 'Upload gambar gagal.';
        } else {
            $gambar_url = $uploadedImagePath;
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO resep (judul, deskripsi, bahan, langkah, waktu, porsi, tingkat_kesulitan, kategori, gambar_url, kalori, protein, karbohidrat, lemak, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $errors[] = 'Gagal menyiapkan query database.';
        } else {
            $stmt->bind_param(
                "ssssiisssiddd",
                $judul,
                $deskripsi,
                $bahan,
                $langkah,
                $waktu,
                $porsi,
                $tingkat_kesulitan,
                $kategori,
                $gambar_url,
                $kalori,
                $protein,
                $karbohidrat,
                $lemak
            );

            if ($stmt->execute()) {
                $success = 'Resep berhasil ditambahkan.';
                foreach ($formData as $key => $value) {
                    $formData[$key] = ($key === 'tingkat_kesulitan') ? 'Sedang' : '';
                }
            } else {
                if (!empty($gambar_url) && file_exists(__DIR__ . '/../' . $gambar_url)) {
                    @unlink(__DIR__ . '/../' . $gambar_url);
                }
                $errors[] = 'Gagal menyimpan resep: ' . $stmt->error;
            }
        }
    }
}

$categories = function_exists('getUniqueCategories') ? getUniqueCategories() : [];
?>

<section class="input-page">
    <div class="container">
        <?php if (!$canAccessInput): ?>
            <?php http_response_code(403); ?>
            <div class="input-alert input-alert-error">
                <i class="fas fa-lock"></i>
                <div>
                    <h3>Akses Ditolak</h3>
                    <p>Halaman Input hanya tersedia untuk akun dengan email tertentu.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="input-hero">
                <div>
                    <h1>Input Resep Baru</h1>
                    <p>Tambahkan resep baru sesuai struktur database `resep` agar langsung bisa tampil di aplikasi.</p>
                </div>
                <a href="?page=all_resep" class="input-back-btn"><i class="fas fa-arrow-left"></i> Kembali ke Resep</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="input-alert input-alert-error">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>
                        <h3>Data belum valid</h3>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="input-alert input-alert-success">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <h3>Berhasil</h3>
                        <p><?= htmlspecialchars($success) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form class="input-form" method="post" action="?page=input" enctype="multipart/form-data">
                <div class="input-card">
                    <h2><i class="fas fa-book-open"></i> Informasi Resep</h2>
                    <div class="input-grid">
                        <label>
                            <span>Judul Resep</span>
                            <input type="text" name="judul" maxlength="200" required value="<?= htmlspecialchars($formData['judul']) ?>">
                        </label>
                        <label>
                            <span>Kategori</span>
                            <input type="text" name="kategori" list="categoryList" required value="<?= htmlspecialchars($formData['kategori']) ?>">
                            <datalist id="categoryList">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </label>
                        <label>
                            <span>Tingkat Kesulitan</span>
                            <select name="tingkat_kesulitan" required>
                                <?php foreach (['Mudah', 'Sedang', 'Sulit'] as $option): ?>
                                    <option value="<?= $option ?>" <?= $formData['tingkat_kesulitan'] === $option ? 'selected' : '' ?>>
                                        <?= $option ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="image-upload-block">
                        <span class="image-upload-label">Gambar Resep</span>
                        <label for="gambarFile" class="image-upload-dropzone" id="imageDropzone">
                            <input type="file" id="gambarFile" name="gambar_file" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" required>
                            <div class="dropzone-icon"><i class="fas fa-image"></i></div>
                            <p class="dropzone-title">Klik untuk pilih gambar atau drag-and-drop ke sini</p>
                            <p class="dropzone-subtitle">Format: JPG, PNG, WEBP, GIF. Maksimal 5MB. Nama file otomatis mengikuti judul resep.</p>
                            <div class="dropzone-filename" id="dropzoneFilename">Belum ada file dipilih</div>
                        </label>
                    </div>
                    <label class="input-full">
                        <span>Deskripsi</span>
                        <textarea name="deskripsi" rows="3"><?= htmlspecialchars($formData['deskripsi']) ?></textarea>
                    </label>
                </div>

                <div class="input-card">
                    <h2><i class="fas fa-utensils"></i> Detail Memasak</h2>
                    <div class="input-grid">
                        <label>
                            <span>Waktu (menit)</span>
                            <input type="number" name="waktu" min="1" required value="<?= htmlspecialchars($formData['waktu']) ?>">
                        </label>
                        <label>
                            <span>Porsi</span>
                            <input type="number" name="porsi" min="1" required value="<?= htmlspecialchars($formData['porsi']) ?>">
                        </label>
                    </div>
                    <label class="input-full">
                        <span>Bahan</span>
                        <textarea name="bahan" rows="4" required placeholder="Pisahkan dengan koma atau baris baru"><?= htmlspecialchars($formData['bahan']) ?></textarea>
                    </label>
                    <label class="input-full">
                        <span>Langkah Memasak</span>
                        <textarea name="langkah" rows="6" required placeholder="Contoh: 1. Tumis bawang..."><?= htmlspecialchars($formData['langkah']) ?></textarea>
                    </label>
                </div>

                <div class="input-card">
                    <h2><i class="fas fa-chart-pie"></i> Informasi Nutrisi</h2>
                    <div class="input-grid nutrition-grid">
                        <label>
                            <span>Kalori (kkal)</span>
                            <input type="number" name="kalori" min="0" required value="<?= htmlspecialchars($formData['kalori']) ?>">
                        </label>
                        <label>
                            <span>Protein (g)</span>
                            <input type="number" name="protein" min="0" step="0.1" required value="<?= htmlspecialchars($formData['protein']) ?>">
                        </label>
                        <label>
                            <span>Karbohidrat (g)</span>
                            <input type="number" name="karbohidrat" min="0" step="0.1" required value="<?= htmlspecialchars($formData['karbohidrat']) ?>">
                        </label>
                        <label>
                            <span>Lemak (g)</span>
                            <input type="number" name="lemak" min="0" step="0.1" required value="<?= htmlspecialchars($formData['lemak']) ?>">
                        </label>
                    </div>
                </div>

                <div class="input-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Resep</button>
                    <a href="?page=all_resep" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<script>
(function () {
    const fileInput = document.getElementById('gambarFile');
    const dropzone = document.getElementById('imageDropzone');
    const fileNameLabel = document.getElementById('dropzoneFilename');

    if (!fileInput || !dropzone || !fileNameLabel) return;

    const updateName = () => {
        const file = fileInput.files && fileInput.files[0];
        fileNameLabel.textContent = file ? file.name : 'Belum ada file dipilih';
    };

    fileInput.addEventListener('change', updateName);

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('is-dragging');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('is-dragging');
        });
    });

    dropzone.addEventListener('drop', (event) => {
        if (!event.dataTransfer || !event.dataTransfer.files || event.dataTransfer.files.length === 0) return;
        fileInput.files = event.dataTransfer.files;
        updateName();
    });
})();
</script>
