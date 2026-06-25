<?php
session_start();
require_once 'db_connect.php';

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'seller'], true)) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$message = '';

try {
    $productCount = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $orderCount = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $reviewCount = $pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();

    if ($user['role'] === 'seller') {
        $sellerProducts = $pdo->prepare('SELECT * FROM products WHERE seller_id = :seller_id ORDER BY created_at DESC');
        $sellerProducts->execute([':seller_id' => $user['id']]);
        $sellerProducts = $sellerProducts->fetchAll();
    } else {
        $sellerProducts = $pdo->query('SELECT p.*, u.username AS seller_name FROM products p JOIN users u ON p.seller_id = u.id ORDER BY p.created_at DESC')->fetchAll();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['description'], $_POST['price'], $_POST['location'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $location = trim($_POST['location']);

        if ($title && $description && $price && $location) {
            $photoPath = null;
            $docPath = null;
            if (!empty($_FILES['photo']['tmp_name'])) {
                $photoPath = 'uploads/photos/' . basename($_FILES['photo']['name']);
                move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
            }
            if (!empty($_FILES['document']['tmp_name'])) {
                $docPath = 'uploads/docs/' . basename($_FILES['document']['name']);
                move_uploaded_file($_FILES['document']['tmp_name'], $docPath);
            }

            $insert = $pdo->prepare('INSERT INTO products (seller_id, title, description, price, photo_path, document_path, location) VALUES (:seller_id, :title, :description, :price, :photo_path, :document_path, :location)');
            $insert->execute([
                ':seller_id' => $user['id'],
                ':title' => $title,
                ':description' => $description,
                ':price' => $price,
                ':photo_path' => $photoPath,
                ':document_path' => $docPath,
                ':location' => $location,
            ]);
            $message = 'Product added successfully.';
            header('Location: dashboard.php');
            exit;
        } else {
            $message = 'Please complete all required fields with valid values.';
        }
    }
} catch (PDOException $e) {
    $message = 'Unable to load dashboard data at this time.';
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Dashboard</h2>
            <p class="muted">Welcome back, <?= htmlspecialchars($user['username']) ?>. Manage products, track activity, and stay informed.</p>
        </div>
        <a href="logout.php" class="button button-secondary">Logout</a>
    </div>
    <div class="grid-3">
        <div class="stat-block">
            <strong>Total products</strong>
            <span><?= number_format($productCount) ?></span>
        </div>
        <div class="stat-block">
            <strong>Orders</strong>
            <span><?= number_format($orderCount) ?></span>
        </div>
        <div class="stat-block">
            <strong>Reviews</strong>
            <span><?= number_format($reviewCount) ?></span>
        </div>
    </div>
    <div class="section-card">
        <div class="section-heading">
            <div>
                <h3>Add a new product</h3>
                <p class="muted">Upload photos and documents for new campus listings.</p>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form class="form-card" method="post" action="dashboard.php" enctype="multipart/form-data">
            <label class="label" for="title">Product title</label>
            <input class="input" type="text" id="title" name="title" required>
            <label class="label" for="description">Description</label>
            <textarea class="textarea" id="description" name="description" required></textarea>
            <label class="label" for="price">Price</label>
            <input class="input" type="number" id="price" name="price" step="0.01" min="0" required>
            <label class="label" for="location">Location</label>
            <input class="input" type="text" id="location" name="location" required>
            <label class="label" for="photo">Product photo</label>
            <input class="input" type="file" id="photo" name="photo" accept="image/*">
            <label class="label" for="document">Document upload (PDF or image)</label>
            <input class="input" type="file" id="document" name="document" accept="application/pdf,image/*">
            <button class="button button-primary" type="submit">Upload product</button>
        </form>
    </div>
    <div class="section-card">
        <div class="section-heading">
            <div>
                <h3>Your products</h3>
                <p class="muted">Review current product listings and maintain campus inventory.</p>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Location</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sellerProducts as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['title']) ?></td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td><?= htmlspecialchars($product['location']) ?></td>
                        <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include 'footer.php'; ?>