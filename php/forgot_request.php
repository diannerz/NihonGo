<?php
// php/forgot_request.php
header('Content-Type: application/json');
require __DIR__.'/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($input['email'] ?? '');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); echo json_encode(['error'=>'invalid_email']); exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e');
$stmt->execute([':e'=>$email]);
$user = $stmt->fetch();
if (!$user) {
    // Don't reveal whether email exists — respond success for UX
    echo json_encode(['success'=>true]);
    exit;
}

$token = bin2hex(random_bytes(32));
$expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

$pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (:uid, :t, :e)')
    ->execute([':uid'=>$user['id'], ':t'=>$token, ':e'=>$expires]);

$resetUrl = sprintf('http://localhost/NihonGo/reset_password.html?token=%s', $token);

// For local dev, return the link (in production you'd email the link)
echo json_encode(['success'=>true, 'resetUrl'=>$resetUrl]);
