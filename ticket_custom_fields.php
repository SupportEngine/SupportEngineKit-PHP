<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $stmt = $pdo->prepare("SELECT id, name, type, required, possible_values FROM custom_fields WHERE zone = 'tickets' AND is_active = 1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute();
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'fields' => $fields]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 