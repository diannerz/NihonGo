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

// Example for processing a donation and updating the database

// Assume $user is the logged-in user and $amount is the donation amount
$amount = $_POST['amount']; // Amount received from the donation form

// Insert the donation into the database
$stmt = $pdo->prepare("INSERT INTO donations (user_id, amount, donation_time) VALUES (?, ?, NOW())");
$stmt->execute([$user['id'], $amount]);

// Update the user's donation status (if needed)
$stmt = $pdo->prepare("UPDATE users SET total_donated = total_donated + ? WHERE id = ?");
$stmt->execute([$amount, $user['id']]);

// You would also handle logic here to call the payment gateway (e.g., PayPal) to process the payment

?>
