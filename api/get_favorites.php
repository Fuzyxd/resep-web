<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/database.php';

if (empty($_SESSION['user']) || empty($_SESSION['user']['uid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit;
}

$userUid = $_SESSION['user']['uid'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$limit = max(1, min(100, $limit));

try {
    $res = getUserFavorites($userUid, $limit);
    $favorites = [];
    if ($res && method_exists($res, 'fetch_assoc')) {
        while ($row = $res->fetch_assoc()) {
            $row['image_url'] = getRecipeImage($row, 'thumb');
            $favorites[] = [
                'id' => (int)$row['id'],
                'judul' => $row['judul'],
                'kategori' => $row['kategori'],
                'waktu' => (int)($row['waktu'] ?? 0),
                'porsi' => $row['porsi'] ?? null,
                'image_url' => $row['image_url'] ?? null,
                'tingkat_kesulitan' => $row['tingkat_kesulitan'] ?? null,
                'excerpt' => mb_substr(strip_tags($row['deskripsi'] ?? ''), 0, 140)
            ];
        }
    }

    echo json_encode(['success' => true, 'count' => count($favorites), 'favorites' => $favorites]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error', 'message' => $e->getMessage()]);
}
?>
