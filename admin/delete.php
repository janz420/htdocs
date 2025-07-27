<?php
include_once '../config/Database.php';
include_once '../models/Menu.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);

if($_POST){
    $menu->food_id = $_POST['id'];

    if($menu->delete()){
        header("Location: index.php?message=Customer+deleted");
    } else{
        header("Location: index.php?error=Unable+to+delete+customer");
    }
}
?>