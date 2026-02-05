<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data && isset($data['uid'])) {
        $_SESSION['user'] = [
            'uid' => $data['uid'],
            'email' => $data['email'],
            'displayName' => $data['displayName'],
            'photoURL' => $data['photoURL']
        ];

        // Attempt to resolve or create a local user id and store it in session for FK-safe operations
        require_once __DIR__ . '/../includes/database.php';
        $localId = resolveLocalUserIdFromAuth($_SESSION['user']);
        if ($localId) {
            $_SESSION['user']['id'] = $localId;
        }

        echo json_encode(['success' => true, 'message' => 'Session updated', 'local_id' => $localId ?? null]);
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
