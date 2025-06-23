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

// Get user_email
$user_email = $_POST['user_email'] ?? $_GET['user_email'] ?? '';
if (!$user_email) {
    echo json_encode(['success' => false, 'error' => 'Missing user_email']);
    exit;
}

// Fetch tickets for this user (by email or user_id if exists)
$stmt = $pdo->prepare("SELECT id, subject, status, priority, created_at, updated_at FROM tickets WHERE email = ? OR name = ? ORDER BY updated_at DESC");
$stmt->execute([$user_email, $user_email]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'tickets' => $tickets]); 