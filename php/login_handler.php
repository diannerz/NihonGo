<?php
/**
 * php/login_handler.php
 * Handles user login with role support
 */

session_start();
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Missing username or password']);
    exit;
}

try {
    // Fetch user with role
    $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
        exit;
    }
    
    // LOGIN SUCCESSFUL - SET SESSION WITH ROLE
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'] ?? 'user';  // ← THIS IS CRITICAL!
    
    // Handle "Remember me"
    if (!empty($_POST['remember'])) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
        
        $updateStmt = $pdo->prepare('UPDATE users SET remember_token = ?, remember_expiry = ? WHERE id = ?');
        $updateStmt->execute([$token, $expiry, $user['id']]);
        
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'role' => $_SESSION['role'],
        'redirect' => ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'dashboard.php'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
