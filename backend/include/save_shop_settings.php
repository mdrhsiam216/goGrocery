<?php

// Save shop settings
// Expected data: location, status

try {
    $userId = $_SESSION['userid'];
    $location = isset($DATA_OBJ->location) ? trim($DATA_OBJ->location) : null;
    $status = isset($DATA_OBJ->status) ? trim($DATA_OBJ->status) : null;

    if (!$location) {
        $info->error = true;
        $info->message = "Location is required";
        echo json_encode($info);
        die;
    }

    // Validate status
    if (!$status || !in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }

    // Update shop
    $query = "UPDATE shops SET location = ?, status = ? WHERE userId = ?";
    $result = $DB->write($query, [$location, $status, $userId]);

    if ($result) {
        $info->success = true;
        $info->message = "Settings saved successfully";
    } else {
        $info->error = true;
        $info->message = "Failed to save settings";
    }
} catch (Exception $e) {
    $info->error = true;
    $info->message = "Error: " . $e->getMessage();
}

echo json_encode($info);
