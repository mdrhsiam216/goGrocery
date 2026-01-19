<?php

$info = (object)[];

if(!isset($_SESSION['userid'])){
    $info->logged_in = false;
    echo json_encode($info);
    die;
}

$userId = $_SESSION['userid'];

$query = "select * from users where id = :id limit 1";
$result = $DB->read($query, ['id'=>$userId]);

if($result){
    $user = $result[0];
    $info->logged_in = true;
    $info->data_type = 'user_info';
    $info->user = new stdClass();
    $info->user->id = $user->id;
    $info->user->name = isset($user->name) ? $user->name : '';
    $info->user->email = isset($user->email) ? $user->email : '';
    $info->user->role = isset($user->role) ? $user->role : 'customer';
    // include image if available
    if(isset($user->image)){
        $info->user->image = $user->image;
    }
    echo json_encode($info);
    die;
}

$info->logged_in = false;
echo json_encode($info);
