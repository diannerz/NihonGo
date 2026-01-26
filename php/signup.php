<?php
// php/signup.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// -------------------------------
// BASIC VALIDATION
// -------------------------------
if (!$username || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// -------------------------------
// STRICT EMAIL VALIDATION
// ONLY allow @gmail.com or @email.com
// -------------------------------
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format.']);
    exit;
}

if (
    !preg_match('/@gmail\.com$/i', $email) &&
    !preg_match('/@email\.com$/i', $email)
) {
    http_response_code(400);
    echo json_encode(['error' => 'Email must end with @gmail.com or @email.com']);
    exit;
}


// -------------------------------
// PASSWORD RULES
// -------------------------------
if (strlen($password) < 6 ||
    !preg_match('/[A-Za-z]/', $password) ||
    !preg_match('/\d/', $password)) {

    http_response_code(400);
    echo json_encode([
        'error' => 'Password must be at least 6 chars and include letters and numbers.'
    ]);
    exit;
}

try {
    // CHECK DUPLICATES
    $stmt = $pdo->prepare('SELECT id, username, email 
                           FROM users 
                           WHERE username = :u OR email = :e');
    $stmt->execute([':u' => $username, ':e' => $email]);
    $existing = $stmt->fetchAll();

    foreach ($existing as $row) {
        if (strtolower($row['username']) === strtolower($username)) {
            http_response_code(409);
            echo json_encode(['error' => 'username_taken']);
            exit;
        }
        if (strtolower($row['email']) === strtolower($email)) {
            http_response_code(409);
            echo json_encode(['error' => 'email_taken']);
            exit;
        }
    }

    // INSERT NEW USER
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash)
                             VALUES (:u, :e, :p)');
    $insert->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

    // AUTO LOGIN
    session_start();
    $_SESSION['user_id'] = (int)$pdo->lastInsertId();
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user';  // New users are always regular users

    echo json_encode(['success' => true]);

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error.']);
}
