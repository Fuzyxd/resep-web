<?php
session_start();
require_once '../includes/database.php';

// Check admin access
if (!isset($_SESSION['user'])) {
    header('Location: ../?page=login');
    exit;
}

// Get all recipes without images
$sql = "SELECT * FROM resep WHERE (gambar IS NULL OR gambar = '') ORDER BY created_at DESC";
$recipes = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gambar Resep</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>Upload Gambar Resep</h1>
        
        <div class="recipes-grid">
            <?php while ($recipe = $recipes->fetch_assoc()): ?>
            <div class="recipe-upload-card">
                <h3><?= htmlspecialchars($recipe['judul']) ?></h3>
                <p>Kategori: <?= htmlspecialchars($recipe['kategori']) ?></p>
                
                <form class="upload-form" data-recipe-id="<?= $recipe['id'] ?>">
                    <div class="form-group">
                        <input type="file" 
                               name="gambar" 
                               accept="image/*" 
                               required
                               class="file-input">
                    </div>
                    
                    <div class="preview-container">
                        <img src="" alt="Preview" class="image-preview" style="display: none;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                    
                    <div class="upload-status"></div>
                </form>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <style>
    .recipes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .recipe-upload-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: 2px solid #f0f0f0;
    }
    
    .recipe-upload-card h3 {
        margin-bottom: 0.5rem;
        color: var(--dark);
    }
    
    .recipe-upload-card p {
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .file-input {
        width: 100%;
        padding: 10px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        margin-bottom: 1rem;
        cursor: pointer;
    }
    
    .preview-container {
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .image-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
    }
    
    .upload-status {
        margin-top: 1rem;
        padding: 10px;
        border-radius: 6px;
        display: none;
    }
    
    .upload-status.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        display: block;
    }
    
    .upload-status.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        display: block;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                const preview = this.closest('.upload-form').querySelector('.image-preview');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none';
                }
            });
        });
        
        // Form submission
        document.querySelectorAll('.upload-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const recipeId = this.dataset.recipeId;
                const formData = new FormData(this);
                formData.append('recipe_id', recipeId);
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                const statusDiv = this.querySelector('.upload-status');
                
                // Show loading
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                submitBtn.disabled = true;
                
                try {
                    const response = await fetch('../api/upload_image.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        statusDiv.textContent = 'Gambar berhasil diupload!';
                        statusDiv.className = 'upload-status success';
                        
                        // Update preview with new image
                        const preview = this.querySelector('.image-preview');
                        preview.src = '../' + result.image_url;
                        
                        // Clear file input
                        this.querySelector('.file-input').value = '';
                    } else {
                        statusDiv.textContent = 'Error: ' + result.error;
                        statusDiv.className = 'upload-status error';
                    }
                } catch (error) {
                    statusDiv.textContent = 'Terjadi kesalahan: ' + error.message;
                    statusDiv.className = 'upload-status error';
                } finally {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Auto hide message after 5 seconds
                    setTimeout(() => {
                        statusDiv.style.display = 'none';
                    }, 5000);
                }
            });
        });
    });
    </script>
</body>
</html>