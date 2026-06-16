<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['key']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$key = $data['key'];
$quantity = max(1, (int)$data['quantity']);

if (isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key]['quantity'] = $quantity;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Item not found']);
}
?>