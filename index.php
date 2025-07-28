<?php
include_once 'config/Database.php';
include_once 'models/Menu.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);
$stmt = $menu->read();
$stmt_categories = $menu->readCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TUPAD BALAY Cashier</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <!-- Order Section -->
        <div class="order-container">
            <h2>Current Order</h2>
            <form action="order.php" method="POST"></form>
            <table class="order-table" id="order-table">
                <thead>
                    <tr>
                        <th hidden>ID</th>
                        <th>Item</th>
                        <th>Size</th>
                        <th>Price</th>
                        <th style="width:12%">Quantity</th>
                        <th>Subtotal</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="order-items">
                    <!-- Order items will be added here -->
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right;"></td>
                        <td>Total:</td>
                        <td id="order-total"></td>
                    </tr>
                </tfoot>
            </table>
            <br>
            <!-- Add this after the order table but before the submit button -->
            <div class="payment-section">
                <div class="payment-details">
                    <div>Total: <span id="display-total">₱0.00</span></div>
                    <div>Amount: <span id="display-amount">₱0.00</span></div>
                    <div>Change: <span id="display-change">₱0.00</span></div>
                </div>
                <div class="form-group">
                    <label for="payment-amount">Amount Received:</label>
                    <input type="number" style="width:200px;" id="payment-amount" min="0" step="0.01" class="form-control">
                </div>
            </div>
            <button class="btn btn-primary" id="submit-order">Submit Order</button>
        </div>

        <!-- Menu List Section -->
        <div class="menu-list">
            <div class="category-filter">
                <button class="btn btn-filter active" data-category="all">Show All</button>
                <?php 
                $stmt_categories->execute();
                while ($cat = $stmt_categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <button class="btn btn-filter" data-category="<?php echo htmlspecialchars($cat['category_name']); ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </button>
                <?php endwhile; ?>
            </div>
            <h2>Menu Items</h2>
            <div id="menu-items-container">
                <?php 
                $stmt_menu = $menu->read();
                if($stmt_menu->rowCount() > 0): ?>
                    <div class="menu-grid">
                        <?php while ($row = $stmt_menu->fetch(PDO::FETCH_ASSOC)): 
                            $category = $row['category_id'] ?? 1;
                            $defaultImage = $category == 2 ? 
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
                                <img src="<?php echo htmlspecialchars($row['food_image'] ?? 'assets/images/default-food.png');?>" 
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
                    <p>No menu items found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Selection Panel -->
    <div class="selection-panel" id="selection-panel">
        <h3 id="selected-item-name"></h3>
        <div class="price-options">
            <div class="price-option" data-size="Regular" id="regular-option">Regular</div>
            <div class="price-option" data-size="Solo" id="solo-option">Solo</div>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" min="1" value="1" class="form-control">
        </div>
        <div class="form-group">
            <label for="notes">Notes:</label>
            <textarea id="notes" style="height: 80px; width: 218px;" class="form-control" placeholder="Special instructions..."></textarea>
        </div>
        <div class="action-buttons">
            <button class="btn btn-secondary" id="cancel-selection">Cancel</button>
            <button class="btn btn-primary" id="add-to-order">Add to Order</button>
        </div>
    </div>

<script src="assets/js/script.js"></script>
<script src="assets/js/order.js"></script>
</body>
</html>