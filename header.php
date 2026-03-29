<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>Listeners Lounge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/listeners_lounge/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="/listeners_lounge/index.php" class="nav-logo">
        <span class="logo-icon">♪</span>
        <span class="logo-text">Listeners <em>Lounge</em></span>
    </a>

    <div class="nav-center">
        <form action="/listeners_lounge/search.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search albums or artists..." 
                   value="<?= isset($_GET['q']) ? h($_GET['q']) : '' ?>" class="search-input">
            <button type="submit" class="search-btn">⌕</button>
        </form>
    </div>

    <div class="nav-right">
        <div class="genre-dropdown">
            <button class="nav-btn genre-toggle">Genres ▾</button>
            <div class="genre-menu">
                <?php
                $genres = ['Electronic', 'R&B', 'Pop', 'Rock', 'Hip-Hop', 'Jazz', 'Country', 'Indie', 'Classical', 'Metal'];
                foreach ($genres as $genre):
                ?>
                <a href="/listeners_lounge/genre.php?genre=<?= urlencode($genre) ?>" class="genre-item"><?= h($genre) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (isLoggedIn()): ?>
            <a href="/listeners_lounge/account.php" class="nav-btn nav-btn--outline">My Account</a>
            <a href="/listeners_lounge/logout.php" class="nav-btn">Log Out</a>
        <?php else: ?>
            <a href="/listeners_lounge/auth.php" class="nav-btn nav-btn--outline">Login</a>
            <a href="/listeners_lounge/auth.php?mode=register" class="nav-btn nav-btn--filled">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (isset($_SESSION['flash'])): ?>
<div class="flash flash--<?= h($_SESSION['flash']['type']) ?>">
    <?= h($_SESSION['flash']['message']) ?>
</div>
<?php unset($_SESSION['flash']); endif; ?>
