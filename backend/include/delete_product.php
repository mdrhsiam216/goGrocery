<?php

// Delete product
// Expected data: productId

$productId = isset($DATA_OBJ->productId) ? $DATA_OBJ->productId : null;

if (!$productId) {
    $info->error = true;
    $info->message = "Product ID is required";
    echo json_encode($info);
    die;
}

// Delete product
$query = "DELETE FROM products WHERE id = ?";
$result = $DB->write($query, [$productId]);

if ($result) {
    $info->success = true;
    $info->message = "Product deleted successfully";
} else {
    $info->error = true;
    $info->message = "Failed to delete product";
}

echo json_encode($info);
