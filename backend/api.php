<?php
// Simple API Router
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        require 'login.php';
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid API action']);
        break;
}
?>
