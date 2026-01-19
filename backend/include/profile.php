<?php
$info = (object)[];
$userId = isset($_SESSION['userid']) ? $_SESSION['userid'] : 0;
if(!$userId){ $info->message='Not logged in'; echo json_encode($info); die; }

if($DATA_OBJ->data_type == 'save_profile'){
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : null;
    $email = isset($DATA_OBJ->email) ? trim($DATA_OBJ->email) : null;
    $location = isset($DATA_OBJ->location) ? trim($DATA_OBJ->location) : null;
    
    $params = ['id'=>$userId];
    $query = "update users set "; 
    $sets = [];
    
    if($name !== null){ $sets[] = 'name = :name'; $params['name']=$name; }
    if($email !== null){ $sets[] = 'email = :email'; $params['email']=$email; }
    
    // Handle image upload
    if(isset($_FILES['image']) && $_FILES['image']['tmp_name']){
        $up = $_FILES['image'];
        $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../../assets/images/users';
        if(!is_dir($dir)) @mkdir($dir, 0755, true);
        $filename = 'user_' . $userId . '_' . uniqid() . '.' . ($ext ? $ext : 'jpg');
        $dest = $dir . '/' . $filename;
        if(move_uploaded_file($up['tmp_name'], $dest)){
            $sets[] = 'image = :image';
            $params['image'] = $filename;
        }
    }
    
    if(count($sets) > 0){
        $query .= implode(',', $sets) . ' where id = :id';
        $DB->write($query, $params);
    }
    
    // Save location to customers table
    if($location !== null){
        $DB->write("update customers set location = :location where userId = :userId", ['location' => $location, 'userId' => $userId]);
    }
    
    $info->message = 'Saved'; 
    echo json_encode($info); 
    die;
}

if($DATA_OBJ->data_type == 'change_password'){
    $old = isset($DATA_OBJ->old_password) ? $DATA_OBJ->old_password : '';
    $new = isset($DATA_OBJ->new_password) ? $DATA_OBJ->new_password : '';
    if(empty($old) || empty($new)){ $info->message='Missing'; echo json_encode($info); die; }
    $res = $DB->read("select password from users where id = :id limit 1", ['id'=>$userId]);
    if(!$res){ $info->message='User not found'; echo json_encode($info); die; }
    $pw = $res[0]->password;
    // Simple plain-text password comparison
    if($old == $pw){
        $ok = $DB->write("update users set password = :pw where id = :id", ['pw'=>$new, 'id'=>$userId]);
        $info->message = $ok ? 'Password changed' : 'Could not change'; echo json_encode($info); die;
    } else { $info->message='Wrong password'; echo json_encode($info); die; }
}
