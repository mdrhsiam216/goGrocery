<?php
if($DATA_OBJ->data_type == "get_categories") {
    // Get all categories
    $query = "SELECT id, name FROM category ORDER BY name ASC";
    $categories = $DB->read($query);
    
    $info->categories = $categories ? $categories : [];
}
elseif($DATA_OBJ->data_type == "add_category") {
    // Add new category
    $name = isset($DATA_OBJ->name) ? $DATA_OBJ->name : null;
    
    if (!$name) {
        $info->error = true;
        $info->message = "Category name is required";
        echo json_encode($info);
        die;
    }
    
    $query = "INSERT INTO category (name) VALUES (?)";
    $result = $DB->write($query, [$name]);
    
    if ($result) {
        $info->success = true;
        $info->message = "Category added successfully";
    } else {
        $info->error = true;
        $info->message = "Failed to add category";
    }
}
elseif($DATA_OBJ->data_type == "update_category") {
    // Update category
    $id = isset($DATA_OBJ->id) ? $DATA_OBJ->id : null;
    $name = isset($DATA_OBJ->name) ? $DATA_OBJ->name : null;
    
    if (!$id || !$name) {
        $info->error = true;
        $info->message = "Category ID and name are required";
        echo json_encode($info);
        die;
    }
    
    $query = "UPDATE category SET name = ? WHERE id = ?";
    $result = $DB->write($query, [$name, $id]);
    
    if ($result) {
        $info->success = true;
        $info->message = "Category updated successfully";
    } else {
        $info->error = true;
        $info->message = "Failed to update category";
    }
}
elseif($DATA_OBJ->data_type == "delete_category") {
    // Delete category
    $id = isset($DATA_OBJ->id) ? $DATA_OBJ->id : null;
    
    if (!$id) {
        $info->error = true;
        $info->message = "Category ID is required";
        echo json_encode($info);
        die;
    }
    
    $query = "DELETE FROM category WHERE id = ?";
    $result = $DB->write($query, [$id]);
    
    if ($result) {
        $info->success = true;
        $info->message = "Category deleted successfully";
    } else {
        $info->error = true;
        $info->message = "Failed to delete category";
    }
}

echo json_encode($info);
