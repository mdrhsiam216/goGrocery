<?php
$info = (object)[];

// Ensure session user
$userId = isset($_SESSION['userid']) ? $_SESSION['userid'] : 0;
if(!$userId){
    $info->logged_in = false;
    echo json_encode($info);
    die;
}

// Get customer id
$cust = $DB->read("select id from customers where userId = :uid limit 1", ['uid'=>$userId]);
$customerId = ($cust && count($cust)>0) ? $cust[0]->id : null;

if(!$customerId){
    // no customer record -> no orders, but user is authenticated
    $info->logged_in = true;
    $info->data_type = 'get_orders_view';
    $info->orders = [];
    echo json_encode($info);
    die;
}

// Fetch orders for this customer
$orders = $DB->read(
    "select o.id, o.shopId, o.status, o.amount, o.method, o.voucher, s.name as shop_name from orders o left join shops s on o.shopId = s.id where o.customerId = :cid order by o.id desc",
    ['cid'=>$customerId]
);

$out = [];
if($orders){
    foreach($orders as $o){
        $oid = $o->id;
        $items = $DB->read("select oi.id, oi.productId, oi.quantity, p.name, p.price from orderItems oi left join products p on oi.productId = p.id where oi.ordersId = :oid", ['oid'=>$oid]);
        $o->items = $items ? $items : [];
        $out[] = $o;
    }
}

$info->logged_in = true;
$info->data_type = 'get_orders_view';
$info->orders = $out;
echo json_encode($info);
die;
