<?php
require "db.php";
require "check_auth.php";
header('Content-Type: application/json');

if (!$user) {
  http_response_code(401);
  echo json_encode(['error' => 'unauthorized']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$display = trim($data['display_name'] ?? '');
$bio = trim($data['bio'] ?? '');
$avatar = trim($data['avatar_url'] ?? '');

$stmt = $pdo->prepare("
  UPDATE users
  SET display_name = :d,
      bio = :b,
      avatar_url = :a
  WHERE id = :id
");

$stmt->execute([
  ':d' => $display ?: null,
  ':b' => $bio ?: null,
  ':a' => $avatar ?: null,
  ':id' => $user['id']
]);

echo json_encode(['success' => true]);
