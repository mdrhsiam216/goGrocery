<?php

$data = [];
$data['email'] = $DATA_OBJ->email;
$password = $DATA_OBJ->password;

$query = "select * from users where email = :email limit 1";
$result = $DB->read($query, $data);

if($result){
    $row = $result[0];
    // Support both hashed and legacy plain-text passwords
    $validPassword = false;
    if(password_verify($password, $row->password)){
        $validPassword = true;
    } elseif($password == $row->password){
        // legacy plain-text match
        $validPassword = true;
    }

    if($validPassword){
        // If legacy plain-text matched, re-hash and store the password
        if(!password_needs_rehash($row->password, PASSWORD_DEFAULT) && password_hash($password, PASSWORD_DEFAULT) !== $row->password){
            $updateQuery = "update users set password = :password where id = :id";
            $DB->write($updateQuery, ['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $row->id]);
        }

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
