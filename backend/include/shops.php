<?php
$info = (object)[];

// get shops
if($DATA_OBJ->data_type == 'get_shops'){
    // Get all shops
    $query = "select * from shops order by name";
    $res = $DB->read($query);
    $info->data_type = 'get_shops';
    $info->shops = $res ? $res : [];
    echo json_encode($info);
    die;
}

// admin add shop
if($DATA_OBJ->data_type == 'add_shop'){
    if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','manager','shop'])){
        $info->message = 'Unauthorized'; echo json_encode($info); die;
    }
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : '';
    $location = isset($DATA_OBJ->location) ? trim($DATA_OBJ->location) : '';
    if(empty($name)){ $info->message = 'Name required'; echo json_encode($info); die; }

    // if a shop user is creating, associate the shop to their user id
    $userCol = null; $params = ['name'=>$name, 'location'=>$location];
    if(isset($_SESSION['role']) && $_SESSION['role'] == 'shop'){
        $userCol = ', user_id';
        $params['user_id'] = $_SESSION['userid'];
        $ok = $DB->write("insert into shops (name, location, user_id) values (:name, :location, :user_id)", $params);
    } else {
        // admin/manager can optionally assign owner by providing user_id
        if(isset($DATA_OBJ->user_id)){
            $params['user_id'] = intval($DATA_OBJ->user_id);
            $ok = $DB->write("insert into shops (name, location, user_id) values (:name, :location, :user_id)", $params);
        } else {
            $ok = $DB->write("insert into shops (name, location) values (:name, :location)", $params);
        }
    }
    $info->message = $ok ? 'Shop created' : 'Could not create';
    echo json_encode($info); die;
}

if($DATA_OBJ->data_type == 'update_shop'){
    if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','manager','shop'])){ $info->message = 'Unauthorized'; echo json_encode($info); die; }
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0;
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : '';
    $loc = isset($DATA_OBJ->location) ? trim($DATA_OBJ->location) : null;
    if(!$id){ $info->message = 'Invalid id'; echo json_encode($info); die; }
    // shop users can only update their own shop
    if($_SESSION['role'] == 'shop'){
        $ok = $DB->write("update shops set name = :name, location = :loc where id = :id and user_id = :uid", ['name'=>$name, 'loc'=>$loc, 'id'=>$id, 'uid'=>$_SESSION['userid']]);
    } else {
        $ok = $DB->write("update shops set name = :name, location = :loc where id = :id", ['name'=>$name, 'loc'=>$loc, 'id'=>$id]);
    }
    $info->message = $ok ? 'Updated' : 'Could not update'; echo json_encode($info); die;
}

if($DATA_OBJ->data_type == 'delete_shop'){
    if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])){ $info->message = 'Unauthorized'; echo json_encode($info); die; }
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0; if(!$id){ $info->message = 'Invalid id'; echo json_encode($info); die; }
    $ok = $DB->write("delete from shops where id = :id", ['id'=>$id]); $info->message = $ok ? 'Deleted' : 'Could not delete'; echo json_encode($info); die;
}

// get shop for logged-in shop user
if($DATA_OBJ->data_type == 'get_my_shop'){
    if(!isset($_SESSION['role']) || $_SESSION['role'] != 'shop'){ $info->message = 'Unauthorized'; echo json_encode($info); die; }
    $uid = $_SESSION['userid'];
    $query = "select s.*, u.id as owner_id, u.name as owner_name, u.email as owner_email from shops s left join users u on u.id = s.user_id where s.user_id = :uid limit 1";
    $res = $DB->read($query, ['uid'=>$uid]);
    $info->data_type = 'get_my_shop';
    $info->shop = $res ? $res[0] : null;
    echo json_encode($info);
    die;
}
