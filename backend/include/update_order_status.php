<?php

// Update order status
// Expected data: orderId, status

$orderId = isset($DATA_OBJ->orderId) ? $DATA_OBJ->orderId : null;
$newStatus = isset($DATA_OBJ->status) ? $DATA_OBJ->status : null;

if (!$orderId || !$newStatus) {
    $info->error = true;
    $info->message = "Order ID and status are required";
    echo json_encode($info);
    die;
}

// Validate status
$validStatuses = ['pending', 'delivering', 'delivered', 'rejected'];
if (!in_array($newStatus, $validStatuses)) {
    $info->error = true;
    $info->message = "Invalid status";
    echo json_encode($info);
    die;
}

// Update order status
$query = "UPDATE orders SET status = ? WHERE id = ?";
$result = $DB->write($query, [$newStatus, $orderId]);

if ($result) {
    $info->success = true;
    $info->message = "Order status updated successfully";
} else {
    $info->error = true;
    $info->message = "Failed to update order status";
}

echo json_encode($info);
