<?php
require 'check_auth.php';
require 'db.php';

header('Content-Type: application/json');

// Check if the user is logged in
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the donation amount from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$amount = $data['amount'] ?? 0;

// Check if the amount is valid
if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid donation amount']);
    exit;
}

// Insert the donation record into the database
$stmt = $pdo->prepare("INSERT INTO donations (user_id, amount) VALUES (:user_id, :amount)");
$stmt->execute([':user_id' => $user['id'], ':amount' => $amount]);

echo json_encode(['success' => true, 'message' => 'Donation successful']);
?>
