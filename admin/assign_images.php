<?php
session_start();
require_once '../includes/database.php';

// Get all recipes
$recipes = $conn->query("SELECT * FROM resep");

while ($recipe = $recipes->fetch_assoc()) {
    $image_found = false;
    
    // Cari gambar di folder uploads berdasarkan nama resep
    $recipe_slug = strtolower(preg_replace('/[^a-z0-9]/', '-', $recipe['judul']));
    $upload_dir = '../assets/images/uploads/recipes/';
    
    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    foreach ($extensions as $ext) {
        $filename = $recipe_slug . '.' . $ext;
        $filepath = $upload_dir . $filename;
        
        if (file_exists($filepath)) {
            // Update database
            $relative_path = 'assets/images/uploads/recipes/' . $filename;
            $sql = "UPDATE resep SET gambar = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $relative_path, $recipe['id']);
            $stmt->execute();
            
            echo "Assigned image to: {$recipe['judul']} - {$filename}<br>";
            $image_found = true;
            break;
        }
    }
    
    if (!$image_found) {
        echo "No image found for: {$recipe['judul']}<br>";
    }
}

echo "<br><strong>Done!</strong>";
?>