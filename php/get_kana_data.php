<?php
// get_kana_data.php - Returns kana data from database as JSON
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'hiragana';

try {
    $stmt = $pdo->prepare('
        SELECT id, kana_char, romaji, mnemonic, vocab_jp, vocab_romaji, vocab_eng
        FROM kana_flashcards
        WHERE kana_type = :type
        ORDER BY id
    ');
    $stmt->execute([':type' => $type]);
    $kana = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($kana);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch kana data']);
}
?>
