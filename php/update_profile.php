<?php
require "check_auth.php";
require "db.php";

if (!$user) {
  http_response_code(401);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$fields = [];
$params = [':id' => $user['id']];

if (isset($data['display_name'])) {
  $fields[] = "display_name = :dn";
  $params[':dn'] = trim($data['display_name']);
}

if (isset($data['bio'])) {
  $fields[] = "bio = :bio";
  $params[':bio'] = trim($data['bio']);
}

if (isset($data['avatar_url'])) {
  $fields[] = "avatar_url = :av";
  $params[':av'] = trim($data['avatar_url']);
}

if (!$fields) {
  exit;
}

$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['success'=>true]);
