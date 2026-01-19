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
