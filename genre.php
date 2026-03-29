<?php
require_once __DIR__ . '/includes/config.php';
$conn = getConnection();
$genre = trim($_GET['genre'] ?? '');
$validGenres = ['Pop', 'Rock', 'Hip-Hop', 'Jazz', 'R&B', 'Indie', 'Electronic', 'Country', 'Classical', 'Metal'];
if (!in_array($genre, $validGenres)) redirect('/listeners_lounge/index.php');
$pageTitle = $genre . ' Albums';

$stmt = $conn->prepare("
    SELECT a.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
    FROM albums a
    LEFT JOIN reviews r ON a.id = r.album_id
    WHERE a.genre = ?
    GROUP BY a.id
    ORDER BY a.title
");
$stmt->bind_param("s", $genre);
$stmt->execute();
$albums = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="genre-hero">
    <div class="breadcrumb">
        <a href="/listeners_lounge/index.php">Home</a> / <?= h($genre) ?>
    </div>
    <h1><?= h($genre) ?> Albums</h1>
    <p style="color: #888888; font-size: 14px; margin-top: 5px;"><?= count($albums) ?> album(s) found</p>
</div>

<div class="section" style="padding-top: 10px;">
    <?php if (empty($albums)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🎵</div>
        <h3>No albums yet</h3>
        <p>No <?= h($genre) ?> albums in the collection.</p>
    </div>
    <?php else: ?>
    <div class="album-grid album-grid--large">
        <?php foreach ($albums as $album): ?>
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
                    <span class="album-card-genre"><?= h($album['release_year']) ?></span>
                    <?php if ($album['avg_rating']): ?>
                    <span class="rating-score">★ <?= round($album['avg_rating'], 1) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>