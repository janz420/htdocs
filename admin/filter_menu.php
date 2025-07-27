<?php
include_once '../config/Database.php';
include_once '../models/Menu.php';

$database = new Database();
$db = $database->getConnection();
$menu = new Menu($db);

$category = $_GET['category'] ?? '';

if(!empty($category)) {
    $stmt = $menu->readByCategoryName($category);
    
    if($stmt->rowCount() > 0): ?>
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
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
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
        <p>No items found in this category.</p>
    <?php endif;
}
?>