<?php
$info = (object)[];
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){ $info->message='Unauthorized'; echo json_encode($info); die; }

if($DATA_OBJ->data_type == 'get_users'){
    // include image for UI avatars
    $res = $DB->read("select id, name, email, role, image from users order by id desc");
    $info->data_type = 'get_users'; $info->users = $res ? $res : []; echo json_encode($info); die;
}

if($DATA_OBJ->data_type == 'update_user'){
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0; $role = isset($DATA_OBJ->role) ? $DATA_OBJ->role : null;
    if(!$id || !$role){ $info->message='Invalid'; echo json_encode($info); die; }
    $ok = $DB->write("update users set role = :r where id = :id", ['r'=>$role, 'id'=>$id]); $info->message = $ok ? 'Updated' : 'Could not update'; echo json_encode($info); die;
}

if($DATA_OBJ->data_type == 'delete_user'){
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0; if(!$id){ $info->message='Invalid'; echo json_encode($info); die; }
    $ok = $DB->write("delete from users where id = :id", ['id'=>$id]); $info->message = $ok ? 'Deleted' : 'Could not delete'; echo json_encode($info); die;
}

if($DATA_OBJ->data_type == 'create_user'){
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : '';
    $email = isset($DATA_OBJ->email) ? trim($DATA_OBJ->email) : '';
    $password = isset($DATA_OBJ->password) ? trim($DATA_OBJ->password) : '';
    $role = isset($DATA_OBJ->role) ? trim($DATA_OBJ->role) : 'customer';

    if(empty($name) || empty($email) || empty($password)){
        $info->message = 'All fields required'; echo json_encode($info); die;
    }

    // check email
    $check = $DB->read("select id from users where email = :email limit 1", ['email'=>$email]);
    if($check){ $info->message = 'Email already exists'; echo json_encode($info); die; }

    $ok = $DB->write("insert into users (name, email, password, role) values (:name, :email, :password, :role)", 
        ['name'=>$name, 'email'=>$email, 'password'=>$password, 'role'=>$role]);
    
    if($ok){
         // if customer, add to customers table
         if($role == 'customer'){
             $u = $DB->read("select id from users where email = :email limit 1", ['email'=>$email]);
             if($u){
                 $DB->write("insert into customers (userId) values (:uid)", ['uid'=>$u[0]->id]);
             }
         }
         $info->message = 'User created successfully';
    } else {
        $info->message = 'Could not create user';
    }
    echo json_encode($info); die;
}
