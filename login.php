<?php
session_start();
require_once 'db_connect.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            if ($user['role'] === 'customer') {
                header('Location: shop.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }
    }
    $message = 'Invalid username or password. Please try again.';
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Login</h2>
            <p class="muted">Sign in to manage your products, view orders, or place a purchase.</p>
        </div>
        <a class="button button-secondary" href="register.php">Create account</a>
    </div>
    <div class="form-card">
        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <label class="label" for="username">Username</label>
            <input class="input" type="text" id="username" name="username" required>
            <label class="label" for="password">Password</label>
            <input class="input" type="password" id="password" name="password" required>
            <button class="button button-primary" type="submit">Login</button>
        </form>
    </div>
</section>
<?php include 'footer.php'; ?>