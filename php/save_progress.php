<?php
session_start();
header('Content-Type: application/json');

// require login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

require __DIR__ . '/db.php';

// read body
$input = json_decode(file_get_contents("php://input"), true);
$kana = trim($input['kana'] ?? '');
$type = strtolower(trim($input['type'] ?? ''));

// validation
if ($kana === '') {
    echo json_encode(['error' => 'missing_kana']);
    exit;
}

// FIRST: Determine kana type by the actual character,
// ignoring anything the client sends.
if (preg_match('/[\x{3040}-\x{309F}]/u', $kana)) {
    $type = 'hiragana';
} 
elseif (preg_match('/[\x{30A0}-\x{30FF}]/u', $kana)) {
    $type = 'katakana';
}
else {
    echo json_encode(['error' => 'invalid_kana']);
    exit;
}

// SECOND: Now check if user already reached 46 for this type
$cntStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT kana_char) 
    FROM kana_progress 
    WHERE user_id = :uid AND kana_type = :type
");
$cntStmt->execute([
    ':uid' => $_SESSION['user_id'],
    ':type' => $type
]);
$learned = (int)$cntStmt->fetchColumn();

if ($learned >= 46) {
    echo json_encode(['success' => true, 'note' => 'max_reached']);
    exit;
}

// THIRD: Perform the insert
$stmt = $pdo->prepare("
    INSERT IGNORE INTO kana_progress (user_id, kana_type, kana_char) 
    VALUES (:uid, :type, :kana)
");
$stmt->execute([
    ':uid'  => $_SESSION['user_id'],
    ':type' => $type,
    ':kana' => $kana
]);

echo json_encode(['success' => true]);
