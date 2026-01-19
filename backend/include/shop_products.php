<?php

// Get all products for a shop
// Expected data: userId (from session or request)

$userId = isset($DATA_OBJ->userId) ? $DATA_OBJ->userId : $_SESSION['userid'];

if (!$userId) {
    $info->error = true;
    $info->message = "User ID is required";
    echo json_encode($info);
    die;
}

// Get shop info by userId
$query = "SELECT id FROM shops WHERE userId = ?";
$shop = $DB->read($query, [$userId]);

if (!$shop) {
    $info->error = true;
    $info->message = "Shop not found";
    echo json_encode($info);
    die;
}

$shopId = $shop[0]->id;

// Get all products for this shop
$queryProducts = "
    SELECT 
        p.id,
        p.name,
        p.categoryId,
        p.price,
        p.quantity,
        p.image
    FROM products p
    WHERE p.shopId = ?
    ORDER BY p.id DESC
";
$products = $DB->read($queryProducts, [$shopId]);

// Build response
$info->success = true;
$info->products = $products ? $products : [];

echo json_encode($info);
