<?php
// Database Configuration
// Listeners Lounge - INT1059 Advanced Web

define('DB_HOST', 'sql310.infinityfree.com');
define('DB_USER', 'if0_41687360');
define('DB_PASS', 'joon36472');
define('DB_NAME', 'if0_41687360_listeners');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: sanitise output
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Helper: get average rating for album
function getAverageRating($conn, $album_id) {
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE album_id = ?");
    $stmt->bind_param("i", $album_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result;
}

// Helper: render star rating HTML
function renderStars($rating, $interactive = false) {
    $html = '<div class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($interactive) {
            $html .= '<span class="star' . ($i <= $rating ? ' filled' : '') . '" data-rating="' . $i . '">★</span>';
        } else {
            $html .= '<span class="star' . ($i <= $rating ? ' filled' : '') . '">★</span>';
        }
    }
    $html .= '</div>';
    return $html;
}
?>
