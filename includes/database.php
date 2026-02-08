<?php
// Database initialization and helper functions
// Load config if DB constants aren't defined
if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
    $configPath = __DIR__ . '/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

// Provide sensible defaults only when constants are missing
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'resep_db');

// Initialize database connection only once
if (!defined('DB_INITIALIZED')) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    define('DB_INITIALIZED', true);
}

// Define functions only once
if (!defined('DB_FUNCTIONS_LOADED')) {

function getRecipes($limit = 10, $offset = 0) {
    global $conn;
    $sql = "SELECT * FROM resep ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

function getRecipeById($id) {
    global $conn;
    $sql = "SELECT * FROM resep WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getRecipesByCategory($category, $limit = 10) {
    global $conn;
    $sql = "SELECT * FROM resep WHERE kategori = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $category, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

function getFilteredRecipes($sql, $params = [], $types = "") {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        if (empty($row['gambar_url'])) {
            $row['gambar_url'] = getRecipeImage($row);
        }
        $recipes[] = $row;
    }
    return $recipes;
}

function getUniqueCategories() {
    global $conn;
    $categories = [];
    $sql = "SELECT DISTINCT kategori FROM resep WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['kategori'];
        }
    }
    return $categories;
}

function getFeaturedRecipes($limit = 3) {
    global $conn;
    $sql = "SELECT * FROM resep ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        if (empty($row['gambar_url'])) {
            $row['gambar_url'] = getRecipeImage($row);
        }
        $recipes[] = $row;
    }
    return $recipes;
}

function getTotalRecipesCount() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as total FROM resep");
    if ($result && ($row = $result->fetch_assoc())) {
        return (int)$row['total'];
    }
    return 0;
}

function getRecipeCountByCategory($category) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM resep WHERE kategori = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && ($row = $result->fetch_assoc())) {
        return (int)$row['total'];
    }
    return 0;
}

function getRecipeCountByDifficulty($difficulty) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM resep WHERE tingkat_kesulitan = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param("s", $difficulty);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && ($row = $result->fetch_assoc())) {
        return (int)$row['total'];
    }
    return 0;
}

function getRecipeCountByTime($timeKey) {
    global $conn;
    switch ($timeKey) {
        case 'cepat':
            $sql = "SELECT COUNT(*) as total FROM resep WHERE waktu <= 30";
            break;
        case 'sedang':
            $sql = "SELECT COUNT(*) as total FROM resep WHERE waktu > 30 AND waktu <= 60";
            break;
        case 'lama':
            $sql = "SELECT COUNT(*) as total FROM resep WHERE waktu > 60";
            break;
        default:
            return 0;
    }
    $result = $conn->query($sql);
    if ($result && ($row = $result->fetch_assoc())) {
        return (int)$row['total'];
    }
    return 0;
}

function detectFavoritUserColumn() {
    global $conn;
    static $cached = null;
    if ($cached !== null) return $cached;
    $candidates = ['user_uid', 'user_id', 'uid', 'user'];
    foreach ($candidates as $col) {
        $colEsc = $conn->real_escape_string($col);
        $res = $conn->query("SHOW COLUMNS FROM favorit LIKE '$colEsc'");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $type = strtolower($row['Type'] ?? '');
            $bind = (strpos($type, 'int') !== false) ? 'i' : 's';
            $cached = ['name' => $col, 'bind' => $bind];
            return $cached;
        }
    }
    $cached = null;
    return null;
}

// Resolve numeric local user id from session user info (Firebase uid or email). Returns int or null
function resolveLocalUserIdFromAuth($sessionUser) {
    global $conn;
    if (!$sessionUser) return null;

    // If session user already has a numeric local id, return it
    if (is_array($sessionUser) && isset($sessionUser['id']) && is_numeric($sessionUser['id'])) return (int)$sessionUser['id'];

    $candidates = ['uid', 'firebase_uid', 'auth_uid', 'user_uid', 'user'];
    $email = is_array($sessionUser) ? ($sessionUser['email'] ?? null) : null;
    $uid = is_array($sessionUser) ? ($sessionUser['uid'] ?? null) : (is_string($sessionUser) ? $sessionUser : null);
    if (is_string($uid) && trim($uid) === '') $uid = null;

    // Try to match by known UID columns
    foreach ($candidates as $col) {
        $colEsc = $conn->real_escape_string($col);
        $res = $conn->query("SHOW COLUMNS FROM users LIKE '$colEsc'");
        if ($res && $res->num_rows > 0 && $uid) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE `$colEsc` = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $uid);
                $stmt->execute();
                $r = $stmt->get_result();
                if ($row = $r->fetch_assoc()) {
                    return (int)$row['id'];
                }
            }
        }
    }

    // Fallback: try to match by email
    if ($email) {
        $res = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
        if ($res && $res->num_rows > 0) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $r = $stmt->get_result();
                if ($row = $r->fetch_assoc()) {
                    return (int)$row['id'];
                }
            }
        }
    }

    // If we reach here, no existing local user found. Attempt to create one if users table supports uid/email insert.
    // Check minimal columns exist
    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'id'");
    $canInsert = false;
    if ($check && $check->num_rows > 0) {
        // check that either 'uid' or 'email' columns exist to insert
        $uidExists = $conn->query("SHOW COLUMNS FROM users LIKE 'uid'");
        $emailExists = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
        if (($uidExists && $uidExists->num_rows > 0) || ($emailExists && $emailExists->num_rows > 0)) {
            $canInsert = true;
        }
    }

    if ($canInsert && ($uid || $email)) {
        // If firebase_uid column exists but no uid provided, avoid insert that would default to ''
        $firebaseCols = $conn->query("SHOW COLUMNS FROM users LIKE 'firebase_uid'");
        if ($firebaseCols && $firebaseCols->num_rows > 0 && !$uid) {
            return null;
        }
        // Build insert columns dynamically, verify each exists to avoid unknown column errors
        $cols = [];
        $placeholders = [];
        $types = '';
        $values = [];

        // Verify uid column exists before using it
        if ($uid) {
            $uidCols = $conn->query("SHOW COLUMNS FROM users LIKE 'uid'");
            if ($uidCols && $uidCols->num_rows > 0) {
                $cols[] = '`uid`'; $placeholders[] = '?'; $types .= 's'; $values[] = $uid;
            }
            $firebaseCols = $conn->query("SHOW COLUMNS FROM users LIKE 'firebase_uid'");
            if ($firebaseCols && $firebaseCols->num_rows > 0) {
                $cols[] = '`firebase_uid`'; $placeholders[] = '?'; $types .= 's'; $values[] = $uid;
            }
        }

        if ($email) {
            $emailCols = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
            if ($emailCols && $emailCols->num_rows > 0) {
                $cols[] = '`email`'; $placeholders[] = '?'; $types .= 's'; $values[] = $email;
            }
        }

        if (isset($sessionUser['displayName'])) {
            $nameCols = $conn->query("SHOW COLUMNS FROM users LIKE 'displayName'");
            if ($nameCols && $nameCols->num_rows > 0) {
                $cols[] = '`displayName`'; $placeholders[] = '?'; $types .= 's'; $values[] = $sessionUser['displayName'];
            }
        }

        if (isset($sessionUser['displayName'])) {
            $nameCols = $conn->query("SHOW COLUMNS FROM users LIKE 'display_name'");
            if ($nameCols && $nameCols->num_rows > 0) {
                $cols[] = '`display_name`'; $placeholders[] = '?'; $types .= 's'; $values[] = $sessionUser['displayName'];
            }
        }

        if (isset($sessionUser['photoURL'])) {
            $photoCols = $conn->query("SHOW COLUMNS FROM users LIKE 'photo_url'");
            if ($photoCols && $photoCols->num_rows > 0) {
                $cols[] = '`photo_url`'; $placeholders[] = '?'; $types .= 's'; $values[] = $sessionUser['photoURL'];
            }
        }

        if (count($cols) === 0) {
            // Nothing to insert (no matching columns)
            return null;
        }

        $sql = "INSERT INTO users (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // dynamic bind
            $stmt->bind_param($types, ...$values);
            if ($stmt->execute()) {
                return $conn->insert_id;
            } else {
                // Log failure to insert
                @file_put_contents(__DIR__ . '/../logs/favorites.log', "[".date('Y-m-d H:i:s')."] failed to insert local user: " . $stmt->error . "\n", FILE_APPEND | LOCK_EX);
            }
        } else {
            @file_put_contents(__DIR__ . '/../logs/favorites.log', "[".date('Y-m-d H:i:s')."] failed to prepare insert user SQL: " . $conn->error . "\n", FILE_APPEND | LOCK_EX);
        }
    }

    return null;
}

// Sync display name and photo from session into users table
function syncUserProfileFromSession($sessionUser) {
    global $conn;
    if (!$sessionUser) return null;

    $user_id = resolveLocalUserIdFromAuth($sessionUser);
    if (!$user_id) return null;

    $displayName = $sessionUser['displayName'] ?? ($sessionUser['display_name'] ?? null);
    $photoUrl = $sessionUser['photoURL'] ?? ($sessionUser['photo_url'] ?? null);

    $sets = [];
    $types = '';
    $values = [];

    if ($displayName) {
        $nameCols = $conn->query("SHOW COLUMNS FROM users LIKE 'display_name'");
        if ($nameCols && $nameCols->num_rows > 0) {
            $sets[] = "display_name = ?";
            $types .= 's';
            $values[] = $displayName;
        }
    }

    if ($photoUrl) {
        $photoCols = $conn->query("SHOW COLUMNS FROM users LIKE 'photo_url'");
        if ($photoCols && $photoCols->num_rows > 0) {
            $sets[] = "photo_url = ?";
            $types .= 's';
            $values[] = $photoUrl;
        }
    }

    if (count($sets) === 0) return $user_id;

    $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return $user_id;
    $types .= 'i';
    $values[] = (int)$user_id;
    $stmt->bind_param($types, ...$values);
    $stmt->execute();

    return $user_id;
}

function isFavorited($user_uid, $resep_id) {
    global $conn;
    $colInfo = detectFavoritUserColumn();
    if (!$colInfo) {
        // Table doesn't have a recognizable user column; cannot determine favorites
        return false;
    }

    // If column expects integer id, resolve local user id
    $bindUser = $user_uid;
    if ($colInfo['bind'] === 'i') {
        // Accept either numeric or session user array/string
        if (!is_numeric($user_uid)) {
            // if session user array was passed, resolve
            if (is_array($user_uid)) {
                $resolved = resolveLocalUserIdFromAuth($user_uid);
            } else {
                // try session lookup
                $resolved = resolveLocalUserIdFromAuth(isset($_SESSION['user']) ? $_SESSION['user'] : null);
            }
            if (!$resolved) return false;
            $bindUser = $resolved;
        } else {
            $bindUser = (int)$user_uid;
        }
    }

    $sql = "SELECT id FROM favorit WHERE {$colInfo['name']} = ? AND resep_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $types = $colInfo['bind'] . 'i';
    $stmt->bind_param($types, $bindUser, $resep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function toggleFavorite($user_uid, $resep_id) {
    global $conn;
    $colInfo = detectFavoritUserColumn();
    if (!$colInfo) {
        return false;
    }

    // Resolve user id if needed
    $bindUser = $user_uid;
    if ($colInfo['bind'] === 'i') {
        if (!is_numeric($user_uid)) {
            if (is_array($user_uid)) {
                $resolved = resolveLocalUserIdFromAuth($user_uid);
            } else {
                $resolved = resolveLocalUserIdFromAuth(isset($_SESSION['user']) ? $_SESSION['user'] : null);
            }
            if (!$resolved) return false;
            $bindUser = $resolved;
        } else {
            $bindUser = (int)$user_uid;
        }
    }

    // Check if already favorited
    if (isFavorited($bindUser, $resep_id)) {
        // Remove from favorites
        $sql = "DELETE FROM favorit WHERE {$colInfo['name']} = ? AND resep_id = ?";
    } else {
        // Add to favorites
        $sql = "INSERT INTO favorit ({$colInfo['name']}, resep_id) VALUES (?, ?)";
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $types = $colInfo['bind'] . 'i';
    $stmt->bind_param($types, $bindUser, $resep_id);
    return $stmt->execute();
}

// Comments (stored in rating table, rating can be NULL)
function addRecipeComment($user_uid, $resep_id, $comment) {
    global $conn;
    $comment = trim((string)$comment);
    if ($comment === '' || !$resep_id) return false;

    // Ensure rating table exists with needed columns
    $res = $conn->query("SHOW COLUMNS FROM rating LIKE 'user_id'");
    if (!$res || $res->num_rows === 0) return false;
    $res = $conn->query("SHOW COLUMNS FROM rating LIKE 'resep_id'");
    if (!$res || $res->num_rows === 0) return false;
    $res = $conn->query("SHOW COLUMNS FROM rating LIKE 'komentar'");
    if (!$res || $res->num_rows === 0) return false;

    $user_id = $user_uid;
    if (!is_numeric($user_uid)) {
        $resolved = syncUserProfileFromSession(is_array($user_uid) ? $user_uid : (isset($_SESSION['user']) ? $_SESSION['user'] : null));
        if (!$resolved) return false;
        $user_id = (int)$resolved;
    } else {
        $user_id = (int)$user_uid;
    }

    $sql = "INSERT INTO rating (user_id, resep_id, rating, komentar, created_at) VALUES (?, ?, NULL, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param("iis", $user_id, $resep_id, $comment);
    return $stmt->execute();
}

function getRecipeComments($resep_id, $limit = 50) {
    global $conn;
    if (!$resep_id) return [];

    $res = $conn->query("SHOW COLUMNS FROM rating LIKE 'komentar'");
    if (!$res || $res->num_rows === 0) return [];

    $sql = "SELECT r.komentar, r.created_at, u.display_name, u.photo_url, u.email
            FROM rating r
            LEFT JOIN users u ON u.id = r.user_id
            WHERE r.resep_id = ? AND r.komentar IS NOT NULL AND r.komentar <> ''
            ORDER BY r.id DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("ii", $resep_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $name = $row['display_name'] ?: ($row['email'] ? explode('@', $row['email'])[0] : 'Pengguna');
        $row['display_name'] = $name;
        $comments[] = $row;
    }
    return $comments;
}

function getUserFavorites($user_uid, $limit = 20) {
    global $conn;
    $colInfo = detectFavoritUserColumn();
    if (!$colInfo) {
        // Return empty result set if we can't determine user column
        return $conn->query("SELECT r.* FROM resep r WHERE 1=0");
    }

    // Resolve user id if integer column
    $bindUser = $user_uid;
    if ($colInfo['bind'] === 'i') {
        if (!is_numeric($user_uid)) {
            if (is_array($user_uid)) {
                $resolved = resolveLocalUserIdFromAuth($user_uid);
            } else {
                $resolved = resolveLocalUserIdFromAuth(isset($_SESSION['user']) ? $_SESSION['user'] : null);
            }
            if (!$resolved) {
                // Return empty result if we can't map to local user
                return $conn->query("SELECT r.* FROM resep r WHERE 1=0");
            }
            $bindUser = $resolved;
        } else {
            $bindUser = (int)$user_uid;
        }
    }

    // Determine suitable order column
    $orderCol = 'f.created_at';
    $res = $conn->query("SHOW COLUMNS FROM favorit LIKE 'created_at'");
    if (!$res || $res->num_rows === 0) {
        // fallback to recipe creation time if available
        $res2 = $conn->query("SHOW COLUMNS FROM resep LIKE 'created_at'");
        if ($res2 && $res2->num_rows > 0) {
            $orderCol = 'r.created_at';
        } else {
            $orderCol = 'f.id';
        }
    }
    $sql = "SELECT r.* FROM resep r 
            JOIN favorit f ON r.id = f.resep_id 
            WHERE f.{$colInfo['name']} = ? 
            ORDER BY {$orderCol} DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // Fallback: return empty result
        return $conn->query("SELECT r.* FROM resep r WHERE 1=0");
    }
    $types = $colInfo['bind'] . 'i';
    $stmt->bind_param($types, $bindUser, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Add helper to resolve recipe images with fallbacks
function getRecipeImage($recipe) {
    // Jika ada gambar_url di database dan file ada, gunakan itu
    if (!empty($recipe['gambar_url']) && file_exists($recipe['gambar_url'])) {
        return $recipe['gambar_url'];
    }

    // Coba cari gambar berdasarkan slug judul di folder uploads
    $recipe_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $recipe['judul'] ?? ''));
    $upload_dir = 'assets/images/uploads/recipes/';

    $possible_files = [
        $upload_dir . $recipe_slug . '.jpg',
        $upload_dir . $recipe_slug . '.jpeg',
        $upload_dir . $recipe_slug . '.png',
        $upload_dir . $recipe_slug . '.webp',
    ];

    foreach ($possible_files as $file) {
        if (file_exists($file)) {
            // Update database with found path (best-effort)
            // Do not attempt to update the database here — some schemas
            // may not have a `gambar_url` column which would cause errors.
            // Return the found file path so caller can use it.

            return $file;
        }
    }

    // Fallback berdasarkan kategori
    $category_images = [
        'makanan berat' => 'assets/images/categories/main-dish.jpg',
        'makanan ringan' => 'assets/images/categories/snack.jpg',
        'kue' => 'assets/images/categories/cake.jpg',
        'minuman' => 'assets/images/categories/drink.jpg',
        'sarapan' => 'assets/images/categories/breakfast.jpg',
        'makanan penutup' => 'assets/images/categories/dessert.jpg'
    ];

    $category = strtolower($recipe['kategori'] ?? '');
    if (isset($category_images[$category]) && file_exists($category_images[$category])) {
        return $category_images[$category];
    }

    // Ultimate fallback
    return 'assets/images/default-recipe.jpg';
}

// Update existing getRecipes function to include resolved images
function getRecipesWithImages($limit = 10, $offset = 0) {
    global $conn;
    $sql = "SELECT * FROM resep ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = getRecipeImage($row);
        $recipes[] = $row;
    }

    return $recipes;
}

// Function to get single recipe with resolved image
function getRecipeWithImage($id) {
    global $conn;
    $sql = "SELECT * FROM resep WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $row['image_url'] = getRecipeImage($row);
        return $row;
    }

    return null;
}

// Profile functions
function getUserById($user_uid) {
    global $conn;
    
    // Try firebase_uid first
    $sql = "SELECT * FROM users WHERE firebase_uid = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $user_uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }
    
    return null;
}

function getUserStats($user_uid) {
    global $conn;
    
    // Resolve local user id (needed for numeric user_id columns)
    $user_id = resolveLocalUserIdFromAuth($user_uid);
    
    if (!$user_id) {
        return [
            'total_recipes' => 0,
            'total_favorites' => 0,
            'recipes_tried' => 0,
            'average_rating' => 0.0
        ];
    }
    
    // Count total recipes (if we have a user_id column in resep table)
    $total_recipes = 0;
    $res = $conn->query("SHOW COLUMNS FROM resep LIKE 'user_id'");
    if ($res && $res->num_rows > 0) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM resep WHERE user_id = $user_id");
        if ($count_result) {
            $row = $count_result->fetch_assoc();
            $total_recipes = $row['count'] ?? 0;
        }
    }
    
    // Count total favorites
    $colInfo = detectFavoritUserColumn();
    $total_favorites = 0;
    if ($colInfo) {
        $bindUser = null;
        if ($colInfo['bind'] === 'i') {
            $bindUser = (int)$user_id;
        } else {
            if (is_array($user_uid)) {
                $bindUser = $user_uid['uid'] ?? ($user_uid['email'] ?? '');
            } else {
                $bindUser = is_string($user_uid) ? $user_uid : '';
            }
            if ($bindUser === '' && isset($_SESSION['user'])) {
                $bindUser = $_SESSION['user']['uid'] ?? ($_SESSION['user']['email'] ?? '');
            }
        }
        if ($bindUser !== null && $bindUser !== '') {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM favorit WHERE {$colInfo['name']} = ?");
            if ($stmt) {
                $types = $colInfo['bind'];
                $stmt->bind_param($types, $bindUser);
                $stmt->execute();
                $count_result = $stmt->get_result();
                if ($count_result) {
                    $row = $count_result->fetch_assoc();
                    $total_favorites = $row['count'] ?? 0;
                }
            }
        }
    }
    
    // Count recipes tried (average rating count)
    $recipes_tried = 0;
    $res = $conn->query("SHOW COLUMNS FROM rating LIKE 'user_id'");
    if ($res && $res->num_rows > 0) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM rating WHERE user_id = $user_id");
        if ($count_result) {
            $row = $count_result->fetch_assoc();
            $recipes_tried = $row['count'] ?? 0;
        }
    }
    
    // Average rating
    $average_rating = 0.0;
    $res = $conn->query("SHOW COLUMNS FROM rating LIKE 'rating'");
    if ($res && $res->num_rows > 0) {
        $avg_result = $conn->query("SELECT AVG(rating) as avg_rating FROM rating WHERE user_id = $user_id");
        if ($avg_result) {
            $row = $avg_result->fetch_assoc();
            $average_rating = round($row['avg_rating'] ?? 0, 1);
        }
    }
    
    return [
        'total_recipes' => $total_recipes,
        'total_favorites' => $total_favorites,
        'recipes_tried' => $recipes_tried,
        'average_rating' => $average_rating
    ];
}

function getUserFavoriteRecipes($user_uid, $limit = 6) {
    global $conn;
    
    // Get local user id
    $user_id = null;
    $sql = "SELECT id FROM users WHERE firebase_uid = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $user_uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];
        }
    }
    
    if (!$user_id) {
        return [];
    }
    
    $colInfo = detectFavoritUserColumn();
    if (!$colInfo) {
        return [];
    }
    
    $sql = "SELECT r.* FROM resep r 
            JOIN favorit f ON r.id = f.resep_id 
            WHERE f.{$colInfo['name']} = ? 
            ORDER BY f.id DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param('ii', $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = getRecipeImage($row);
        $recipes[] = $row;
    }
    
    return $recipes;
}

function getUserRecipes($user_uid, $limit = 6) {
    global $conn;
    
    // For now, return empty array if user recipes table doesn't exist
    // This would need to be implemented if you have a user_id column in resep table
    $res = $conn->query("SHOW COLUMNS FROM resep LIKE 'user_id'");
    if (!$res || $res->num_rows === 0) {
        return [];
    }
    
    // Get local user id
    $user_id = null;
    $sql = "SELECT id FROM users WHERE firebase_uid = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $user_uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];
        }
    }
    
    if (!$user_id) {
        return [];
    }
    
    $sql = "SELECT r.* FROM resep r WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param('ii', $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = getRecipeImage($row);
        $row['views'] = 0; // Placeholder
        $row['favorite_count'] = 0; // Placeholder
        $row['status'] = 'published'; // Placeholder
        $recipes[] = $row;
    }
    
    return $recipes;
}

function updateUserProfile($user_uid, $fullname, $bio) {
    global $conn;
    
    $sql = "UPDATE users SET display_name = ?, created_at = created_at WHERE firebase_uid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param('ss', $fullname, $user_uid);
    return $stmt->execute();
}

function changeUserPassword($user_uid, $current_password, $new_password) {
    // This would require password field in users table which currently doesn't exist
    // For Firebase-based auth, password changes should be done via Firebase
    // Return true to not block the UI, but in reality this needs Firebase integration
    return false;
}

function uploadUserAvatar($user_uid, $file) {
    global $conn;
    
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'error' => 'File tidak valid'];
    }
    
    // Create uploads directory if not exists
    $upload_dir = __DIR__ . '/../assets/images/uploads/avatars/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Tipe file tidak didukung. Gunakan JPG, PNG, atau WebP.'];
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_uid . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    $relative_path = 'assets/images/uploads/avatars/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Gagal mengupload file'];
    }
    
    // Update database
    $sql = "UPDATE users SET photo_url = ? WHERE firebase_uid = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ss', $relative_path, $user_uid);
        $stmt->execute();
    }
    
    return ['success' => true, 'path' => $relative_path];
}

define('DB_FUNCTIONS_LOADED', true);
}
?>
