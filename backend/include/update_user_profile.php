<?php

// Update user profile (name and picture)

try {
    $userId = $_SESSION['userid'];
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : (isset($_POST['name']) ? trim($_POST['name']) : '');

    if (!$name) {
        $info->error = true;
        $info->message = "Name is required";
        echo json_encode($info);
        die;
    }

    $imagePath = null;

    // Handle profile picture upload
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['tmp_name']) {
        $up = $_FILES['profilePicture'];
        $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../../assets/images/users';
        
        if (!is_dir($dir))
            @mkdir($dir, 0755, true);
        
        $filename = uniqid('user_') . '.' . ($ext ? $ext : 'jpg');
        $dest = $dir . '/' . $filename;
        
        if (move_uploaded_file($up['tmp_name'], $dest)) {
            $imagePath = $filename;
        }
    }

    // Update user
    if ($imagePath) {
        $query = "UPDATE Users SET name = ?, image = ? WHERE id = ?";
        $result = $DB->write($query, [$name, $imagePath, $userId]);
    } else {
        $query = "UPDATE Users SET name = ? WHERE id = ?";
        $result = $DB->write($query, [$name, $userId]);
    }

    if ($result) {
        $info->success = true;
        $info->message = "Profile updated successfully";
    } else {
        $info->error = true;
        $info->message = "Failed to update profile";
    }
} catch (Exception $e) {
    $info->error = true;
    $info->message = "Error: " . $e->getMessage();
}

echo json_encode($info);
