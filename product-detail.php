<?php
session_start();
require_once 'db_connect.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    header('Location: shop.php');
    exit;
}

$product = null;
$comments = [];
$message = '';

try {
    $stmt = $pdo->prepare('SELECT p.*, u.username AS seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = :id');
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: shop.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT c.comment_text, c.rating, c.created_at, u.username FROM comments c JOIN users u ON c.customer_id = u.id WHERE c.product_id = :product_id ORDER BY c.created_at DESC');
    $stmt->execute([':product_id' => $productId]);
    $comments = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'], $_POST['rating'])) {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
            $message = 'Please log in as a customer to leave a review.';
        } else {
            $commentText = trim($_POST['comment_text']);
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
            if ($commentText && $rating) {
                $stmt = $pdo->prepare('INSERT INTO comments (product_id, customer_id, comment_text, rating) VALUES (:product_id, :customer_id, :comment_text, :rating)');
                $stmt->execute([
                    ':product_id' => $productId,
                    ':customer_id' => $_SESSION['user']['id'],
                    ':comment_text' => $commentText,
                    ':rating' => $rating,
                ]);
                header('Location: product-detail.php?id=' . $productId . '&success=review');
                exit;
            }
            $message = 'Please enter a comment and a rating between 1 and 5.';
        }
    }
} catch (PDOException $e) {
    $message = 'Unable to load product details at this time.';
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2><?= htmlspecialchars($product['title']) ?></h2>
            <p class="muted">Sold by <?= htmlspecialchars($product['seller_name']) ?> • <?= htmlspecialchars($product['location']) ?></p>
        </div>
        <div>
            <span class="badge">$<?= number_format($product['price'], 2) ?></span>
        </div>
    </div>
    <div class="product-details">
        <div class="product-gallery">
            <img src="<?= htmlspecialchars($product['photo_path']) ?>" alt="Product image">
        </div>
        <div class="product-meta">
            <div class="card">
                <h3>Product details</h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <div class="meta-row">
                    <strong>Location</strong>
                    <span><?= htmlspecialchars($product['location']) ?></span>
                </div>
                <div class="meta-row">
                    <strong>Seller</strong>
                    <span><?= htmlspecialchars($product['seller_name']) ?></span>
                </div>
                <div class="meta-row">
                    <strong>Document</strong>
                    <span><a href="<?= htmlspecialchars($product['document_path']) ?>" download>Download</a></span>
                </div>
                <div class="meta-row">
                    <a class="button button-primary" href="checkout.php?product_id=<?= $product['id'] ?>">Place order</a>
                </div>
            </div>
            <div class="card">
                <h3>Customer reviews</h3>
                <p class="muted">Read feedback from campus buyers and share your rating.</p>
                <ul class="comments-list">
                    <?php if ($comments): ?>
                        <?php foreach ($comments as $comment): ?>
                            <li class="comment-card">
                                <div class="rating-stars"><?= str_repeat('★', $comment['rating']) ?><?= str_repeat('☆', 5 - $comment['rating']) ?></div>
                                <p><?= htmlspecialchars($comment['comment_text']) ?></p>
                                <small>— <?= htmlspecialchars($comment['username']) ?>, <?= date('M j, Y', strtotime($comment['created_at'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="comment-card"><p>No reviews yet. Be the first to leave feedback.</p></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card">
                <h3>Leave a review</h3>
                <?php if ($message): ?>
                    <div class="alert"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <form method="post" action="product-detail.php?id=<?= $product['id'] ?>">
                    <label class="label" for="rating">Rating</label>
                    <select class="select" id="rating" name="rating" required>
                        <option value="">Choose rating</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
                        <?php endfor; ?>
                    </select>
                    <label class="label" for="comment_text">Comment</label>
                    <textarea class="textarea" id="comment_text" name="comment_text" placeholder="Share your experience" required></textarea>
                    <button class="button button-primary" type="submit">Submit review</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>