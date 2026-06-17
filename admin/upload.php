<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Create upload directory
$upload_dir = '../assets/images/products/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$response = ['success' => false, 'image_url' => '', 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_image'])) {
    $file = $_FILES['product_image'];
    
    // Check upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['error'] = 'Upload error: ' . $file['error'];
        echo json_encode($response);
        exit;
    }
    
    // Validate file type
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif'];
    $mime_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed)) {
        $response['error'] = 'Only JPG, PNG, WEBP, GIF images allowed. Got: ' . $mime_type;
        echo json_encode($response);
        exit;
    }
    
    // Validate size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $response['error'] = 'Image too large. Max 5MB. Got: ' . round($file['size'] / 1024 / 1024, 2) . 'MB';
        echo json_encode($response);
        exit;
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $response['success'] = true;
        $response['image_url'] = '/assets/images/products/' . $filename;
    } else {
        $response['error'] = 'Failed to move uploaded file. Check folder permissions.';
    }
} else {
    $response['error'] = 'No file uploaded';
}

header('Content-Type: application/json');
echo json_encode($response);
?>