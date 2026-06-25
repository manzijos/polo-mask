<?php
session_start();
require_once 'db_connect.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    $location = trim($_POST['location'] ?? '');

    if ($username && $password && in_array($role, ['admin','seller','customer'], true)) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            if ($stmt->fetch()) {
                $message = 'That username is already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $pdo->prepare('INSERT INTO users (username, password_hash, role, location) VALUES (:username, :password_hash, :role, :location)');
                $insert->execute([
                    ':username' => $username,
                    ':password_hash' => $hash,
                    ':role' => $role,
                    ':location' => $location ?: null,
                ]);
                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            $message = 'Unable to create account. Please try again later.';
        }
    } else {
        $message = 'Please complete all required fields.';
    }
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Create your account</h2>
            <p class="muted">Register as a customer, seller, or admin to access the e-shopping platform.</p>
        </div>
        <a class="button button-secondary" href="login.php">Already have an account?</a>
    </div>
    <div class="form-card">
        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post" action="register.php">
            <label class="label" for="username">Username</label>
            <input class="input" type="text" id="username" name="username" required>
            <label class="label" for="password">Password</label>
            <input class="input" type="password" id="password" name="password" required>
            <label class="label" for="role">Account type</label>
            <select class="select" id="role" name="role" required>
                <option value="customer">Customer</option>
                <option value="seller">Seller</option>
                <option value="admin">Admin</option>
            </select>
            <label class="label" for="location">Location</label>
            <input class="input" type="text" id="location" name="location" placeholder="e.g. Campus Store">
            <button class="button button-primary" type="submit">Register account</button>
        </form>
    </div>
</section>
<?php include 'footer.php'; ?>