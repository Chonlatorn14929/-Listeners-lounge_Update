<?php
require_once __DIR__ . '/includes/config.php';

$conn = getConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) redirect('/listeners_lounge/index.php');

// get album info
$stmt = $conn->prepare("SELECT * FROM albums WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$album = $stmt->get_result()->fetch_assoc();

if (!$album) die("Album not found.");

$pageTitle = $album['title'];

// get rating info
$ratingData = getAverageRating($conn, $id);
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : null;
$reviewCount = $ratingData['count'];

// get reviews
$revStmt = $conn->prepare("
    SELECT r.*, u.username
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.album_id = ?
    ORDER BY r.created_at DESC
");
$revStmt->bind_param("i", $id);
$revStmt->execute();
$reviews = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// check if user reviewed or favourited
$userReview = null;
$isFavourite = false;
if (isLoggedIn()) {
    $uid = $_SESSION['user_id'];

    $s = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND album_id = ?");
    $s->bind_param("ii", $uid, $id);
    $s->execute();
    $userReview = $s->get_result()->fetch_assoc();

    $s2 = $conn->prepare("SELECT id FROM favourites WHERE user_id = ? AND album_id = ?");
    $s2->bind_param("ii", $uid, $id);
    $s2->execute();
    $isFavourite = (bool)$s2->get_result()->fetch_assoc();
}

// handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) redirect('/listeners_lounge/auth.php');

    $action = $_POST['action'] ?? '';

    if ($action === 'submit_review') {
        $rating = intval($_POST['rating'] ?? 0);
        $text = trim($_POST['review_text'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please select a star rating.'];
        } elseif (strlen($text) < 10) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Review must be at least 10 characters.'];
        } else {
            $uid = $_SESSION['user_id'];
            if ($userReview) {
                $s = $conn->prepare("UPDATE reviews SET rating = ?, review_text = ? WHERE user_id = ? AND album_id = ?");
                $s->bind_param("isii", $rating, $text, $uid, $id);
            } else {
                $s = $conn->prepare("INSERT INTO reviews (user_id, album_id, rating, review_text) VALUES (?, ?, ?, ?)");
                $s->bind_param("iiis", $uid, $id, $rating, $text);
            }
            $s->execute();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Review saved!'];
        }
        redirect("/listeners_lounge/album.php?id=$id");
    }

    if ($action === 'toggle_favourite') {
        $uid = $_SESSION['user_id'];
        if ($isFavourite) {
            $s = $conn->prepare("DELETE FROM favourites WHERE user_id = ? AND album_id = ?");
            $s->bind_param("ii", $uid, $id);
            $s->execute();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Removed from favourites.'];
        } else {
            $s = $conn->prepare("INSERT INTO favourites (user_id, album_id) VALUES (?, ?)");
            $s->bind_param("ii", $uid, $id);
            $s->execute();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Added to favourites!'];
        }
        redirect("/listeners_lounge/album.php?id=$id");
    }
}

$tracks = array_map('trim', explode(',', $album['track_listing'] ?? ''));
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="album-detail">

    <!-- left sidebar -->
    <div class="album-detail-sidebar">
        <div class="album-cover large" style="background-color: <?= h($album['cover_color']) ?>;">
            <?php if (!empty($album['cover_image'])): ?>
                <img src="/listeners_lounge/assets/images/<?= h($album['cover_image']) ?>"
                     alt="<?= h($album['title']) ?>"
                     onerror="this.style.display='none'">
            <?php else: ?>
                <?= h($album['cover_emoji']) ?>
            <?php endif; ?>
        </div>

        <div class="album-meta-block">
            <div class="album-meta-row">
                <span class="meta-label">Artist</span>
                <span class="meta-value"><?= h($album['artist']) ?></span>
            </div>
            <div class="album-meta-row">
                <span class="meta-label">Year</span>
                <span class="meta-value"><?= h($album['release_year']) ?></span>
            </div>
            <div class="album-meta-row">
                <span class="meta-label">Genre</span>
                <span class="meta-value"><?= h($album['genre']) ?></span>
            </div>
            <div class="album-meta-row">
                <span class="meta-label">Tracks</span>
                <span class="meta-value"><?= count($tracks) ?></span>
            </div>
        </div>

        <?php if (isLoggedIn()): ?>
        <form method="POST">
            <input type="hidden" name="action" value="toggle_favourite">
            <button type="submit" class="btn <?= $isFavourite ? 'btn--danger' : 'btn--accent' ?>">
                <?= $isFavourite ? '♥ Remove Favourite' : '♡ Add to Favourites' ?>
            </button>
        </form>
        <?php else: ?>
        <a href="/listeners_lounge/auth.php" class="btn btn--outline">Login to Add Favourites</a>
        <?php endif; ?>
    </div>

    <!-- right main area -->
    <div class="album-detail-main">
        <div class="breadcrumb">
            <a href="/listeners_lounge/index.php">Home</a> /
            <a href="/listeners_lounge/genre.php?genre=<?= urlencode($album['genre']) ?>"><?= h($album['genre']) ?></a> /
            <?= h($album['title']) ?>
        </div>

        <div class="album-title-block">
            <h1><?= h($album['title']) ?></h1>
            <div class="album-artist">by <span><?= h($album['artist']) ?></span> · <?= h($album['release_year']) ?></div>

            <div class="album-rating-big">
                <?php if ($avgRating): ?>
                    <span class="rating-number"><?= $avgRating ?></span>
                    <?= renderStars(round($avgRating)) ?>
                    <span class="rating-count">(<?= $reviewCount ?> review<?= $reviewCount != 1 ? 's' : '' ?>)</span>
                <?php else: ?>
                    <span style="color: #666666; font-size: 14px;">No ratings yet</span>
                <?php endif; ?>
            </div>

            <?php if ($album['description']): ?>
            <p class="album-description"><?= h($album['description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- track list -->
        <?php if (!empty($tracks) && $tracks[0] !== ''): ?>
        <div class="track-list">
            <h3>Track Listing</h3>
            <?php foreach ($tracks as $i => $track): ?>
            <div class="track-item">
                <span class="track-num"><?= $i + 1 ?></span>
                <span><?= h($track) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- reviews -->
        <div class="reviews-section">
            <h2>Reviews (<?= $reviewCount ?>)</h2>

            <?php if (isLoggedIn()): ?>
            <div class="review-form-card">
                <h3><?= $userReview ? 'Edit Your Review' : 'Write a Review' ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="submit_review">
                    <input type="hidden" name="rating" id="ratingInput" value="<?= $userReview ? $userReview['rating'] : 0 ?>">

                    <div class="form-group">
                        <label class="form-label">Rating</label>
                        <div class="stars interactive" id="starRating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= ($userReview && $i <= $userReview['rating']) ? 'filled' : '' ?>"
                                  data-rating="<?= $i ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Your Review</label>
                        <textarea name="review_text" class="form-textarea"
                                  placeholder="Write your thoughts about this album..."><?= $userReview ? h($userReview['review_text']) : '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn--accent" style="width: auto; padding: 9px 22px;">
                        <?= $userReview ? 'Update Review' : 'Submit Review' ?>
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="no-reviews" style="margin-bottom: 16px;">
                <a href="/listeners_lounge/auth.php" style="color: #4caf50;">Login</a> to write a review.
            </div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
            <div class="no-reviews">No reviews yet. Be the first!</div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <span class="review-author"><?= h($review['username']) ?></span>
                            <?= renderStars($review['rating']) ?>
                        </div>
                        <span class="review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <p class="review-text"><?= nl2br(h($review['review_text'])) ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
