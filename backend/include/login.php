<?php

$data = [];
$data['email'] = $DATA_OBJ->email;
$password = $DATA_OBJ->password;

$query = "select * from users where email = :email limit 1";
$result = $DB->read($query, $data);

if($result){
    $row = $result[0];
    if($password == $row->password){ // Note: You should use password_verify in production, simplistic comparison as requested
        $_SESSION['userid'] = $row->id;
        $_SESSION['role'] = $row->role;
        $role = isset($row->role) ? $row->role : 'customer';
        $info->message = "Login successful";
        $info->data_type = "login";
        $info->role = $role;

        // Provide a simple role-based redirect (paths are relative to views/common/login.html)
        switch($role){
            case 'admin':
                $info->redirect = "../admin/dashboard.html";
                break;
            case 'shop':
                $info->redirect = "../shop/dashboard.html";
                break;
            case 'manager':
                $info->redirect = "../manager/dashboard.html";
                break;
            default:
                $info->redirect = "../customer/shops.html";
        }

        echo json_encode($info);
        die;
    }
}

$info->message = "Wrong email or password";
$info->data_type = "login";
echo json_encode($info);
