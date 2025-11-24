<?php
// php/get_daily_quiz.php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');

// try to fetch existing
$stmt = $pdo->prepare("SELECT quiz_json FROM daily_quiz WHERE day_date = :day LIMIT 1");
$stmt->execute([':day'=>$today]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && !empty($row['quiz_json'])) {
    echo $row['quiz_json'];
    exit;
}

// not found -> generate by including generator (safe)
$genPath = __DIR__ . '/generate_daily_quiz.php';
if (file_exists($genPath)) {
    // require will output JSON itself (the generator echoes the payload)
    require $genPath;
    exit;
}

// fallback: error
http_response_code(500);
echo json_encode(['error'=>'no_quiz_available']);
