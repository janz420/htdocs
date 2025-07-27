<?php
header('Content-Type: application/json');
include_once 'config/Database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->orderItems)) {
    try {
        // First get the current maximum purchased_id
        $maxIdQuery = "SELECT MAX(purchased_id) as max_id FROM purchased";
        $maxIdStmt = $db->query($maxIdQuery);
        $maxIdResult = $maxIdStmt->fetch(PDO::FETCH_ASSOC);
        
        // Determine the new purchased_id (if no records exist, start with 1)
        $newPurchasedId = ($maxIdResult['max_id'] !== null) ? $maxIdResult['max_id'] + 1 : 1;

        // Prepare insert statement - now including purchased_id
        $query = "INSERT INTO purchased 
                  (purchased_id, item_id, item_price, item_size, item_qty, item_subtotal, notes) 
                  VALUES (:purchased_id, :item_id, :item_price, :item_size, :item_qty, :item_subtotal, :notes)";
        
        $stmt = $db->prepare($query);
        
        // Start transaction
        $db->beginTransaction();
        
        $total = 0;
        foreach ($data->orderItems as $item) {
            // Calculate subtotal if not provided
            $subtotal = $item->subtotal ?? ($item->price * $item->quantity);
            $total += $subtotal;
            
            // Bind parameters - including the same purchased_id for all items in this order
            $stmt->bindParam(':purchased_id', $newPurchasedId);
            $stmt->bindParam(':item_id', $item->id);
            $stmt->bindParam(':item_price', $item->price);
            $stmt->bindParam(':item_size', $item->size);
            $stmt->bindParam(':item_qty', $item->quantity);
            $stmt->bindParam(':item_subtotal', $subtotal);
            $stmt->bindParam(':notes', $item->notes);
            
            // Execute
            $stmt->execute();
        }
        
        // Commit transaction
        $db->commit();
        
        // Return success with order details for printing
        echo json_encode([
            'success' => true,
            'message' => 'Order submitted successfully!',
            'orderDetails' => $data->orderItems,
            'orderTotal' => $total,
            'purchasedId' => $newPurchasedId  // Return the purchased_id to the client
        ]);
        
    } catch (PDOException $e) {
        // Rollback on error
        $db->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error submitting order: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No order items received'
    ]);
}
?>