<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Authenticate
api_authenticate();

// Support JSON POST bodies
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        $_POST = array_merge($_POST, $input);
    }
}

// Get ticket_id, user_email, and message
$ticket_id = $_POST['ticket_id'] ?? '';
$user_email = $_POST['user_email'] ?? '';
$message = $_POST['message'] ?? '';
if (!$ticket_id || !$user_email || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Lookup user_id if user_email matches a user
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$user_email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $user ? $user['id'] : null;

// Insert reply
$stmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
$stmt->execute([$ticket_id, $user_id, $message]);
$reply_id = $pdo->lastInsertId();

// Update ticket's updated_at
$stmt = $pdo->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?");
$stmt->execute([$ticket_id]);

echo json_encode(['success' => true, 'reply_id' => $reply_id]); 