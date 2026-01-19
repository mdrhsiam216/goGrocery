<?php
$info = (object)[];

// Validate coupon code
if($DATA_OBJ->data_type == 'validate_coupon'){
    $code = isset($DATA_OBJ->code) ? trim($DATA_OBJ->code) : '';
    
    if(empty($code)){
        $info->valid = false;
        $info->message = 'Please enter a coupon code';
        echo json_encode($info);
        die;
    }

    // Check if coupon exists and has available quantity
    $query = "select * from coupon where name = :name and quantity > claimedQuantity limit 1";
    $res = $DB->read($query, ['name' => $code]);
    
    if($res && count($res) > 0){
        $coupon = $res[0];
        $info->valid = true;
        $info->code = $code;
        $info->percentage = $coupon->percentage;
        $info->message = "Coupon applied successfully";
    } else {
        $info->valid = false;
        $info->message = "Coupon not found or already used up";
    }
    
    echo json_encode($info);
    die;
}
?>
