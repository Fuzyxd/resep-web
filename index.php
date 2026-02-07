<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep Nusantara - Beranda</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/home.css?v=<?= filemtime(__DIR__ . '/assets/css/home.css') ?>">
    <link rel="stylesheet" href="assets/css/all_resep.css?v=<?= filemtime(__DIR__ . '/assets/css/all_resep.css') ?>">
    <?php if ($page === 'profile'): ?>
        <link rel="stylesheet" href="assets/css/profile.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main>
    <?php
        $allowed_pages = ['home', 'resep', 'favorit', 'profile', 'login', 'register', 'all_resep'];
        
        if (in_array($page, $allowed_pages)) {
            if ($page == 'home') {
                include 'pages/home.php';
            } elseif ($page == 'resep') {
                include 'pages/resep.php';
            } elseif ($page == 'favorit') {
                include 'pages/favorit.php';
            } elseif ($page == 'profile') {
                include 'pages/profile.php';
            } elseif ($page == 'login') {
                include 'auth/login.php';
            } elseif ($page == 'register') {
                include 'auth/register.php';
            } elseif ($page == 'all_resep') {
                include 'pages/all_resep.php';
            }
        } else {
            include 'pages/home.php';
        }
        ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Firebase SDK -->
    <script type="module" src="assets/js/firebase-config.js"></script>
    <script type="module" src="assets/js/auth.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
