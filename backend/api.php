<?php 

session_start();

try {
	$DATA_RAW = file_get_contents("php://input");
	$DATA_OBJ = json_decode($DATA_RAW);

	// If request is a multipart/form-data (file upload), php://input won't be JSON.
	// Support form posts by using $_POST when JSON decode fails.
	if(!$DATA_OBJ && isset($_POST['data_type'])){
		$DATA_OBJ = (object) $_POST;
	}

	$info = (object)[];

	//check if logged in
	if(!isset($_SESSION['userid']))
	{
		// Allow unauthenticated requests for login, signup, user_info, logout, and product/category browsing
		if(isset($DATA_OBJ->data_type) && !in_array($DATA_OBJ->data_type, ["login","signup","user_info","logout","get_products","get_categories","get_shops","validate_coupon"]))
		{
			$info->logged_in = false;
			echo json_encode($info);
			die;
		}
		
	}


	require_once("classes/autoload.php");
	$DB = new Database();


$Error = "";

//proccess the data
if(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "signup")
{
	//signup
	include("include/signup.php");

}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "login")
{
	//login
	include("include/login.php");

}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "logout")
{
	include("include/logout.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "user_info")
{

	//user info
	include("include/user_info.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "get_orders_view"){
	include("include/orders_view.php");
}elseif(isset($DATA_OBJ->data_type) && in_array($DATA_OBJ->data_type, ["get_categories", "add_category", "update_category", "delete_category"])){
	include("include/categories.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "get_products"){
	include("include/products.php");
}elseif(isset($DATA_OBJ->data_type) && in_array($DATA_OBJ->data_type, ["add_product", "update_product"])){
	include("include/products.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "cart_count"){
	include("include/cart.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "add_to_cart"){
	include("include/cart.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "get_cart"){
	include("include/cart.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "get_shops"){
	include("include/shops.php");
}elseif(isset($DATA_OBJ->data_type) && in_array($DATA_OBJ->data_type, ['update_cart','remove_from_cart','clear_cart'])){
	include("include/cart.php");
}elseif(isset($DATA_OBJ->data_type) && in_array($DATA_OBJ->data_type, ['add_shop','update_shop','delete_shop'])){
	include("include/shops.php");
}elseif(isset($DATA_OBJ->data_type) && in_array($DATA_OBJ->data_type, ['create_order','get_orders','update_order'])){
	include("include/orders.php");
}elseif(isset($DATA_OBJ->data_type) && in_array($DATA_OBJ->data_type, ['get_users','update_user','delete_user','create_user'])){
	include("include/admin.php");
}elseif(isset($DATA_OBJ->data_type) && in_array($DATA_OBJ->data_type, ['save_profile','change_password'])){
	include("include/profile.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "validate_coupon"){
	include("include/coupon.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "settings")
{
	//user info
	include("include/settings.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "save_settings")
{
	//user info
	include("include/save_settings.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "shop_settings")
{
	//shop settings
	include("include/shop_settings.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "save_shop_settings")
{
	//save shop settings
	include("include/save_shop_settings.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "update_user_profile")
{
	//update user profile
	include("include/update_user_profile.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "shop_dashboard")
{
	//shop dashboard
	include("include/shop_dashboard.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "shop_orders")
{
	//shop orders
	include("include/shop_orders.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "update_order_status")
{
	//update order status
	include("include/update_order_status.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "shop_products")
{
	//shop products
	include("include/shop_products.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "update_product")
{
	//update product
	include("include/update_product.php");
}elseif(isset($DATA_OBJ->data_type) && $DATA_OBJ->data_type == "delete_product")
{
	//delete product
	include("include/delete_product.php");
}else {
	// No matching handler found
	if (!isset($info->message)) {
		$info->message = "Unknown request type";
		if (isset($DATA_OBJ->data_type)) {
			$info->data_type = $DATA_OBJ->data_type;
		}
	}
	echo json_encode($info);
}

} catch (Exception $e) {
	$errorInfo = new stdClass();
	$errorInfo->error = true;
	$errorInfo->message = "Server error: " . $e->getMessage();
	$errorInfo->exception = get_class($e);
	echo json_encode($errorInfo);
}