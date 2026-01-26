<?php
// php/check_auth.php
session_start();
require __DIR__ . '/db.php';

$user = null;

if (!empty($_SESSION['user_id'])) {
    $user = ['id'=>$_SESSION['user_id'],'username'=>$_SESSION['username'],'role'=>$_SESSION['role'] ?? 'user'];
} elseif (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare('SELECT id, username, role, remember_expiry FROM users WHERE remember_token = :t');
    $stmt->execute([':t'=>$token]);
    $row = $stmt->fetch();
    if ($row && strtotime($row['remember_expiry']) > time()) {
        // restore session
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $user = ['id'=>$_SESSION['user_id'],'username'=>$_SESSION['username'],'role'=>$row['role']];
    } else {
        // invalid token: clear cookie
        setcookie('remember_token','', time()-3600, '/', '', false, true);
        $user = null;
    }
}
