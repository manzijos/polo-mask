<?php
session_start();
require_once 'db_connect.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$orders = [];

try {
    $stmt = $pdo->prepare('SELECT o.*, p.title, p.photo_path, p.document_path FROM orders o JOIN products p ON o.product_id = p.id WHERE o.customer_id = :customer_id ORDER BY o.order_date DESC');
    $stmt->execute([':customer_id' => $user['id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Your orders</h2>
            <p class="muted">Track purchase status and download receipts or product documents.</p>
        </div>
    </div>
    <?php if ($orders): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Ordered</th>
                    <th>Downloads</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($order['title']) ?></strong>
                            <p class="muted">Order #<?= $order['id'] ?></p>
                        </td>
                        <td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
                        <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                        <td>
                            <?php if ($order['document_path']): ?>
                                <a href="<?= htmlspecialchars($order['document_path']) ?>" download>Download doc</a>
                            <?php else: ?>
                                <span class="muted">No document</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert">
            <p>No orders have been placed yet. Browse products to place your first order.</p>
        </div>
    <?php endif; ?>
</section>
<?php include 'footer.php'; ?>