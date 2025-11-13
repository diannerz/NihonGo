<?php
require __DIR__ . '/db.php';
try {
    $stmt = $pdo->query('SELECT COUNT(*) as c FROM users');
    $row = $stmt->fetch();
    echo "DB OK — users table count: " . ($row['c'] ?? '0');
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage();
}
