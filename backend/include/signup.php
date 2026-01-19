<?php

// Collect data
$data = [];
$data['name'] = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : "";
$data['email'] = isset($DATA_OBJ->email) ? trim($DATA_OBJ->email) : "";
$data['password'] = isset($DATA_OBJ->password) ? $DATA_OBJ->password : "";

// Accept role from client if provided, otherwise default to customer
$allowed_roles = ['admin','customer','manager','rider','shop'];
$data['role'] = (isset($DATA_OBJ->role) && in_array($DATA_OBJ->role, $allowed_roles)) ? $DATA_OBJ->role : 'customer';

// Simple validation
if(empty($data['name']) || empty($data['email']) || empty($data['password'])){
    $info->message = "Please fill in all fields";
    $info->data_type = "signup";
    echo json_encode($info);
    die;
}

// Check if email exists
$query = "select * from users where email = :email limit 1";
$result = $DB->read($query, ['email' => $data['email']]);

if($result){
    $info->message = "That email is already in use";
    $info->data_type = "signup";
    echo json_encode($info);
    die;
}

// Save to DB
$image = isset($DATA_OBJ->image) ? trim($DATA_OBJ->image) : null;
$data['image'] = $image;

$query = "insert into users (name, email, password, role, image) values (:name, :email, :password, :role, :image)";
$result = $DB->write($query, $data);

if($result){
    // If customer role, also create an entry in customers table
    if($data['role'] == 'customer'){
        // Get the last inserted user id
        $userRes = $DB->read("select id from users where email = :email limit 1", ['email' => $data['email']]);
        if($userRes){
            $userId = $userRes[0]->id;
            $location = isset($DATA_OBJ->location) ? trim($DATA_OBJ->location) : null;
            $DB->write("insert into customers (userId, location) values (:userId, :location)", ['userId' => $userId, 'location' => $location]);
        }
    }
    
    $info->message = "Your profile was created successfully";
    $info->data_type = "signup";
    echo json_encode($info);
}else{
    $info->message = "An error occurred while creating your profile";
    $info->data_type = "signup";
    echo json_encode($info);
}
