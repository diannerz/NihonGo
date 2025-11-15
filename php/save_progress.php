<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

require __DIR__ . '/db.php';

$input = json_decode(file_get_contents("php://input"), true);
$kana = $input['kana'] ?? '';
$type = $input['type'] ?? '';

if (!$kana || !$type) {
    echo json_encode(['error' => 'invalid']);
    exit;
}

$stmt = $pdo->prepare("INSERT IGNORE INTO kana_progress (user_id, kana_type, kana_char)
                       VALUES (:uid, :type, :kana)");
$stmt->execute([
    ':uid' => $_SESSION['user_id'],
    ':type' => $type,
    ':kana' => $kana
]);

echo json_encode(['success' => true]);
