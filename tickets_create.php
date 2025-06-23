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

// Validate input
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';
$user_email = $_POST['user_email'] ?? '';
$name = $_POST['name'] ?? '';
$company = $_POST['company'] ?? '';
$priority = $_POST['priority'] ?? 'medium';
$category_id = $_POST['category_id'] ?? null;
$custom_fields_json = $_POST['custom_fields'] ?? '{}';
$custom_fields = json_decode($custom_fields_json, true);
if (!is_array($custom_fields)) $custom_fields = [];

if (!$subject || !$message || !$user_email) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Fetch active custom fields for tickets
$stmt_cf = $pdo->prepare("SELECT * FROM custom_fields WHERE zone = 'tickets' AND is_active = 1");
$stmt_cf->execute();
$active_custom_fields = $stmt_cf->fetchAll(PDO::FETCH_ASSOC);

// Validate required custom fields
$custom_field_errors = [];
foreach ($active_custom_fields as $cf) {
    $fid = (string)$cf['id'];
    $is_required = $cf['required'];
    $value = $custom_fields[$fid] ?? '';
    if ($is_required && ($value === '' || $value === null)) {
        $custom_field_errors[$fid] = 'This field is required.';
    }
}
if (!empty($custom_field_errors)) {
    echo json_encode(['success' => false, 'error' => 'Missing required custom fields', 'custom_field_errors' => $custom_field_errors]);
    exit;
}

// Insert ticket
$fields = ['subject', 'message', 'priority', 'email'];
$values = [$subject, $message, $priority, $user_email];
$placeholders = ['?', '?', '?', '?'];
if (!empty($name)) {
    $fields[] = 'name';
    $values[] = $name;
    $placeholders[] = '?';
}
if (!empty($company)) {
    $fields[] = 'company';
    $values[] = $company;
    $placeholders[] = '?';
}
if (!empty($category_id)) {
    $fields[] = 'category_id';
    $values[] = $category_id;
    $placeholders[] = '?';
}
$query = "INSERT INTO tickets (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
$stmt = $pdo->prepare($query);
$stmt->execute($values);
$ticket_id = $pdo->lastInsertId();

// Insert custom field values
foreach ($active_custom_fields as $cf) {
    $fid = (string)$cf['id'];
    $value = $custom_fields[$fid] ?? '';
    $stmt_cfval = $pdo->prepare("INSERT INTO custom_field_values (field_id, value, item_id) VALUES (?, ?, ?)");
    $stmt_cfval->execute([$fid, $value, $ticket_id]);
}

echo json_encode(['success' => true, 'ticket_id' => $ticket_id]); 