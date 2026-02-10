<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && isset($data['uid'])) {
        require_once __DIR__ . '/../includes/database.php';

        $_SESSION['user'] = [
            'uid' => $data['uid'],
            'email' => $data['email'] ?? null,
            'displayName' => $data['displayName'] ?? null,
            'photoURL' => $data['photoURL'] ?? null
        ];

        // Resolve local user id for FK-safe operations
        $localId = resolveLocalUserIdFromAuth($_SESSION['user']);
        if ($localId) {
            $_SESSION['user']['id'] = $localId;
        }

        // Hydrate session with local DB values (if any)
        if (function_exists('hydrateSessionUser')) {
            $_SESSION['user'] = hydrateSessionUser($_SESSION['user']);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Session updated',
            'local_id' => $localId ?? null,
            'user' => [
                'displayName' => $_SESSION['user']['displayName'] ?? null,
                'photoURL' => $_SESSION['user']['photoURL'] ?? null,
                'email' => $_SESSION['user']['email'] ?? null
            ]
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out']);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
