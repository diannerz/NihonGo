<?php
// php/reset_password.php
header('Content-Type: application/json');
require __DIR__.'/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$token = $input['token'] ?? '';
$newPassword = $input['password'] ?? '';

if (!$token || !$newPassword) {
    http_response_code(400);
    echo json_encode(['error'=>'missing']);
    exit;
}

if (strlen($newPassword) < 6 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
    http_response_code(400);
    echo json_encode(['error'=>'invalid_password']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT pr.id as pr_id, pr.user_id, pr.expires_at FROM password_resets pr WHERE pr.token = :t');
    $stmt->execute([':t'=>$token]);
    $row = $stmt->fetch();

    if (!$row || strtotime($row['expires_at']) < time()) {
        http_response_code(400);
        echo json_encode(['error'=>'invalid_or_expired']);
        exit;
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :uid');
    $update->execute([':h'=>$hash, ':uid'=>$row['user_id']]);

    // remove reset row
    $pdo->prepare('DELETE FROM password_resets WHERE id = :id')->execute([':id'=>$row['pr_id']]);

    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'server']);
}
