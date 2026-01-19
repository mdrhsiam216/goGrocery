<?php
$info = (object) [];

// Handle data_type first if it's set
if (isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == 'add_product') {
    // only shop or admin can add
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['shop', 'admin'])) {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }

    // support both JSON (base64 image) and form upload
    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : (isset($_POST['name']) ? trim($_POST['name']) : '');
    $categoryId = isset($DATA_OBJ->category_id) ? intval($DATA_OBJ->category_id) : (isset($_POST['category_id']) ? intval($_POST['category_id']) : 0);
    $price = isset($DATA_OBJ->price) ? floatval($DATA_OBJ->price) : (isset($_POST['price']) ? floatval($_POST['price']) : 0);
    $quantity = isset($DATA_OBJ->quantity) ? intval($DATA_OBJ->quantity) : (isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0);
    $shopId = isset($DATA_OBJ->shopId) ? intval($DATA_OBJ->shopId) : (isset($_POST['shopId']) ? intval($_POST['shopId']) : 0);

    if (empty($name) || !$categoryId) {
        $info->message = 'Name and category required';
        echo json_encode($info);
        die;
    }

    // if shop role, try to find associated shop id (if shops.user_id exists)
    if ($_SESSION['role'] == 'shop' && !$shopId) {
        $s = $DB->read("select id from shops where user_id = :uid limit 1", ['uid' => $_SESSION['userid']]);
        if ($s)
            $shopId = $s[0]->id;
    }

    if (!$shopId) {
        $info->message = 'shopId required';
        echo json_encode($info);
        die;
    }

    $imagePath = null;
    // handle uploaded file
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $up = $_FILES['image'];
        $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../../assets/images/products';
        if (!is_dir($dir))
            @mkdir($dir, 0755, true);
        $filename = uniqid('prod_') . '.' . ($ext ? $ext : 'jpg');
        $dest = $dir . '/' . $filename;
        if (move_uploaded_file($up['tmp_name'], $dest)) {
            $imagePath = $filename;
        }
    } elseif (isset($DATA_OBJ->image_base64) && $DATA_OBJ->image_base64) {
        // data URL or base64 string
        $b = $DATA_OBJ->image_base64;
        if (preg_match('/^data:(image\/[^;]+);base64,(.+)$/', $b, $m)) {
            $mime = $m[1];
            $data = $m[2];
            $ext = explode('/', $mime)[1];
            $dir = __DIR__ . '/../../assets/images/products';
            if (!is_dir($dir))
                @mkdir($dir, 0755, true);
            $filename = uniqid('prod_') . '.' . $ext;
            $dest = $dir . '/' . $filename;
            if (file_put_contents($dest, base64_decode($data)) !== false) {
                $imagePath = $filename;
            }
        } else {
            // raw base64
            $data = $b;
            $dir = __DIR__ . '/../../assets/images/products';
            if (!is_dir($dir))
                @mkdir($dir, 0755, true);
            $filename = uniqid('prod_') . '.jpg';
            $dest = $dir . '/' . $filename;
            if (file_put_contents($dest, base64_decode($data)) !== false) {
                $imagePath = $filename;
            }
        }
    }

    $params = ['name' => $name, 'categoryId' => $categoryId, 'price' => $price, 'quantity' => $quantity, 'shopId' => $shopId];
    if ($imagePath)
        $params['image'] = $imagePath;

    // build insert
    if (isset($params['image'])) {
        $ok = $DB->write("insert into products (name, categoryId, price, quantity, shopId, image) values (:name, :categoryId, :price, :quantity, :shopId, :image)", $params);
    } else {
        $ok = $DB->write("insert into products (name, categoryId, price, quantity, shopId) values (:name, :categoryId, :price, :quantity, :shopId)", $params);
    }

    $info->data_type = 'add_product';
    $info->message = $ok ? 'Product added' : 'Could not add product';
    echo json_encode($info);
    die;
}

if (isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == 'update_product') {
    // only shop or admin
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['shop', 'admin'])) {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }

    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
    if (!$id) {
        $info->message = 'Product ID required';
        echo json_encode($info);
        die;
    }

    // If shop, verify ownership
    if ($_SESSION['role'] == 'shop') {
        $check = $DB->read("select id from products where id = :id and shopId = (select id from shops where userId = :uid limit 1)", ['id' => $id, 'uid' => $_SESSION['userid']]);
        if (!$check || count($check) == 0) {
            $info->message = 'Product not found or access denied';
            echo json_encode($info);
            die;
        }
    }

    $name = isset($DATA_OBJ->name) ? trim($DATA_OBJ->name) : (isset($_POST['name']) ? trim($_POST['name']) : '');
    $categoryId = isset($DATA_OBJ->category_id) ? intval($DATA_OBJ->category_id) : (isset($_POST['category_id']) ? intval($_POST['category_id']) : 0);
    $price = isset($DATA_OBJ->price) ? floatval($DATA_OBJ->price) : (isset($_POST['price']) ? floatval($_POST['price']) : 0);
    $quantity = isset($DATA_OBJ->quantity) ? intval($DATA_OBJ->quantity) : (isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0);

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $up = $_FILES['image'];
        $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../../assets/images/products';
        if (!is_dir($dir))
            @mkdir($dir, 0755, true);
        $filename = uniqid('prod_') . '.' . ($ext ? $ext : 'jpg');
        $dest = $dir . '/' . $filename;
        if (move_uploaded_file($up['tmp_name'], $dest)) {
            $imagePath = $filename;
        }
    }

    $sql = "update products set name = :name, categoryId = :cat, price = :price, quantity = :qty";
    $params = ['name' => $name, 'cat' => $categoryId, 'price' => $price, 'qty' => $quantity, 'id' => $id];

    if ($imagePath) {
        $sql .= ", image = :img";
        $params['img'] = $imagePath;
    }
    $sql .= " where id = :id";

    $ok = $DB->write($sql, $params);
    $info->message = $ok ? 'Product updated' : 'No changes or update failed';
    echo json_encode($info);
    die;
}

if (isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == 'delete_product') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['shop', 'admin'])) {
        $info->message = 'Unauthorized';
        echo json_encode($info);
        die;
    }
    $id = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0;
    if (!$id) {
        $info->message = 'ID required';
        echo json_encode($info);
        die;
    }

    if ($_SESSION['role'] == 'shop') {
        $check = $DB->read("select id from products where id = :id and shopId = (select id from shops where userId = :uid limit 1)", ['id' => $id, 'uid' => $_SESSION['userid']]);
        if (!$check) {
            $info->message = 'Access denied';
            echo json_encode($info);
            die;
        }
    }

    $ok = $DB->write("delete from products where id = :id", ['id' => $id]);
    $info->message = $ok ? 'Product deleted' : 'Delete failed';
    echo json_encode($info);
    die;
}

// Get products (default behavior when no data_type or data_type is get_products)
// optional category filter and limit
$category = isset($DATA_OBJ->category) ? intval($DATA_OBJ->category) : 0;
$shopFilter = isset($DATA_OBJ->shopId) ? intval($DATA_OBJ->shopId) : 0;
$pid = isset($DATA_OBJ->id) ? intval($DATA_OBJ->id) : 0;
$limit = isset($DATA_OBJ->limit) ? intval($DATA_OBJ->limit) : 20;
$random = isset($DATA_OBJ->random) ? intval($DATA_OBJ->random) : 0;

// Build base query and parameters
$params = [];
$where = [];
if ($category > 0) {
    $where[] = 'p.categoryId = :category';
    $params['category'] = $category;
}
if ($shopFilter > 0) {
    $where[] = 'p.shopId = :shopId';
    $params['shopId'] = $shopFilter;
}
if ($pid > 0) {
    $where[] = 'p.id = :pid';
    $params['pid'] = $pid;
}

$whereSql = '';
if (count($where) > 0) {
    $whereSql = ' WHERE ' . implode(' AND ', $where);
}

if ($random) {
    $query = "select p.*, c.name as category_name from products p left join category c on p.categoryId = c.id" . $whereSql . " order by rand() limit $limit";
    $result = $DB->read($query, $params);
} else {
    $query = "select p.*, c.name as category_name from products p left join category c on p.categoryId = c.id" . $whereSql . " order by p.id desc limit $limit";
    $result = $DB->read($query, $params);
}

// Construct image paths for products
if ($result) {
    foreach ($result as &$product) {
        if (isset($product->image) && !empty($product->image)) {
            $product->image = '/goGrocery/assets/images/products/' . $product->image;
        }
    }
}

// ... (existing code for get_products)

$info->data_type = 'get_products';
$info->products = $result ? $result : [];

echo json_encode($info);
