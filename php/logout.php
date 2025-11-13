<?php
// php/logout.php
session_start();
require __DIR__.'/db.php';

if (!empty($_COOKIE['remember_token'])) {
    $stmt = $pdo->prepare('UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE remember_token = :t');
    $stmt->execute([':t' => $_COOKIE['remember_token']]);
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

$_SESSION = [];
session_destroy();
header('Location: ../login.html');
exit;
