<?php
$info = (object) [];
$userId = isset($_SESSION['userid']) ? $_SESSION['userid'] : 0;

// create order from checkout (customer)
// creates one order per shop with the provided items
if ($DATA_OBJ->data_type == 'create_order') {
    if (!$userId) {
        $info->logged_in = false;
        echo json_encode($info);
        die;
    }

    $shopId = isset($DATA_OBJ->shopId) ? intval($DATA_OBJ->shopId) : 0;
    $amount = isset($DATA_OBJ->amount) ? floatval($DATA_OBJ->amount) : 0;
    $method = isset($DATA_OBJ->method) ? $DATA_OBJ->method : 'cash';
    $voucher = isset($DATA_OBJ->voucher) ? $DATA_OBJ->voucher : null;
    $items = isset($DATA_OBJ->items) ? $DATA_OBJ->items : [];

    if (!$shopId || !$amount || count($items) == 0) {
        $info->message = 'Invalid order data';
        echo json_encode($info);
        die;
    }

    // Get customer ID - create if doesn't exist
    $customerRes = $DB->read("select id from customers where userId = :uid limit 1", ['uid' => $userId]);
    $customerId = ($customerRes && count($customerRes) > 0) ? $customerRes[0]->id : null;

    if (!$customerId) {
        // Create customer record if it doesn't exist
        $userRes = $DB->read("select name, location from Users where id = :uid", ['uid' => $userId]);
        if ($userRes && count($userRes) > 0) {
            $user = $userRes[0];
            $location = isset($user->location) ? $user->location : '';
            $DB->write(
                "insert into customers (userId, location) values (:uid, :loc)",
                ['uid' => $userId, 'loc' => $location]
            );
            $con = $DB->connect();
            $customerId = $con->lastInsertId();
        }
    }

    if (!$customerId) {
        $info->message = 'Could not create customer record';
        echo json_encode($info);
        die;
    }

    // Create order
    $orderParams = [
        'shopId' => $shopId,
        'customerId' => $customerId,
        'amount' => $amount,
        'method' => $method,
        'status' => 'pending'
    ];

    if ($voucher) {
        $orderParams['voucher'] = $voucher;
        $orderSQL = "insert into orders (shopId, customerId, status, amount, method, voucher) values (:shopId, :customerId, :status, :amount, :method, :voucher)";
    } else {
        $orderSQL = "insert into orders (shopId, customerId, status, amount, method) values (:shopId, :customerId, :status, :amount, :method)";
    }

    $ok = $DB->write($orderSQL, $orderParams);

    if (!$ok) {
        $info->message = 'Could not create order: ' . (isset($GLOBALS['db_error']) ? $GLOBALS['db_error'] : 'Unknown error');
        $info->debug_shopId = $shopId;
        $info->debug_customerId = $customerId;
        $info->debug_amount = $amount;
        echo json_encode($info);
        die;
    }

    // Get inserted order ID from the return value
    $orderId = $ok;

    // Insert order items
    foreach ($items as $item) {
        $productId = isset($item->productId) ? intval($item->productId) : (isset($item['productId']) ? intval($item['productId']) : 0);
        $quantity = isset($item->quantity) ? intval($item->quantity) : (isset($item['quantity']) ? intval($item['quantity']) : 0);

        if ($productId > 0 && $quantity > 0) {
            $DB->write(
                "insert into orderItems (ordersId, productId, quantity) values (:oid, :pid, :q)",
                ['oid' => $orderId, 'pid' => $productId, 'q' => $quantity]
            );
        }
    }

    // Increment claimed quantity for voucher if used
    if ($voucher) {
        $DB->write(
            "update coupon set claimedQuantity = claimedQuantity + 1 where name = :name",
            ['name' => $voucher]
        );
    }

    $info->data_type = 'create_order';
    $info->order_id = $orderId;
    $info->message = 'Order created successfully';
    echo json_encode($info);
    die;
}

// get orders: admin gets all, shop gets shop orders, customer gets own
if ($DATA_OBJ->data_type == 'get_orders') {
    if (!isset($_SESSION['role'])) {
        $info->message = 'Not authorized';
        echo json_encode($info);
        die;
    }
    $role = $_SESSION['role'];
    if ($role == 'admin' || $role == 'manager') {
        $q = "select o.*, u.name as customer_name, s.name as shop_name from orders o left join users u on o.customerId = u.id left join shops s on o.shopId = s.id order by o.id desc";
        $res = $DB->read($q);
    } elseif ($role == 'shop') {
        // shop user gets their shop orders
        $shopId = isset($DATA_OBJ->shopId) ? intval($DATA_OBJ->shopId) : 0;
        $q = "select o.*, u.name as customer_name, s.name as shop_name from orders o left join users u on o.customerId = u.id left join shops s on o.shopId = s.id where o.shopId = :sid order by o.id desc";
        $res = $DB->read($q, ['sid' => $shopId]);
    } else {
        // customer gets their own orders
        $q = "select o.*, u.name as customer_name, s.name as shop_name from orders o left join users u on o.customerId = u.id left join shops s on o.shopId = s.id where o.customerId = (select id from customers where userId = :uid) order by o.id desc";
        $res = $DB->read($q, ['uid' => $userId]);
    }
    $info->data_type = 'get_orders';
    $info->orders = $res ? $res : [];
    echo json_encode($info);
    die;
}

// update order status (admin/shop)
if ($DATA_OBJ->data_type == 'update_order') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'shop', 'manager'])) {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0;
    $status = isset($DATA_OBJ->status) ? $DATA_OBJ->status : null;
    if (!$id || !$status) {
        $info->message = 'Invalid data';
        echo json_encode($info);
        die;
    }

    // shop restricted update
    if ($_SESSION['role'] == 'shop') {
        $check = $DB->read("select id from orders where id = :id and shopId = (select id from shops where user_id = :uid limit 1)", ['id' => $id, 'uid' => $_SESSION['userid']]);
        if (!$check || count($check) == 0) {
            $info->message = 'Order not found or access denied';
            echo json_encode($info);
            die;
        }
    }

    $ok = $DB->write("update orders set status = :s where id = :id", ['s' => $status, 'id' => $id]);
    $info->message = $ok ? 'Updated' : 'Could not update';
    echo json_encode($info);
    die;
}

// delete order (admin/manager)
if ($DATA_OBJ->data_type == 'delete_order') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0;
    if (!$id) {
        $info->message = 'Invalid id';
        echo json_encode($info);
        die;
    }
    $ok = $DB->write("delete from orders where id = :id", ['id' => $id]);
    $info->message = $ok ? 'Deleted' : 'Could not delete';
    echo json_encode($info);
    die;
}
