<?php
// php/reset_password.php
header('Content-Type: application/json');
require __DIR__.'/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$token = $input['token'] ?? '';
$newPassword = $input['password'] ?? '';

if (!$token || !$newPassword) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing token or password.']);
    exit;
}

// -------------------------
// PASSWORD RULE CHECK
// -------------------------
if (strlen($newPassword) < 6 ||
    !preg_match('/[A-Za-z]/', $newPassword) ||
    !preg_match('/\d/', $newPassword)) {

    http_response_code(400);
    echo json_encode([
        'error' => 'Password must be at least 6 chars and include letters and numbers.'
    ]);
    exit;
}

// -------------------------
// VALIDATE TOKEN
// -------------------------
$stmt = $pdo->prepare('SELECT pr.id AS pr_id, pr.user_id, pr.expires_at
                       FROM password_resets pr WHERE pr.token = :t');
$stmt->execute([':t' => $token]);
$row = $stmt->fetch();

if (!$row || strtotime($row['expires_at']) < time()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or expired reset link.']);
    exit;
}

// -------------------------
// UPDATE PASSWORD
// -------------------------
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :uid')
    ->execute([':h' => $hash, ':uid' => $row['user_id']]);

$pdo->prepare('DELETE FROM password_resets WHERE id = :id')
    ->execute([':id' => $row['pr_id']]);

echo json_encode(['success' => true]);
