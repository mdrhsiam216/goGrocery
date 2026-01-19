<?php

// Collect data
$data = [];
$data['name'] = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : "";
$data['email'] = isset($DATA_OBJ->email) ? trim($DATA_OBJ->email) : "";
$raw_password = isset($DATA_OBJ->password) ? $DATA_OBJ->password : "";

// Accept role from client if provided, otherwise default to customer
$allowed_roles = ['admin','customer','manager','rider','shop'];
$data['role'] = (isset($DATA_OBJ->role) && in_array($DATA_OBJ->role, $allowed_roles)) ? $DATA_OBJ->role : 'customer';

// Hash password before storing (simple, using PHP's password_hash)
$data['password'] = password_hash($raw_password, PASSWORD_DEFAULT);

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
    $info->message = "Your profile was created successfully";
    $info->data_type = "signup";
    echo json_encode($info);
}else{
    $info->message = "An error occurred while creating your profile";
    $info->data_type = "signup";
    echo json_encode($info);
}
