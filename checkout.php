<?php
session_start();
require_once 'db_connect.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
if (!$productId) {
    header('Location: shop.php');
    exit;
}

$product = null;
$message = '';

try {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: shop.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $shippingName = trim($_POST['shipping_name'] ?? '');
        $shippingEmail = trim($_POST['shipping_email'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        if ($shippingName && filter_var($shippingEmail, FILTER_VALIDATE_EMAIL) && $shippingAddress) {
            $insert = $pdo->prepare('INSERT INTO orders (customer_id, product_id) VALUES (:customer_id, :product_id)');
            $insert->execute([
                ':customer_id' => $_SESSION['user']['id'],
                ':product_id' => $productId,
            ]);
            header('Location: customer-orders.php');
            exit;
        }
        $message = 'Please confirm your contact details and shipping address.';
    }
} catch (PDOException $e) {
    $message = 'Unable to process checkout right now.';
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Checkout</h2>
            <p class="muted">Complete your order with secure details and fast confirmation.</p>
        </div>
    </div>
    <div class="product-details">
        <div class="product-gallery">
            <img src="<?= htmlspecialchars($product['photo_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>
        <div class="card">
            <h3><?= htmlspecialchars($product['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p class="price">$<?= number_format($product['price'], 2) ?></p>
            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form class="form-card" method="post" action="checkout.php?product_id=<?= $productId ?>">
                <label class="label" for="shipping_name">Full name</label>
                <input class="input" type="text" id="shipping_name" name="shipping_name" required>
                <label class="label" for="shipping_email">Email address</label>
                <input class="input" type="email" id="shipping_email" name="shipping_email" required>
                <label class="label" for="shipping_address">Shipping address</label>
                <textarea class="textarea" id="shipping_address" name="shipping_address" required></textarea>
                <button class="button button-primary" type="submit">Confirm order</button>
            </form>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>