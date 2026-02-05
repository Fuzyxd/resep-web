<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/database.php';

// Simple file logger helper
function fav_log($message) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/favorites.log';
    $time = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$time] $message\n", FILE_APPEND | LOCK_EX);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$recipeId = isset($data['recipeId']) ? (int)$data['recipeId'] : 0;
$action = isset($data['action']) ? $data['action'] : null; // optional: 'add'|'remove'

if (empty($_SESSION['user']) || empty($_SESSION['user']['uid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    fav_log('unauthenticated request to toggle favorite');
    exit;
}

if (!$recipeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_recipe_id']);
    fav_log('invalid recipe id in request: ' . json_encode($data));
    exit;
}

$userUid = $_SESSION['user']['uid'];
try {
    // Respect explicit action if provided
    $current = isFavorited($userUid, $recipeId);
    $didChange = false;
    if ($action === 'add' && !$current) {
        $didChange = toggleFavorite($userUid, $recipeId);
    } elseif ($action === 'remove' && $current) {
        $didChange = toggleFavorite($userUid, $recipeId);
    } elseif ($action === null) {
        // toggle behavior
        $didChange = toggleFavorite($userUid, $recipeId);
    }

    // Re-check final state
    $favorited = isFavorited($userUid, $recipeId);

    fav_log("user={$userUid} recipe={$recipeId} action={$action} changed=" . ($didChange ? '1' : '0') . " fav=" . ($favorited ? '1' : '0'));

    // If caller requested an explicit action but database didn't change accordingly, report failure
    if ($action === 'add' && !$favorited) {
        http_response_code(500);
        fav_log("failed to add favorite for user={$userUid} recipe={$recipeId}");
        echo json_encode(['success' => false, 'error' => 'failed_to_add']);
        exit;
    }
    if ($action === 'remove' && $favorited) {
        http_response_code(500);
        fav_log("failed to remove favorite for user={$userUid} recipe={$recipeId}");
        echo json_encode(['success' => false, 'error' => 'failed_to_remove']);
        exit;
    }

    echo json_encode(['success' => true, 'favorited' => $favorited]);
} catch (Exception $e) {
    http_response_code(500);
    fav_log('error toggling favorite: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'server_error', 'message' => $e->getMessage()]);
}
?>