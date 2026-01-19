<?php 

session_start();

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
}