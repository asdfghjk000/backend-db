<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include_once 'Database.php';

// Handle OPTIONS request for preflight CORS checks
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit();
}

try {
    // Decode the JSON body of the POST request
    $data = json_decode(file_get_contents("php://input"));

    // Check if `orderId` is provided in the request body
    if (!isset($data->orderId)) {
        echo json_encode(["success" => false, "message" => "Order ID is required."]);
        exit();
    }

    $orderId = $data->orderId;

    // Start a transaction to ensure data integrity
    $db->beginTransaction();

    // Retrieve the deleted order details
    $orderQuery = "SELECT * FROM deleted_orders WHERE OrderID = :orderId";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->bindParam(':orderId', $orderId);
    $orderStmt->execute();

    $deletedOrder = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$deletedOrder) {
        echo json_encode(["success" => false, "message" => "Order not found in deleted orders."]);
        $db->rollBack();
        exit();
    }

    // Insert the order back into the `orders` table
    $restoreOrderQuery = "INSERT INTO orders (OrderID, TotalAmount, PaymentMethod, OrderType, OrderDate) 
                          VALUES (:OrderID, :TotalAmount, :PaymentMethod, :OrderType, :OrderDate)";
    $restoreOrderStmt = $db->prepare($restoreOrderQuery);
    $restoreOrderStmt->execute([
        ':OrderID' => $deletedOrder['OrderID'],
        ':TotalAmount' => $deletedOrder['TotalAmount'],
        ':PaymentMethod' => $deletedOrder['PaymentMethod'],
        ':OrderType' => $deletedOrder['OrderType'],
        ':OrderDate' => $deletedOrder['OrderDate']
    ]);

    // Retrieve the deleted order items
    $itemsQuery = "SELECT * FROM deleted_order_items WHERE DeletedOrderID = :orderId";
    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->bindParam(':orderId', $orderId);
    $itemsStmt->execute();

    $deletedItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Insert the order items back into the `order_items` table
    $restoreItemsQuery = "INSERT INTO order_items (OrderID, ProductName, Quantity, Price) 
                          VALUES (:OrderID, :ProductName, :Quantity, :Price)";
    $restoreItemsStmt = $db->prepare($restoreItemsQuery);

    foreach ($deletedItems as $item) {
        $restoreItemsStmt->execute([
            ':OrderID' => $deletedOrder['OrderID'], // Use the restored OrderID here
            ':ProductName' => $item['ProductName'],
            ':Quantity' => $item['Quantity'],
            ':Price' => $item['Price']
        ]);
    }

    // Delete the restored data from the deleted tables
    $deleteOrderQuery = "DELETE FROM deleted_orders WHERE OrderID = :orderId";
    $deleteItemsQuery = "DELETE FROM deleted_order_items WHERE DeletedOrderID = :orderId";

    $deleteOrderStmt = $db->prepare($deleteOrderQuery);
    $deleteItemsStmt = $db->prepare($deleteItemsQuery);

    $deleteOrderStmt->bindParam(':orderId', $orderId);
    $deleteItemsStmt->bindParam(':orderId', $orderId);

    $deleteOrderStmt->execute();
    $deleteItemsStmt->execute();

    // Commit the transaction
    $db->commit();

    echo json_encode(["success" => true, "message" => "Order and items restored successfully."]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
