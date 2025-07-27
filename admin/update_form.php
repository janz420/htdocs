<?php
include_once '../config/Database.php';
include_once '../models/Menu.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);
$menu->food_id = $_GET['id'] ?? die('ID not specified');

$menu->readOne();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Item</h1>
        
        <form action="update.php" method="POST" class="customer-form">
            <input type="hidden" name="id" value="<?= $menu->food_id ?>">
            
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($menu->food_name) ?>" required>
            </div>
            <div class="form-group">
                <label for="regular_price">Regular:</label>
                <input type="text" id="regular_price" name="price1" value="<?= htmlspecialchars($menu->food_regular_price) ?>" required>
            </div>
            <div class="form-group">
                <label for="solo_price">Solo:</label>
                <input type="text" id="solo_price" name="price2" value="<?= htmlspecialchars($menu->food_solo_price) ?>">
            </div>
            
            <button type="submit" class="btn">Update Menu</button>
            <a href="index.php" class="btn btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>