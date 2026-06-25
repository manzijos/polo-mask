<?php
// db_connect.php
// Secure PDO database connection for the e-shopping platform.

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'polo_mask_e_shopping');
define('DB_USER', 'admin');
define('DB_PASS', 'password123');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}
?>