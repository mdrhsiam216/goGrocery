<?php

// Get shop data for dashboard
// Expected data: userId (from session or request)

$userId = isset($DATA_OBJ->userId) ? $DATA_OBJ->userId : $_SESSION['userid'];

if (!$userId) {
    $info->error = true;
    $info->message = "User ID is required";
    echo json_encode($info);
    die;
}

// Get shop info by userId
$query = "SELECT * FROM shops WHERE userId = ?";
$shop = $DB->read($query, [$userId]);

if (!$shop) {
    $info->error = true;
    $info->message = "Shop not found";
    echo json_encode($info);
    die;
}

$shopId = $shop[0]->id;

// Get total products for this shop
$queryProducts = "SELECT COUNT(*) as total FROM products WHERE shopId = ?";
$productsResult = $DB->read($queryProducts, [$shopId]);
$totalProducts = $productsResult ? $productsResult[0]->total : 0;

// Get total orders for this shop
$queryOrders = "SELECT COUNT(*) as total FROM orders WHERE shopId = ?";
$ordersResult = $DB->read($queryOrders, [$shopId]);
$totalOrders = $ordersResult ? $ordersResult[0]->total : 0;

// Get recent orders
$queryRecentOrders = "
    SELECT 
        o.id,
        o.customerId,
        o.status,
        o.amount,
        o.method,
        c.location as customer_location,
        u.name as customer_name
    FROM orders o
    JOIN customers c ON o.customerId = c.id
    JOIN users u ON c.userId = u.id
    WHERE o.shopId = ?
    ORDER BY o.id DESC
    LIMIT 10
";
$recentOrders = $DB->read($queryRecentOrders, [$shopId]);

// Build response
$info->success = true;
$info->data = (object)[
    'shop_id' => $shopId,
    'shop_name' => 'Shop',
    'location' => $shop[0]->location,
    'status' => $shop[0]->status,
    'total_products' => $totalProducts,
    'total_orders' => $totalOrders,
    'recent_orders' => $recentOrders ? $recentOrders : []
];

echo json_encode($info);
