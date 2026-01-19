<?php
$info = (object)[];

// optional category filter
$category = isset($DATA_OBJ->category) ? intval($DATA_OBJ->category) : 0;

if($category > 0){
    $query = "select p.*, c.name as category_name from products p left join category c on p.categoryId = c.id where p.categoryId = :category";
    $result = $DB->read($query, ['category' => $category]);
}else{
    $query = "select p.*, c.name as category_name from products p left join category c on p.categoryId = c.id order by p.id desc limit 20";
    $result = $DB->read($query);
}

$info->data_type = 'get_products';
$info->products = $result ? $result : [];

echo json_encode($info);

// handle product create/update/delete via multipart/form-data or JSON
if(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == 'add_product'){
    // only shop or admin can add
    if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['shop','admin'])){ $info->message='Unauthorized'; echo json_encode($info); die; }

    // support both JSON (base64 image) and form upload
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : (isset($_POST['name']) ? trim($_POST['name']) : '');
    $categoryId = isset($DATA_OBJ->category_id) ? intval($DATA_OBJ->category_id) : (isset($_POST['category_id']) ? intval($_POST['category_id']) : 0);
    $price = isset($DATA_OBJ->price) ? floatval($DATA_OBJ->price) : (isset($_POST['price']) ? floatval($_POST['price']) : 0);
    $quantity = isset($DATA_OBJ->quantity) ? intval($DATA_OBJ->quantity) : (isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0);
    $shopId = isset($DATA_OBJ->shopId) ? intval($DATA_OBJ->shopId) : (isset($_POST['shopId']) ? intval($_POST['shopId']) : 0);

    if(empty($name) || !$categoryId){ $info->message = 'Name and category required'; echo json_encode($info); die; }

    // if shop role, try to find associated shop id (if shops.user_id exists)
    if($_SESSION['role'] == 'shop' && !$shopId){
        $s = $DB->read("select id from shops where user_id = :uid limit 1", ['uid'=>$_SESSION['userid']]);
        if($s) $shopId = $s[0]->id;
    }

    if(!$shopId){ $info->message = 'shopId required'; echo json_encode($info); die; }

    $imagePath = null;
    // handle uploaded file
    if(isset($_FILES['image']) && $_FILES['image']['tmp_name']){
        $up = $_FILES['image'];
        $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../../assets/uploads/products';
        if(!is_dir($dir)) @mkdir($dir, 0755, true);
        $filename = uniqid('prod_') . '.' . ($ext ? $ext : 'jpg');
        $dest = $dir . '/' . $filename;
        if(move_uploaded_file($up['tmp_name'], $dest)){
            $imagePath = '/assets/uploads/products/' . $filename;
        }
    } elseif(isset($DATA_OBJ->image_base64) && $DATA_OBJ->image_base64){
        // data URL or base64 string
        $b = $DATA_OBJ->image_base64;
        if(preg_match('/^data:(image\/[^;]+);base64,(.+)$/', $b, $m)){
            $mime = $m[1]; $data = $m[2];
            $ext = explode('/', $mime)[1];
            $dir = __DIR__ . '/../../assets/uploads/products'; if(!is_dir($dir)) @mkdir($dir,0755,true);
            $filename = uniqid('prod_') . '.' . $ext;
            $dest = $dir . '/' . $filename;
            if(file_put_contents($dest, base64_decode($data)) !== false){ $imagePath = '/assets/uploads/products/' . $filename; }
        } else {
            // raw base64
            $data = $b; $dir = __DIR__ . '/../../assets/uploads/products'; if(!is_dir($dir)) @mkdir($dir,0755,true);
            $filename = uniqid('prod_') . '.jpg'; $dest = $dir . '/' . $filename; if(file_put_contents($dest, base64_decode($data)) !== false){ $imagePath = '/assets/uploads/products/' . $filename; }
        }
    }

    $params = ['name'=>$name, 'categoryId'=>$categoryId, 'price'=>$price, 'quantity'=>$quantity, 'shopId'=>$shopId];
    if($imagePath) $params['image'] = $imagePath;

    // build insert
    if(isset($params['image'])){
        $ok = $DB->write("insert into products (name, categoryId, price, quantity, shopId, image) values (:name, :categoryId, :price, :quantity, :shopId, :image)", $params);
    } else {
        $ok = $DB->write("insert into products (name, categoryId, price, quantity, shopId) values (:name, :categoryId, :price, :quantity, :shopId)", $params);
    }

    $info->data_type = 'add_product';
    $info->message = $ok ? 'Product added' : 'Could not add product';
    echo json_encode($info); die;
}
