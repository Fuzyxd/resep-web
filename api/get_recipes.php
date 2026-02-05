<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/database.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$recipes = getRecipesWithImages($limit, $offset);

// Ensure numeric indexes are converted to safe JSON structure
echo json_encode(array_values($recipes));
?>