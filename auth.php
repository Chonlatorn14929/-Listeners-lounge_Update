<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) redirect('/listeners_lounge/index.php');

$conn = getConnection();
$mode = $_GET['mode'] ?? 'login';
$errors = [];
$pageTitle = 'Login / Register';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // LOGIN
    if ($action === 'login') {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $errors['login'] = 'Please fill in all fields.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->bind_param("ss", $identifier, $identifier);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Welcome back, ' . $user['username'] . '!'];
                redirect('/listeners_lounge/index.php');
            } else {
                $errors['login'] = 'Wrong username or password. Please try again.';
                $mode = 'login';
            }
        }
    }

    // REGISTER
    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $mode = 'register';

        if (empty($username) || empty($email) || empty($password)) {
            $errors['register'] = 'Please fill in all fields.';
        } elseif (strlen($username) < 3) {
            $errors['register'] = 'Username must be at least 3 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['register'] = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $errors['register'] = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $errors['register'] = 'Passwords do not match.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                $errors['register'] = 'Username or email is already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt2->bind_param("sss", $username, $email, $hash);
                if ($stmt2->execute()) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Account created! Welcome, ' . $username . '!'];
                    redirect('/listeners_lounge/index.php');
                } else {
                    $errors['register'] = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="auth-page">
    <div class="auth-box">

        <div class="auth-logo">
            <div class="logo-icon">♪</div>
            <h2>Listeners <span>Lounge</span></h2>
            <p>Your music review collection</p>
        </div>

        <div class="auth-tabs">
            <div class="auth-tab <?= $mode !== 'register' ? 'active' : '' ?>" data-tab="login-panel">Login</div>
            <div class="auth-tab <?= $mode === 'register' ? 'active' : '' ?>" data-tab="register-panel">Register</div>
        </div>

        <div class="auth-card">

            <!-- LOGIN -->
            <div class="auth-panel <?= $mode !== 'register' ? 'active' : '' ?>" id="login-panel">
                <h3>Login</h3>

                <?php if (isset($errors['login'])): ?>
                <div class="flash flash--error" style="border-radius: 4px; margin-bottom: 13px; border: none;">
                    <?= h($errors['login']) ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label class="form-label">Username or Email</label>
                        <input type="text" name="identifier" class="form-input" placeholder="Enter username or email">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Enter password">
                    </div>
                    <button type="submit" class="btn btn--accent" style="width: 100%;">Login</button>
                </form>

                <p style="color: #666666; font-size: 12px; text-align: center; margin-top: 14px;">
                    Demo: <strong style="color: #eeeeee;">MusicLover42</strong> / <strong style="color: #eeeeee;">password</strong>
                </p>
            </div>

            <!-- REGISTER -->
            <div class="auth-panel <?= $mode === 'register' ? 'active' : '' ?>" id="register-panel">
                <h3>Create Account</h3>

                <?php if (isset($errors['register'])): ?>
                <div class="flash flash--error" style="border-radius: 4px; margin-bottom: 13px; border: none;">
                    <?= h($errors['register']) ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" placeholder="Choose a username"
                               value="<?= isset($_POST['username']) ? h($_POST['username']) : '' ?>">
                        <p class="form-help">At least 3 characters</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" placeholder="your@email.com"
                               value="<?= isset($_POST['email']) ? h($_POST['email']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="At least 8 characters">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input" placeholder="Repeat password">
                    </div>
                    <button type="submit" class="btn btn--accent" style="width: 100%;">Create Account</button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
