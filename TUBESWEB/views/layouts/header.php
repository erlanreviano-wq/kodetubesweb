<?php
// views/layouts/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// current route fragment, ex: "admin/dashboard" atau "recipe/my"
$currentUrl = $_GET['url'] ?? '';

// versi css/js untuk cache-busting
$assetVersion = '2';

// helper sederhana
function is_admin_page(string $url): bool {
    return strpos($url, 'admin') === 0;
}
function is_auth_page(string $url): bool {
    return in_array($url, ['login', 'register', 'auth'], true)
        || strpos($url, 'auth') === 0;
}
function is_home_page(string $url): bool {
    return $url === '' || $url === 'home';
}
function is_recipe_page(string $url): bool {
    return strpos($url, 'recipe') === 0 || strpos($url, 'favorites') === 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Resep Sehat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap HANYA UNTUK HOME -->
    <?php if (is_home_page($currentUrl)): ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
              rel="stylesheet"
              integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
              crossorigin="anonymous">
    <?php endif; ?>

    <!-- Base / theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/theme.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/base.css?v=<?= $assetVersion ?>">

    <!-- Nav -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/nav.css?v=<?= $assetVersion ?>">

    <!-- Page-specific CSS -->
    <?php if (is_home_page($currentUrl)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/home.css?v=<?= $assetVersion ?>">
    <?php endif; ?>

    <?php if (is_recipe_page($currentUrl)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/recipes.css?v=<?= $assetVersion ?>">
    <?php endif; ?>

    <?php if (is_auth_page($currentUrl)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/auth.css?v=<?= $assetVersion ?>">
    <?php endif; ?>

    <!-- Admin -->
    <?php if (is_admin_page($currentUrl)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css?v=<?= $assetVersion ?>">
    <?php endif; ?>

    <!-- JS -->
    <script src="<?= BASE_URL ?>/public/js/app.js?v=<?= $assetVersion ?>" defer></script>

    <!-- Bootstrap JS HANYA UNTUK HOME -->
    <?php if (is_home_page($currentUrl)): ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
                integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
                crossorigin="anonymous" defer></script>
    <?php endif; ?>
</head>

<body>
<header class="site-header">
    <div class="nav-inner">
        <a href="<?= BASE_URL ?>/index.php" class="brand">
            Resep<span>Sehat</span>
        </a>

        <nav class="nav-links">
            <a href="<?= BASE_URL ?>/index.php"
               class="nav-link <?= ($currentUrl === '' || $currentUrl === 'home' ? 'active' : '') ?>">
                Beranda
            </a>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/index.php?url=recipe/my"
                   class="nav-link <?= (strpos($currentUrl, 'recipe/my') === 0 ? 'active' : '') ?>">
                    Resep Saya
                </a>

                <a href="<?= BASE_URL ?>/index.php?url=favorites/index"
                   class="nav-link <?= (strpos($currentUrl, 'favorites') === 0 ? 'active' : '') ?>">
                    Favorit
                </a>

                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/index.php?url=admin/dashboard"
                       class="nav-link <?= (strpos($currentUrl, 'admin') === 0 ? 'active' : '') ?>">
                        Admin
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="nav-auth">
            <?php if (empty($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/index.php?url=login"
                   class="btn-ghost <?= ($currentUrl === 'login' ? 'active' : '') ?>">
                    Login
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=register"
                   class="btn-main <?= ($currentUrl === 'register' ? 'active' : '') ?>">
                    Daftar
                </a>
            <?php else: ?>
                <div class="user-menu" style="display:flex;gap:10px;align-items:center;">
                    <span class="nav-hello">
                        Hi, <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
                    </span>
                    <a href="<?= BASE_URL ?>/index.php?url=logout" class="btn-main btn-main--small">
                        Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="site-main" style="max-width:1280px; margin:100px auto 60px; padding:0 16px;">