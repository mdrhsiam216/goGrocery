<?php
$info = (object)[];

// Require DB and session to be available
$userId = isset($_SESSION['userid']) ? $_SESSION['userid'] : 0;

if($DATA_OBJ->data_type == 'cart_count'){
    if(!$userId){
        $info->logged_in = false;
        echo json_encode($info);
        die;
    }
    $query = "select sum(quantity) as cnt from cart where userId = :uid";
    $res = $DB->read($query, ['uid'=> $userId]);
    $cnt = 0;
    if($res && isset($res[0]->cnt)) $cnt = intval($res[0]->cnt);
    $info->data_type = 'cart_count';
    $info->count = $cnt;
    echo json_encode($info);
    die;
}

if($DATA_OBJ->data_type == 'add_to_cart'){
    if(!$userId){
        $info->logged_in = false;
        echo json_encode($info);
        die;
    }
    $productId = isset($DATA_OBJ->productId) ? intval($DATA_OBJ->productId) : 0;
    $quantity = isset($DATA_OBJ->quantity) ? intval($DATA_OBJ->quantity) : 1;
    if($productId <= 0){
        $info->message = 'Invalid product';
        echo json_encode($info);
        die;
    }

    // check if existing
    $query = "select * from cart where userId = :uid and productId = :pid limit 1";
    $res = $DB->read($query, ['uid'=>$userId, 'pid'=>$productId]);
    if($res){
        $existing = $res[0];
        $newQty = $existing->quantity + $quantity;
        $update = "update cart set quantity = :q where id = :id";
        $ok = $DB->write($update, ['q'=>$newQty, 'id'=>$existing->id]);
    } else {
        $insert = "insert into cart (productId, userId, quantity) values (:pid, :uid, :q)";
        $ok = $DB->write($insert, ['pid'=>$productId, 'uid'=>$userId, 'q'=>$quantity]);
    }

    if($ok){
        $info->message = 'Added to cart';
        $info->data_type = 'add_to_cart';
        echo json_encode($info);
        die;
    } else {
        $info->message = 'Could not add to cart';
        echo json_encode($info);
        die;
    }
}

if($DATA_OBJ->data_type == 'get_cart'){
    if(!$userId){
        $info->logged_in = false;
        echo json_encode($info);
        die;
    }
    $query = "select c.id as cartId, c.quantity, c.productId, p.id, p.name, p.categoryId, p.price, p.shopId, p.image, s.name as shop_name from cart c left join products p on c.productId = p.id left join shops s on p.shopId = s.id where c.userId = :uid";
    $res = $DB->read($query, ['uid'=>$userId]);
    $info->data_type = 'get_cart';
    $info->items = $res ? $res : [];
    echo json_encode($info);
    die;
}

// update cart quantity
if($DATA_OBJ->data_type == 'update_cart'){
    if(!$userId){ $info->logged_in = false; echo json_encode($info); die; }
    $cartId = isset($DATA_OBJ->cartId) ? intval($DATA_OBJ->cartId) : 0;
    $quantity = isset($DATA_OBJ->quantity) ? intval($DATA_OBJ->quantity) : 1;
    if(!$cartId || $quantity < 1){ $info->message='Invalid'; echo json_encode($info); die; }
    $ok = $DB->write("update cart set quantity = :q where id = :id and userId = :uid", ['q'=>$quantity, 'id'=>$cartId, 'uid'=>$userId]);
    $info->data_type = 'update_cart'; $info->ok = $ok; echo json_encode($info); die;
}

// remove from cart
if($DATA_OBJ->data_type == 'remove_from_cart'){
    if(!$userId){ $info->logged_in = false; echo json_encode($info); die; }
    $cartId = isset($DATA_OBJ->cartId) ? intval($DATA_OBJ->cartId) : 0;
    if(!$cartId){ $info->message='Invalid'; echo json_encode($info); die; }
    $ok = $DB->write("delete from cart where id = :id and userId = :uid", ['id'=>$cartId, 'uid'=>$userId]);
    $info->data_type = 'remove_from_cart'; $info->ok = $ok; echo json_encode($info); die;
}

// clear cart
if($DATA_OBJ->data_type == 'clear_cart'){
    if(!$userId){ $info->logged_in = false; echo json_encode($info); die; }
    $ok = $DB->write("delete from cart where userId = :uid", ['uid'=>$userId]);
    $info->data_type = 'clear_cart'; $info->ok = $ok; echo json_encode($info); die;
}
