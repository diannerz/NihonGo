<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

require __DIR__ . '/db.php';

$input = json_decode(file_get_contents("php://input"), true);
$kana = trim($input['kana'] ?? '');
$type = strtolower(trim($input['type'] ?? ''));
$action = $input['action'] ?? ''; // 'view', 'master', 'unmaster', 'manga_view', 'quiz_complete'

$uid = (int) $_SESSION['user_id'];
$today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d'); // use UTC or change to server timezone

// validate
if (!in_array($action, ['view','master','unmaster','manga_view','quiz_complete']) ) {
    echo json_encode(['error' => 'invalid_action']);
    exit;
}

// for kana-specific actions require kana/type
if (in_array($action, ['view','master','unmaster'])) {
    if ($kana === '' || !in_array($type, ['hiragana','katakana'])) {
        echo json_encode(['error' => 'invalid_input']);
        exit;
    }
}

/* ------------------------------------
   Ensure the kana row exists before updates
--------------------------------------*/
if (in_array($action, ['view','master','unmaster'])) {
    $existsStmt = $pdo->prepare("
        SELECT id, mastery_level 
        FROM kana_progress 
        WHERE user_id = :uid AND kana_type = :type AND kana_char = :kana
        LIMIT 1
    ");
    $existsStmt->execute([':uid'=>$uid, ':type'=>$type, ':kana'=>$kana]);
    $row = $existsStmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // create with view_count = 0, mastery_level = 0
        $ins = $pdo->prepare("
            INSERT INTO kana_progress (user_id, kana_type, kana_char, view_count, mastery_level, created_at)
            VALUES (:uid, :type, :kana, 0, 0, NOW())
        ");
        $ins->execute([':uid'=>$uid, ':type'=>$type, ':kana'=>$kana]);
        $row = ['mastery_level' => 0];
    }
}

/* ------------------------------------
   VIEW event (increments view_count)
   plus daily unique-count logic
--------------------------------------*/
if ($action === 'view') {
    // increment view_count
    $pdo->prepare("
        UPDATE kana_progress 
        SET view_count = view_count + 1
        WHERE user_id = :uid AND kana_type = :type AND kana_char = :kana
    ")->execute([':uid'=>$uid, ':type'=>$type, ':kana'=>$kana]);

    // try to insert a unique daily_kana_views row; if inserted, increment daily_progress.flashcard_views
    $insertView = $pdo->prepare("
        INSERT IGNORE INTO daily_kana_views (user_id, day_date, kana_type, kana_char)
        VALUES (:uid, :day, :type, :kana)
    ");
    $insertView->execute([':uid'=>$uid, ':day'=>$today, ':type'=>$type, ':kana'=>$kana]);

    // if row affected (inserted), then update daily_progress
    if ($insertView->rowCount() > 0) {
        $upsert = $pdo->prepare("
            INSERT INTO daily_progress (user_id, day_date, flashcard_views)
            VALUES (:uid, :day, 1)
            ON DUPLICATE KEY UPDATE flashcard_views = flashcard_views + 1
        ");
        $upsert->execute([':uid'=>$uid, ':day'=>$today]);
    }
}

/* ------------------------------------
   MASTER (set mastery_level = 2)
   When user marks as mastered, we update kana_progress mastery
   Mastered counts are used for "My progress" (dashboard)
--------------------------------------*/
if ($action === 'master') {
    $pdo->prepare("
        UPDATE kana_progress 
        SET mastery_level = 2
        WHERE user_id = :uid AND kana_type = :type AND kana_char = :kana
    ")->execute([':uid'=>$uid, ':type'=>$type, ':kana'=>$kana]);
}

/* ------------------------------------
   UNMASTER (set mastery_level = 0)
--------------------------------------*/
if ($action === 'unmaster') {
    $pdo->prepare("
        UPDATE kana_progress 
        SET mastery_level = 0
        WHERE user_id = :uid AND kana_type = :type AND kana_char = :kana
    ")->execute([':uid'=>$uid, ':type'=>$type, ':kana'=>$kana]);
}

/* ------------------------------------
   Manga view (page opened / panel viewed)
   We count manga_views (per day) — caller should ensure not to spam
--------------------------------------*/
if ($action === 'manga_view') {
    $upsert = $pdo->prepare("
        INSERT INTO daily_progress (user_id, day_date, manga_views)
        VALUES (:uid, :day, 1)
        ON DUPLICATE KEY UPDATE manga_views = manga_views + 1
    ");
    $upsert->execute([':uid'=>$uid, ':day'=>$today]);
}

/* ------------------------------------
   Quiz complete (increments vocab_quiz_completed)
--------------------------------------*/
if ($action === 'quiz_complete') {
    $upsert = $pdo->prepare("
        INSERT INTO daily_progress (user_id, day_date, vocab_quiz_completed)
        VALUES (:uid, :day, 1)
        ON DUPLICATE KEY UPDATE vocab_quiz_completed = vocab_quiz_completed + 1
    ");
    $upsert->execute([':uid'=>$uid, ':day'=>$today]);
}

/* ------------------------------------
   Return updated mastery + today's daily counters
--------------------------------------*/

// current mastery for this kana if applicable
$mastery_level = 0;
if (in_array($action, ['view','master','unmaster'])) {
    $newMastery = $pdo->prepare("
        SELECT mastery_level FROM kana_progress
        WHERE user_id = :uid AND kana_type = :type AND kana_char = :kana
        LIMIT 1
    ");
    $newMastery->execute([':uid'=>$uid, ':type'=>$type, ':kana'=>$kana]);
    $mastery_level = (int)$newMastery->fetchColumn();
}

// total mastered count for the type (used for the "My progress" widget)
$type_mastered_count = 0;
if (in_array($type, ['hiragana','katakana'])) {
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM kana_progress 
        WHERE user_id = :uid AND kana_type = :type AND mastery_level = 2
    ");
    $countStmt->execute([':uid'=>$uid, ':type'=>$type]);
    $type_mastered_count = (int)$countStmt->fetchColumn();
}

// today's daily_progress values
$dpStmt = $pdo->prepare("
    SELECT flashcard_views, manga_views, vocab_quiz_completed
    FROM daily_progress
    WHERE user_id = :uid AND day_date = :day
    LIMIT 1
");
$dpStmt->execute([':uid'=>$uid, ':day'=>$today]);
$dpRow = $dpStmt->fetch(PDO::FETCH_ASSOC) ?: ['flashcard_views'=>0,'manga_views'=>0,'vocab_quiz_completed'=>0];

echo json_encode([
    'success' => true,
    'mastery_level' => $mastery_level,
    'type_count' => $type_mastered_count,
    'today' => [
        'flashcard_views' => (int)$dpRow['flashcard_views'],
        'manga_views' => (int)$dpRow['manga_views'],
        'vocab_quiz_completed' => (int)$dpRow['vocab_quiz_completed']
    ]
]);
