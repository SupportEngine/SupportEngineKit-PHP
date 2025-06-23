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

// Get ticket_id and user_email
$ticket_id = $_POST['ticket_id'] ?? $_GET['ticket_id'] ?? '';
$user_email = $_POST['user_email'] ?? $_GET['user_email'] ?? '';
if (!$ticket_id || !$user_email) {
    echo json_encode(['success' => false, 'error' => 'Missing ticket_id or user_email']);
    exit;
}

// Fetch ticket
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND (email = ? OR name = ?)");
$stmt->execute([$ticket_id, $user_email, $user_email]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ticket) {
    echo json_encode(['success' => false, 'error' => 'Ticket not found or no permission']);
    exit;
}

// Fetch replies
$stmt = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY created_at ASC");
$stmt->execute([$ticket_id]);
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch custom field values
$stmt_cf = $pdo->prepare("SELECT cf.id, cf.name, cf.type, cf.required, v.value FROM custom_fields cf LEFT JOIN custom_field_values v ON cf.id = v.field_id AND v.item_id = ? WHERE cf.zone = 'tickets' AND cf.is_active = 1");
$stmt_cf->execute([$ticket_id]);
$custom_fields = [];
foreach ($stmt_cf->fetchAll(PDO::FETCH_ASSOC) as $cf) {
    $custom_fields[$cf['id']] = [
        'name' => $cf['name'],
        'type' => $cf['type'],
        'required' => $cf['required'],
        'value' => $cf['value']
    ];
}

echo json_encode(['success' => true, 'ticket' => $ticket, 'replies' => $replies, 'custom_fields' => $custom_fields]); 