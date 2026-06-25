<?php
session_start();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $messageText = trim($_POST['message'] ?? '');
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $messageText) {
        $message = 'Thank you, ' . htmlspecialchars($name) . '. Your message has been received.';
    } else {
        $message = 'Please fill out the contact form with valid information.';
    }
}
?>
<?php include 'header.php'; ?>
<section class="container section-card">
    <div class="section-heading">
        <div>
            <h2>Contact us</h2>
            <p class="muted">Send a message for support, sales inquiries, or questions about the platform.</p>
        </div>
    </div>
    <div class="form-card">
        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post" action="contact.php">
            <label class="label" for="name">Full name</label>
            <input class="input" id="name" name="name" required>
            <label class="label" for="email">Email address</label>
            <input class="input" id="email" type="email" name="email" required>
            <label class="label" for="message">Message</label>
            <textarea class="textarea" id="message" name="message" required></textarea>
            <button class="button button-primary" type="submit">Send message</button>
        </form>
    </div>
</section>
<?php include 'footer.php'; ?>