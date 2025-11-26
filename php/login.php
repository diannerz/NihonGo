<?php
// php/login.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';
session_start();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$keep = !empty($input['keep']);

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :u');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    // ---------------------------
    // CASE 1: USER DOES NOT EXIST
    // ---------------------------
    if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'no_account']);
    exit;
}

if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'wrong_password']);
    exit;
}


    // ---------------------------
    // SUCCESS
    // ---------------------------
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    if ($keep) {
        $token = bin2hex(random_bytes(32));
        $expiry = (new DateTime('+30 days'))->format('Y-m-d H:i:s');
        $update = $pdo->prepare('UPDATE users SET remember_token = :t, remember_expiry = :e WHERE id = :id');
        $update->execute([':t' => $token, ':e' => $expiry, ':id' => $user['id']]);
        setcookie('remember_token', $token, time() + 60*60*24*30, '/', '', false, true);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
