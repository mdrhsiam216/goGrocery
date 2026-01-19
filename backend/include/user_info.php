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
    
    // Get location from customers table if user is a customer
    if($user->role == 'customer'){
        $custRes = $DB->read("select location from customers where userId = :userId limit 1", ['userId' => $userId]);
        if($custRes){
            $info->user->location = $custRes[0]->location;
        }
    }
    
    // Construct image path if image filename exists
    if(isset($user->image) && !empty($user->image)){
        $info->user->image = '/goGrocery/assets/images/users/' . $user->image;
    } else {
        // Provide a placeholder avatar with local file
        $info->user->image = '/goGrocery/assets/images/placeholder.svg';
    }
    
    echo json_encode($info);
    die;
}

$info->logged_in = false;
echo json_encode($info);
