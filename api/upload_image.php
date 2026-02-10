<?php
session_start();
require_once '../includes/database.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get recipe ID
$recipe_id = $_POST['recipe_id'] ?? null;
if (!$recipe_id) {
    echo json_encode(['success' => false, 'error' => 'Recipe ID required']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['gambar'];

function createImageResource($path, $mime) {
    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            return @imagecreatefromjpeg($path);
        case 'image/png':
            return @imagecreatefrompng($path);
        case 'image/gif':
            return @imagecreatefromgif($path);
        case 'image/webp':
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
        default:
            return false;
    }
}

function saveImageResource($image, $path, $mime) {
    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            return imagejpeg($image, $path, 82);
        case 'image/png':
            return imagepng($image, $path, 6);
        case 'image/gif':
            return imagegif($image, $path);
        case 'image/webp':
            return function_exists('imagewebp') ? imagewebp($image, $path, 82) : false;
        default:
            return false;
    }
}

function createThumbnail($srcPath, $destPath, $mime, $maxWidth, $maxHeight) {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }

    $src = createImageResource($srcPath, $mime);
    if (!$src) {
        return false;
    }

    $width = imagesx($src);
    $height = imagesy($src);
    if ($width <= 0 || $height <= 0) {
        imagedestroy($src);
        return false;
    }

    $scale = min($maxWidth / $width, $maxHeight / $height, 1);
    $newW = max(1, (int)floor($width * $scale));
    $newH = max(1, (int)floor($height * $scale));

    $thumb = imagecreatetruecolor($newW, $newH);
    if (in_array($mime, ['image/png', 'image/gif', 'image/webp'], true)) {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefilledrectangle($thumb, 0, 0, $newW, $newH, $transparent);
    }

    imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
    $saved = saveImageResource($thumb, $destPath, $mime);
    imagedestroy($src);
    imagedestroy($thumb);
    return $saved;
}

// Validate file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, WebP allowed']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'error' => 'File too large. Max 5MB']);
    exit;
}

// Create upload directory if not exists
$upload_dir = '../assets/images/uploads/recipes/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'recipe-' . $recipe_id . '-' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Create thumbnail (best-effort)
    $thumb_dir = '../assets/images/uploads/recipes/thumbs/';
    if (!is_dir($thumb_dir)) {
        mkdir($thumb_dir, 0755, true);
    }
    $thumb_path = $thumb_dir . $filename;
    $thumb_relative = 'assets/images/uploads/recipes/thumbs/' . $filename;
    $thumb_created = createThumbnail($filepath, $thumb_path, $file['type'], 480, 360);

    // Update database
    $relative_path = 'assets/images/uploads/recipes/' . $filename;
    
    $sql = "UPDATE resep SET 
            gambar = ?, 
            gambar_nama = ?, 
            gambar_path = ?, 
            gambar_size = ?, 
            gambar_type = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssisi",
        $relative_path,
        $filename,
        $filepath,
        $file['size'],
        $file['type'],
        $recipe_id
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'image_url' => $relative_path,
            'filename' => $filename,
            'thumb_url' => $thumb_created ? $thumb_relative : null
        ]);
    } else {
        // Delete uploaded file if database update fails
        unlink($filepath);
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
}
?>
