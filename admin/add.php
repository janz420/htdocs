<?php
include_once '../config/Database.php';
include_once '../models/Menu.php';
include_once '../models/Login.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);
$stmt = $menu->read();
$stmt_categories = $menu->readCategories();

// Initialize Login object
$login = new Login($db);

// Check if user is logged in, if not redirect to login page
if (!$login->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Get current user data
$user = $login->getCurrentUser();
?>
<?php include 'header.php'?>
    <div class="container">
    <!-- Modify your menu list section -->
    <div class="menu-list">
            <!-- Add this above your menu list -->
            <div class="category-filter">
                <button class="btn btn-filter active" data-category="all">Show All</button>
                <?php 
                $stmt_categories->execute(); // Reset categories pointer
                while ($cat = $stmt_categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <button class="btn btn-filter" data-category="<?php echo htmlspecialchars($cat['category_name']); ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </button>
                <?php endwhile; ?>
            </div>
        <h2>Menu Items</h2>
        <div id="menu-items-container">
            <?php 
            // Default show all items
            $stmt_menu = $menu->read();
            if($stmt_menu->rowCount() > 0): ?>
                <table>
                    <thead>
                        <tr>                            
                            <th>Name</th>
                            <th>Regular</th>
                            <th>Solo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt_menu->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['food_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['food_regular_price']); ?></td>
                            <td><?php echo htmlspecialchars($row['food_solo_price']); ?></td>
                            <td class="actions">
                                <a href="update_form.php?id=<?php echo $row['food_id']; ?>" class="btn btn-edit">Edit</a>
                                <form action="delete.php" method="POST" class="inline-form">
                                    <input type="hidden" name="id" value="<?php echo $row['food_id']; ?>">
                                    <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No menu items found.</p>
            <?php endif; ?>
        </div>
    </div>

        <!-- Add Menu Form -->
        <div class="add-menu-form">
            <form action="create.php" method="POST">
                <h2>Add New Item</h2>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" required>
                        <option value="">--Select Category--</option>
                        <?php 
                        $stmt_categories->execute();
                        while ($row = $stmt_categories->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo htmlspecialchars($row['category_id']); ?>"><?php echo htmlspecialchars($row['category_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="price1">Price Regular:</label>
                    <input type="number" id="price1" name="price1" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="price2">Price Solo:</label>
                    <input type="number" id="price2" name="price2" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Item</button>
            </form>
        </div>
    </div>
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>