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
    if (isset($sessionUser['id']) && is_numeric($sessionUser['id'])) return (int)$sessionUser['id'];

    $candidates = ['uid', 'firebase_uid', 'auth_uid', 'user_uid', 'user'];
    $email = $sessionUser['email'] ?? null;
    $uid = $sessionUser['uid'] ?? null;

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

define('DB_FUNCTIONS_LOADED', true);
}
?>