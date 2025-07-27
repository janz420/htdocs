<?php
include_once '../config/Database.php';
include_once '../models/Menu.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);

if($_POST){
    $menu->food_id = $_POST['id'];
    $menu->food_name = $_POST['name'];
    $menu->food_regular_price = $_POST['price1'];
    $menu->food_solo_price = $_POST['price2'] ?? null;

    if($menu->update()){
        header("Location: index.php?message=Customer+updated");
    } else{
        header("Location: index.php?error=Unable+to+update+customer");
    }
}
?>