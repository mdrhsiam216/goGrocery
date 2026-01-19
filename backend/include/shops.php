<?php
$info = (object) [];

// get shops
if ($DATA_OBJ->data_type == 'get_shops') {
    // Get all shops with owner names from Users table
    $query = "select shops.id, shops.userId, shops.location, shops.status, Users.name from shops left join Users on shops.userId = Users.id order by Users.name";
    $res = $DB->read($query);
    $info->data_type = 'get_shops';
    $info->shops = $res ? $res : [];
    echo json_encode($info);
    die;
}

// admin add shop
if ($DATA_OBJ->data_type == 'add_shop') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager', 'shop'])) {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : '';
    $location = isset($DATA_OBJ->location) ? trim($DATA_OBJ->location) : '';
    /* Name no longer in shops table
    if (empty($name)) {
        $info->message = 'Name required';
        echo json_encode($info);
        die;
    } 
    */

    // if a shop user is creating, associate the shop to their user id
    $userCol = null;
    $params = ['location' => $location];
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'shop') {
        $userCol = ', userId';
        $params['userId'] = $_SESSION['userid'];
        $ok = $DB->write("insert into shops (location, userId) values (:location, :userId)", $params);
    } else {
        // admin/manager can optionally assign owner by providing userId
        if (isset($DATA_OBJ->userId)) {
            $params['userId'] = intval($DATA_OBJ->userId);
            $ok = $DB->write("insert into shops (location, userId) values (:location, :userId)", $params);
        } else {
            $ok = $DB->write("insert into shops (location) values (:location)", $params);
        }
    }
    $info->message = $ok ? 'Shop created' : 'Could not create';
    echo json_encode($info);
    die;
}

if ($DATA_OBJ->data_type == 'update_shop') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager', 'shop'])) {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0;
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : '';
    $loc = isset($DATA_OBJ->location) ? trim($DATA_OBJ->location) : null;
    $status = isset($DATA_OBJ->status) ? $DATA_OBJ->status : null;

    if (!$id) {
        $info->message = 'Invalid id';
        echo json_encode($info);
        die;
    }

    $sql = "update shops set location = :loc";
    $params = ['loc' => $loc];

    if ($name) {
        // Update user name as shop name
        // This requires joining or separate update. Since this is update_shop, we might need to update users table.
        // For simplicity, let's assume get_my_shop fetches owner_name from users.
        // We can update users table here if we have userId.
        if ($_SESSION['role'] == 'shop') {
            $DB->write("update users set name = :name where id = :uid", ['name' => $name, 'uid' => $_SESSION['userid']]);
        }
    }

    /*
    if ($loc !== null) {
        $sql .= ", location = :loc";
        $params['loc'] = $loc;
    }
    */

    if ($status && in_array($status, ['active', 'inactive'])) {
        $sql .= ", status = :status";
        $params['status'] = $status;
    }

    $params['id'] = $id;

    // shop users can only update their own shop
    if ($_SESSION['role'] == 'shop') {
        $sql .= " where id = :id and userId = :uid";
        $params['uid'] = $_SESSION['userid'];
    } else {
        $sql .= " where id = :id";
    }

    // Since we are updating location mainly
    $ok = $DB->write($sql, $params);
    $info->message = $ok ? 'Updated' : 'Could not update';
    echo json_encode($info);
    die;
}

if ($DATA_OBJ->data_type == 'delete_shop') {
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
    $ok = $DB->write("delete from shops where id = :id", ['id' => $id]);
    $info->message = $ok ? 'Deleted' : 'Could not delete';
    echo json_encode($info);
    die;
}

// get shop for logged-in shop user
if ($DATA_OBJ->data_type == 'get_my_shop') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'shop') {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }
    $uid = $_SESSION['userid'];
    $query = "select s.*, u.id as owner_id, u.name as owner_name, u.email as owner_email, u.image as owner_image from shops s left join users u on u.id = s.userId where s.userId = :uid limit 1";
    $res = $DB->read($query, ['uid' => $uid]);
    $info->data_type = 'get_my_shop';
    $info->shop = $res ? $res[0] : null;
    echo json_encode($info);
    die;
}
