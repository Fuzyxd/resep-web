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
            'filename' => $filename
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