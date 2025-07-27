<?php
include_once 'config/Database.php';
include_once 'models/Menu.php';

$database = new Database();
$db = $database->getConnection();
$menu = new Menu($db);

$category = $_GET['category'] ?? '';

if(!empty($category) && $category !== 'all') {
    $stmt = $menu->readByCategoryName($category);
} else {
    $stmt = $menu->read();
}

if($stmt->rowCount() > 0): ?>
    <div class="menu-grid">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
            $itemCategory = $row['category_name'] ?? 'Food';
            $defaultImage = (strtolower($itemCategory) == 'drink' ? 
                'assets/images/default-drink.png' : 
                'assets/images/default-food.png');
        ?>
        <div class="menu-item-card" 
             data-id="<?php echo $row['food_id']; ?>"
             data-name="<?php echo htmlspecialchars($row['food_name']); ?>"
             data-regular-price="<?php echo htmlspecialchars($row['food_regular_price']); ?>"
             data-solo-price="<?php echo htmlspecialchars($row['food_solo_price'] ?? ''); ?>">
            <img src="<?php echo htmlspecialchars($row['food_image'] ?? $defaultImage); ?>" 
                 alt="<?php echo htmlspecialchars($row['food_name']); ?>" 
                 class="menu-item-image">
            <div class="menu-item-name"><?php echo htmlspecialchars($row['food_name']); ?></div>
            <div class="menu-item-prices">
                <span>Regular: ₱<?php echo htmlspecialchars($row['food_regular_price']); ?></span>
                <?php if (!empty($row['food_solo_price'])): ?>
                    <span>Solo: ₱<?php echo htmlspecialchars($row['food_solo_price']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p class="no-items">No items found in this category.</p>
<?php endif; ?>