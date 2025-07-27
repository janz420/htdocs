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
            $itemCategory = $row['category_id'] ?? 1;
            $defaultImage = $itemCategory == 2 ? 
                'assets/images/default-drink.png' : 
                'assets/images/default-food.png';
            $cat = $row['category_id'] ?? null;
        ?>
        <div class="menu-item-card" 
             data-id="<?php echo $row['food_id']; ?>"
             data-name="<?php echo htmlspecialchars($row['food_name']); ?>"
             data-regular-price="<?php echo htmlspecialchars($row['food_regular_price']); ?>"
             data-solo-price="<?php echo htmlspecialchars($row['food_solo_price'] ?? ''); ?>"
             data-category-id="<?php echo $row['category_id']; ?>">
            <img src="<?php echo htmlspecialchars($row['food_image'] ?? $defaultImage); ?>" 
                 alt="<?php echo htmlspecialchars($row['food_name']); ?>" 
                 class="menu-item-image">
            <div class="menu-item-name"><?php echo htmlspecialchars($row['food_name']); ?></div>
            <div class="menu-item-prices">
                <span><?php 
                switch($cat){
                    case 1:
                        echo "Regular ₱".htmlspecialchars($row['food_regular_price']);
                        break;
                    case 2:
                        echo "Large ₱".htmlspecialchars($row['food_regular_price']);
                        break;
                    case 3:
                        echo "Price ₱".htmlspecialchars($row['food_regular_price']) ?? null;
                        break;
                }
                ?></span>
                <?php if (!empty($row['food_solo_price'])): ?><br>
                    <span><?php 
                        switch($cat){
                            case 1:
                                echo "Solo ₱".htmlspecialchars($row['food_solo_price']);
                                break;
                            case 2:
                                echo "Small ₱".htmlspecialchars($row['food_solo_price']);
                                break;
                            case 3:
                                echo null;
                                break;
                        }
                        ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p class="no-items">No items found in this category.</p>
<?php endif; ?>