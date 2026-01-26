<?php
// Prevent any output before JSON header
ob_start();
session_start();

// Set JSON header FIRST
header('Content-Type: application/json');

try {
    // Include database (uses PDO)
    require __DIR__ . '/db.php';
    
    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid method']);
        exit;
    }
    
    // Check authentication
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
    
    $user_id = intval($_SESSION['user_id']);
    
    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }
    
    // Extract values
    $amount = floatval($input['amount'] ?? 0);
    $feature_name = trim($input['feature_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $fullName = trim($input['fullName'] ?? '');
    $cardNumberLast4 = trim($input['cardNumber'] ?? '');
    
    // Validate amount
    if ($amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }
    
    // Validate email
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email']);
        exit;
    }
    
    // Get user's email from database using PDO
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    
    if (!$row) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $userEmail = trim($row['email']);
    
    // Email must match exactly
    if (strtolower($email) !== strtolower($userEmail)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Email mismatch']);
        exit;
    }
    
    // Validate name
    if (!$fullName || strlen($fullName) < 2) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid name']);
        exit;
    }
    
    // Validate card
    if (!$cardNumberLast4 || !preg_match('/^\d{4}$/', $cardNumberLast4)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid card']);
        exit;
    }
    
    // Insert donation using PDO
    $insertStmt = $pdo->prepare('INSERT INTO donations (user_id, amount, feature_name) VALUES (?, ?, ?)');
    $insertStmt->execute([$user_id, $amount, $feature_name]);
    $donation_id = $pdo->lastInsertId();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Success', 'donation_id' => $donation_id]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

ob_end_flush();
?>

