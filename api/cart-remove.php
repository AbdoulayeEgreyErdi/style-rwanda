<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['key'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$key = $data['key'];

if (isset($_SESSION['cart'][$key])) {
    unset($_SESSION['cart'][$key]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Item not found']);
}
?>