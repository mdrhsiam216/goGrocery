<?php

// Update product
// Expected data: productId, name, categoryId, price, quantity

$productId = isset($DATA_OBJ->productId) ? $DATA_OBJ->productId : null;
$name = isset($DATA_OBJ->name) ? $DATA_OBJ->name : null;
$categoryId = isset($DATA_OBJ->categoryId) ? $DATA_OBJ->categoryId : null;
$price = isset($DATA_OBJ->price) ? $DATA_OBJ->price : null;
$quantity = isset($DATA_OBJ->quantity) ? $DATA_OBJ->quantity : null;

if (!$productId || !$name || !$categoryId || !$price || !$quantity) {
    $info->error = true;
    $info->message = "All fields are required";
    echo json_encode($info);
    die;
}

// Update product
$query = "UPDATE products SET name = ?, categoryId = ?, price = ?, quantity = ? WHERE id = ?";
$result = $DB->write($query, [$name, $categoryId, $price, $quantity, $productId]);

if ($result) {
    $info->success = true;
    $info->message = "Product updated successfully";
} else {
    $info->error = true;
    $info->message = "Failed to update product";
}

echo json_encode($info);
