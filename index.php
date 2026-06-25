<?php
session_start();
require_once 'db_connect.php';

$featuredProducts = [];
try {
    $stmt = $pdo->query('SELECT p.id, p.title, p.price, p.photo_path, p.location FROM products p ORDER BY p.created_at DESC LIMIT 4');
    $featuredProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredProducts = [];
}
?>
<?php include 'header.php'; ?>
<section class="hero container">
    <div class="hero-grid">
        <div class="hero-copy">
            <p class="badge">POLO MASK</p>
            <h1>BEST BALACLAVA AT LOWEST PRICE</h1>
            <p>Experience a premium online marketplace designed for customers,and sellers. Browse curated polo products, manage listings securely, and place orders with confidence.</p>
            <div class="hero-actions">
                <a href="shop.php" class="button button-primary">Browse Products</a>
                <a href="about.php" class="button button-secondary">Learn More</a>
            </div>
        </div>
        <div class="hero-visual">
            <img src="assets/images/hero.png" alt="Featured product illustration">
        </div>
    </div>
</section>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Featured Polo Products</h2>
            <p class="muted">Browse top picks from trusted sellers across the campus.</p>
        </div>
        <a href="shop.php" class="button button-secondary">View all products</a>
    </div>
    <div class="product-grid">
        <?php foreach ($featuredProducts as $product): ?>
            <article class="product-card">
                <img src="<?= htmlspecialchars($product['photo_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                <div class="product-card-content">
                    <span class="badge"><?= htmlspecialchars($product['location']) ?></span>
                    <h3><?= htmlspecialchars($product['title']) ?></h3>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>
                    <a class="button button-primary" href="product-detail.php?id=<?= $product['id'] ?>">View product</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<section class="container section-card">
    <div class="section-heading">
        <h2>Why Choose polo mask e-shopping?</h2>
    </div>
    <div class="hero-cards">
        <div class="card">
            <h3>Secure admin portal</h3>
            <p>Admin and seller workflows are unified with safe logins, product tracking, and document upload capabilities.</p>
        </div>
        <div class="card">
            <h3>Professional design</h3>
            <p>A clean and readable interface ensures easy browsing, quick product discovery, and trust for every buyer.</p>
        </div>
        <div class="card">
            <h3>Responsive across devices</h3>
            <p>Built with modern responsive layout techniques so students can shop from mobile, tablet, or desktop.</p>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>