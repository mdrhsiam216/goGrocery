<?php

// Get shop settings
// Expected data: userId

$userId = isset($DATA_OBJ->userId) ? $DATA_OBJ->userId : $_SESSION['userid'];

if (!$userId) {
    $info->error = true;
    $info->message = "User ID is required";
    echo json_encode($info);
    die;
}

// Get shop info by userId
$query = "SELECT id, location, status FROM shops WHERE userId = ?";
$shop = $DB->read($query, [$userId]);

if (!$shop) {
    $info->error = true;
    $info->message = "Shop not found";
    echo json_encode($info);
    die;
}

$info->success = true;
$info->shop = $shop[0];

echo json_encode($info);
