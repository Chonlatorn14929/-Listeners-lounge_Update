<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/config.php';

$conn = getConnection();

// get 8 random albums
$featuredResult = $conn->query("
    SELECT a.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
    FROM albums a
    LEFT JOIN reviews r ON a.id = r.album_id
    GROUP BY a.id
    ORDER BY RAND()
    LIMIT 8
");
$featured = $featuredResult->fetch_all(MYSQLI_ASSOC);

// get recently reviewed albums
$recentResult = $conn->query("
    SELECT a.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
    FROM albums a
    JOIN reviews r ON a.id = r.album_id
    GROUP BY a.id
    ORDER BY MAX(r.created_at) DESC
    LIMIT 4
");
$recentReviewed = $recentResult->fetch_all(MYSQLI_ASSOC);

// get genres
$genreResult = $conn->query("SELECT genre, COUNT(*) as count FROM albums GROUP BY genre ORDER BY count DESC");
$genres = $genreResult->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- hero section -->
<div class="hero">
    <div class="hero-eyebrow">🎵 Music Album Collection</div>
    <h1>Welcome to <em>Listeners Lounge</em></h1>
    <p>Browse and review your favourite music albums.</p>
    <?php if (!isLoggedIn()): ?>
        <a href="/listeners_lounge/auth.php?mode=register" class="hero-cta">Create Account</a>
    <?php else: ?>
        <a href="/listeners_lounge/search.php" class="hero-cta">Browse All Albums</a>
    <?php endif; ?>
</div>

<!-- featured albums -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">Featured Albums</h2>
        <a href="/listeners_lounge/search.php" class="section-link">View all</a>
    </div>
    <div class="album-grid album-grid--large">
        <?php foreach ($featured as $album):
            $avg = $album['avg_rating'] ? round($album['avg_rating'], 1) : null;
        ?>
        <a href="/listeners_lounge/album.php?id=<?= $album['id'] ?>" class="album-card">
            <div class="album-cover">
                <?php if (!empty($album['cover_image'])): ?>
                    <img src="/listeners_lounge/assets/images/<?= h($album['cover_image']) ?>"
                         alt="<?= h($album['title']) ?>"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <span style="color:#666666;">🎵</span>
                <?php endif; ?>
            </div>
            <div class="album-card-info">
                <div class="album-card-title"><?= h($album['title']) ?></div>
                <div class="album-card-artist"><?= h($album['artist']) ?></div>
                <div class="album-card-meta">
                    <span class="album-card-genre"><?= h($album['genre']) ?></span>
                    <?php if ($avg): ?>
                    <span class="rating-score">★ <?= $avg ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- browse by genre -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">Browse by Genre</h2>
        <select class="genre-select"
                onchange="if(this.value) window.location='/listeners_lounge/genre.php?genre='+this.value">
            <option value="">Select a genre...</option>
            <?php foreach ($genres as $g): ?>
            <option value="<?= urlencode($g['genre']) ?>">
                <?= h($g['genre']) ?> (<?= $g['count'] ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px;">
        <?php foreach ($genres as $g): ?>
        <a href="/listeners_lounge/genre.php?genre=<?= urlencode($g['genre']) ?>"
           style="background-color: #222222; border: 1px solid #444444; padding: 7px 16px;
                  border-radius: 4px; font-size: 14px; color: #eeeeee;">
            <?= h($g['genre']) ?>
            <span style="color: #4caf50; font-size: 12px;">(<?= $g['count'] ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- recently reviewed -->
<?php if (!empty($recentReviewed)): ?>
<div class="section">
    <div class="section-header">
        <h2 class="section-title">Recently Reviewed</h2>
    </div>
    <div class="album-grid">
        <?php foreach ($recentReviewed as $album): ?>
        <a href="/listeners_lounge/album.php?id=<?= $album['id'] ?>" class="album-card">
            <div class="album-cover">
                <?php if (!empty($album['cover_image'])): ?>
                    <img src="/listeners_lounge/assets/images/<?= h($album['cover_image']) ?>"
                         alt="<?= h($album['title']) ?>"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <span style="color:#666666;">🎵</span>
                <?php endif; ?>
            </div>
            <div class="album-card-info">
                <div class="album-card-title"><?= h($album['title']) ?></div>
                <div class="album-card-artist"><?= h($album['artist']) ?></div>
                <div class="album-card-meta">
                    <span class="album-card-genre"><?= h($album['genre']) ?></span>
                    <span class="rating-score">★ <?= round($album['avg_rating'], 1) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>