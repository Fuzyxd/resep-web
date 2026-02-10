<?php
$current_user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
if ($current_user && function_exists('hydrateSessionUser')) {
    $current_user = hydrateSessionUser($current_user);
    $_SESSION['user'] = $current_user;
}

function getFirstNameFromUser($user) {
    $name = '';
    if (is_array($user)) {
        $name = trim($user['displayName'] ?? '');
        if ($name === '') {
            $name = trim($user['display_name'] ?? '');
        }
    }
    if ($name !== '') {
        $parts = preg_split('/\s+/', $name);
        return $parts[0];
    }
    $email = is_array($user) ? ($user['email'] ?? '') : '';
    if ($email && strpos($email, '@') !== false) {
        return explode('@', $email)[0];
    }
    return 'User';
}
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="?page=home" class="logo">
                <i class="fas fa-utensils"></i>
                <span>Resep Nusantara</span>
            </a>
        </div>
        
        <div class="navbar-menu">
            <ul class="navbar-nav">
                <li><a href="?page=home" class="<?= ($_GET['page'] ?? 'home') == 'home' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Home
                </a></li>

                <li><a href="?page=all_resep" class="<?= ($_GET['page'] ?? '') == 'all_resep' ? 'active' : '' ?>">
                    <i class="fas fa-book-open"></i> Resep
                </a></li>
                
                <?php if ($current_user): ?>
                <li><a href="?page=favorit" class="<?= ($_GET['page'] ?? '') == 'favorit' ? 'active' : '' ?>">
                    <i class="fas fa-heart"></i> Favorit
                </a></li>
                <?php endif; ?>
                
                <li class="search-box">
                    <input type="text" id="searchInput" placeholder="Cari resep...">
                    <button id="searchButton"><i class="fas fa-search"></i></button>
                </li>
            </ul>
            
            <div class="navbar-auth">
                <?php if ($current_user): ?>
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <img src="<?= $current_user['photoURL'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($current_user['displayName'] ?? 'User') ?>" 
                                 alt="User" class="user-avatar">
                            <span><?= htmlspecialchars(getFirstNameFromUser($current_user)) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="?page=profile"><i class="fas fa-user"></i> Profil</a>
                            <a href="?page=favorit"><i class="fas fa-heart"></i> Favorit</a>
                            <div class="divider"></div>
                            <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="?page=login" class="btn btn-outline">Login</a>
                    <a href="?page=register" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
        
        <button class="navbar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>
