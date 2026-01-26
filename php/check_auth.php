<?php
// php/check_auth.php
session_start();
require __DIR__ . '/db.php';

$user = null;

if (!empty($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    
    // ALWAYS fetch fresh data from database
    $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    
    if ($row) {
        $_SESSION['email'] = $row['email'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $user = [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'role' => $row['role']
        ];
    } else {
        // User no longer exists, clear session
        session_destroy();
        $user = null;
    }
} elseif (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare('SELECT id, username, email, role, remember_expiry FROM users WHERE remember_token = :t');
    $stmt->execute([':t'=>$token]);
    $row = $stmt->fetch();
    if ($row && strtotime($row['remember_expiry']) > time()) {
        // Restore session
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        $user = [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'role' => $row['role']
        ];
    } else {
        // Invalid token: clear cookie
        setcookie('remember_token','', time()-3600, '/', '', false, true);
        $user = null;
    }
}

