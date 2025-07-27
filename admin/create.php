<?php
include_once '../config/Database.php';
include_once '../models/Menu.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);

if($_POST){
    $menu->category_id = $_POST['category'];
    $menu->food_name = $_POST['name'];
    $menu->food_regular_price = $_POST['price1'];
    $menu->food_solo_price = $_POST['price2'] ?? null;

    if($menu->create()){
        header("Location: index.php?message=Order+created");
    } else{
        header("Location: index.php?error=Unable+to+create+order");
    }
}
?>