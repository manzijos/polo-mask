<?php
session_start();
require_once 'db_connect.php';

$filters = [];
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = '(p.title LIKE :search OR p.description LIKE :search)';
    $params[':search'] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['location'])) {
    $where[] = 'p.location = :location';
    $params[':location'] = $_GET['location'];
}

$query = 'SELECT p.id, p.title, p.price, p.photo_path, p.location, p.description FROM products p';
if ($where) {
    $query .= ' WHERE ' . implode(' AND ', $where);
}
$query .= ' ORDER BY p.created_at DESC';

$products = [];
$locations = [];
try {
    $stmt = $pdo->query('SELECT DISTINCT location FROM products ORDER BY location ASC');
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Shop Products</h2>
            <p class="muted">Select from available campus products, filter by location, and view detailed descriptions.</p>
        </div>
    </div>
    <div class="card">
        <form class="form-grid" method="get" action="shop.php">
            <div>
                <label class="label" for="search">Search</label>
                <input class="input" type="text" id="search" name="search" placeholder="Search products" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div>
                <label class="label" for="location">Location</label>
                <select class="select" name="location" id="location">
                    <option value="">All locations</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= htmlspecialchars($location) ?>" <?= (!empty($_GET['location']) && $_GET['location'] === $location) ? 'selected' : '' ?>><?= htmlspecialchars($location) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="align-self: end;">
                <button class="button button-primary" type="submit">Filter</button>
            </div>
        </form>
    </div>
    <div class="product-grid">
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <img src="<?= htmlspecialchars($product['photo_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                    <div class="product-card-content">
                        <span class="badge"><?= htmlspecialchars($product['location']) ?></span>
                        <h3><?= htmlspecialchars($product['title']) ?></h3>
                        <p><?= htmlspecialchars(substr($product['description'], 0, 95)) ?>...</p>
                        <p class="price">$<?= number_format($product['price'], 2) ?></p>
                        <a class="button button-secondary" href="product-detail.php?id=<?= $product['id'] ?>">View details</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card alert">
                <p>No products match your search. Try a different keyword or location.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include 'footer.php'; ?>