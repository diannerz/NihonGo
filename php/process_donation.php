<?php
// php/process_donation.php - Handle donation submission

require 'db.php';
require 'check_auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is authenticated
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get JSON data from request body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$amount = isset($input['amount']) ? floatval($input['amount']) : 0;
$feature_name = isset($input['feature_name']) ? trim($input['feature_name']) : 'General';
$user_id = $user['id'];

// Validate amount
if ($amount <= 0 || $amount > 10000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid donation amount']);
    exit;
}

// Validate feature name
if (empty($feature_name)) {
    $feature_name = 'General';
}

try {
    // Insert donation into database
    $stmt = $pdo->prepare('
        INSERT INTO donations (user_id, amount, feature_name, donation_date)
        VALUES (:user_id, :amount, :feature_name, NOW())
    ');
    
    $result = $stmt->execute([
        ':user_id' => $user_id,
        ':amount' => $amount,
        ':feature_name' => $feature_name
    ]);
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Donation recorded successfully',
            'donation_id' => $pdo->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save donation']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
