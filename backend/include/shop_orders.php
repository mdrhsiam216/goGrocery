<?php

// Get all orders for a shop
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

// Get all orders for this shop with customer details
$queryOrders = "
    SELECT 
        o.id,
        o.customerId,
        o.status,
        o.amount,
        o.method,
        o.voucher,
        c.location as customer_location,
        u.name as customer_name,
        u.email as customer_email
    FROM orders o
    JOIN customers c ON o.customerId = c.id
    JOIN users u ON c.userId = u.id
    WHERE o.shopId = ?
    ORDER BY o.id DESC
";
$orders = $DB->read($queryOrders, [$shopId]);

// Build response
$info->success = true;
$info->orders = $orders ? $orders : [];

echo json_encode($info);
