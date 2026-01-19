<?php
$info = (object)[];
$userId = isset($_SESSION['userid']) ? $_SESSION['userid'] : 0;
if(!$userId){ $info->message='Not logged in'; echo json_encode($info); die; }

if($DATA_OBJ->data_type == 'save_profile'){
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : null;
    $email = isset($DATA_OBJ->email) ? trim($DATA_OBJ->email) : null;
    $params = ['id'=>$userId];
    $query = "update users set "; $sets = [];
    if($name !== null){ $sets[] = 'name = :name'; $params['name']=$name; }
    if($email !== null){ $sets[] = 'email = :email'; $params['email']=$email; }
    if(count($sets) == 0){ $info->message='Nothing to update'; echo json_encode($info); die; }
    $query .= implode(',', $sets) . ' where id = :id';
    $ok = $DB->write($query, $params);
    $info->message = $ok ? 'Saved' : 'Could not save'; echo json_encode($info); die;
}

if($DATA_OBJ->data_type == 'change_password'){
    $old = isset($DATA_OBJ->old_password) ? $DATA_OBJ->old_password : '';
    $new = isset($DATA_OBJ->new_password) ? $DATA_OBJ->new_password : '';
    if(empty($old) || empty($new)){ $info->message='Missing'; echo json_encode($info); die; }
    $res = $DB->read("select password from users where id = :id limit 1", ['id'=>$userId]);
    if(!$res){ $info->message='User not found'; echo json_encode($info); die; }
    $pw = $res[0]->password;
    if(password_verify($old, $pw) || $old == $pw){
        $ok = $DB->write("update users set password = :pw where id = :id", ['pw'=>password_hash($new, PASSWORD_DEFAULT), 'id'=>$userId]);
        $info->message = $ok ? 'Password changed' : 'Could not change'; echo json_encode($info); die;
    } else { $info->message='Wrong password'; echo json_encode($info); die; }
}
