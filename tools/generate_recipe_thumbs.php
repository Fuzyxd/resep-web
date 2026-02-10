<?php
// One-time thumbnail generator for existing recipe images.
// Run via browser: /resep-web/tools/generate_recipe_thumbs.php

$source_dir = __DIR__ . '/../assets/images/uploads/recipes/';
$thumb_dir = __DIR__ . '/../assets/images/uploads/recipes/thumbs/';

if (!is_dir($source_dir)) {
    die('Source directory not found.');
}
if (!is_dir($thumb_dir)) {
    mkdir($thumb_dir, 0755, true);
}

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

$files = glob($source_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
$total = 0;
$created = 0;
$skipped = 0;
$failed = 0;

foreach ($files as $file) {
    $total++;
    $filename = basename($file);
    $dest = $thumb_dir . $filename;
    if (file_exists($dest)) {
        $skipped++;
        continue;
    }
    $mime = mime_content_type($file);
    $ok = createThumbnail($file, $dest, $mime, 480, 360);
    if ($ok) {
        $created++;
    } else {
        $failed++;
    }
}

header('Content-Type: text/plain; charset=UTF-8');
echo "Total: {$total}\n";
echo "Created: {$created}\n";
echo "Skipped: {$skipped}\n";
echo "Failed: {$failed}\n";
