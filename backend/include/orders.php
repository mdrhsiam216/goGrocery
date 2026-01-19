<?php
$info = (object)[];
$userId = isset($_SESSION['userid']) ? $_SESSION['userid'] : 0;

// create order from cart (customer)
if($DATA_OBJ->data_type == 'create_order'){
    if(!$userId){ $info->message='Not logged in'; echo json_encode($info); die; }
    $shopId = isset($DATA_OBJ->shopId) ? intval($DATA_OBJ->shopId) : null;
    $method = isset($DATA_OBJ->method) ? $DATA_OBJ->method : 'cash';

    // get cart items
    $items = $DB->read("select c.quantity, p.id as productId, p.price from cart c left join products p on c.productId = p.id where c.userId = :uid", ['uid'=>$userId]);
    if(!$items || count($items) == 0){ $info->message='Cart empty'; echo json_encode($info); die; }
    $amount = 0;
    foreach($items as $it){ $amount += ($it->price * $it->quantity); }

    $ok = $DB->write("insert into orders (shopId, customerId, status, amount, method) values (:shopId, :cid, 'pending', :amount, :method)", ['shopId'=>$shopId, 'cid'=>$userId, 'amount'=>$amount, 'method'=>$method]);
    if(!$ok){ $info->message='Could not create order'; echo json_encode($info); die; }
    // get inserted order id
    $con = $DB->connect(); $orderId = $con->lastInsertId();
    foreach($items as $it){
        $DB->write("insert into orderItems (ordersId, productId, quantity) values (:oid, :pid, :q)", ['oid'=>$orderId, 'pid'=>$it->productId, 'q'=>$it->quantity]);
    }
    // clear cart
    $DB->write("delete from cart where userId = :uid", ['uid'=>$userId]);
    $info->message = 'Order created'; $info->data_type = 'create_order'; echo json_encode($info); die;
}

// get orders: admin gets all, shop gets shop orders, customer gets own
if($DATA_OBJ->data_type == 'get_orders'){
    if(!isset($_SESSION['role'])){ $info->message='Not authorized'; echo json_encode($info); die; }
    $role = $_SESSION['role'];
    if($role == 'admin'){
        $q = "select o.*, u.name as customer_name from orders o left join users u on o.customerId = u.id order by o.id desc";
        $res = $DB->read($q);
    } elseif($role == 'shop'){
        // find shop id by user? assume shop owner has shopId stored in users.image? (minimal) -> fetch by provided shopId param
        $shopId = isset($DATA_OBJ->shopId) ? intval($DATA_OBJ->shopId) : 0;
        $q = "select o.*, u.name as customer_name from orders o left join users u on o.customerId = u.id where o.shopId = :sid order by o.id desc";
        $res = $DB->read($q, ['sid'=>$shopId]);
    } else {
        // customer
        $q = "select o.*, u.name as customer_name from orders o left join users u on o.customerId = u.id where o.customerId = :cid order by o.id desc";
        $res = $DB->read($q, ['cid'=>$userId]);
    }
    $info->data_type = 'get_orders'; $info->orders = $res ? $res : []; echo json_encode($info); die;
}

// update order status (admin/shop)
if($DATA_OBJ->data_type == 'update_order'){
    if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','shop'])){ $info->message='Unauthorized'; echo json_encode($info); die; }
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0; $status = isset($DATA_OBJ->status) ? $DATA_OBJ->status : null;
    if(!$id || !$status){ $info->message='Invalid data'; echo json_encode($info); die; }
    $ok = $DB->write("update orders set status = :s where id = :id", ['s'=>$status, 'id'=>$id]);
    $info->message = $ok ? 'Updated' : 'Could not update'; echo json_encode($info); die;
}
