<?php
session_start();
require 'php/db.php';

echo "<h1>🔍 Debug Information</h1>";

echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    echo "<h2>Database User Record:</h2>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    if ($user && !isset($_SESSION['role'])) {
        echo "<h2>⚠️ ISSUE FOUND:</h2>";
        echo "User has role '<strong>" . htmlspecialchars($user['role']) . "</strong>' in database,";
        echo "<br>but session doesn't have 'role' key!";
        echo "<br><br>";
        echo "This means the login handler is NOT storing the role in \$_SESSION.";
        echo "<br><br>";
        echo "<strong>Solution:</strong> Need to find login handler and add:<br>";
        echo "<code>\$_SESSION['role'] = \$user['role'];</code>";
    }
}

echo "<br><br><a href='dashboard.php'>← Back to Dashboard</a>";
?>
