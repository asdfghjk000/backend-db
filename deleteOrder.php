<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
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
    // Decode the JSON body of the DELETE request
    $data = json_decode(file_get_contents("php://input"));

    // Check if `orderId` is provided in the request body
    if (!isset($data->orderId)) {
        echo json_encode(["success" => false, "message" => "Order ID is required."]);
        exit();
    }

    $orderId = $data->orderId;

    // Begin transaction
    $db->beginTransaction();

    // Insert the deleted order into the `deleted_orders` table
    $insertDeletedOrderQuery = "INSERT INTO deleted_orders (OrderID, TotalAmount, OrderType, PaymentMethod, PaidAmount, ChangeAmount, OrderDate)
        SELECT OrderID, TotalAmount, OrderType, PaymentMethod, PaidAmount, ChangeAmount, OrderDate FROM orders WHERE OrderID = :orderId";
    $stmtInsertOrder = $db->prepare($insertDeletedOrderQuery);
    $stmtInsertOrder->bindParam(':orderId', $orderId);
    $stmtInsertOrder->execute();

    // Get the ID of the inserted deleted order
    $deletedOrderId = $db->lastInsertId();
if (!$deletedOrderId) {
    throw new Exception("Failed to retrieve DeletedOrderID after inserting into deleted_orders.");
}


    // Insert associated order items into the `deleted_order_items` table
    $insertDeletedItemsQuery = "INSERT INTO deleted_order_items (DeletedOrderID, ProductID, ProductName, Price, Quantity)
        SELECT :deletedOrderId, ProductID, ProductName, Price, Quantity FROM order_items WHERE OrderID = :orderId";
    $stmtInsertItems = $db->prepare($insertDeletedItemsQuery);
    $stmtInsertItems->bindParam(':deletedOrderId', $deletedOrderId);
    $stmtInsertItems->bindParam(':orderId', $orderId);
    $stmtInsertItems->execute();

    // Delete associated order items from the `order_items` table
    $deleteItemsQuery = "DELETE FROM order_items WHERE OrderID = :orderId";
    $stmtItems = $db->prepare($deleteItemsQuery);
    $stmtItems->bindParam(':orderId', $orderId);
    $stmtItems->execute();

    // Delete the main order record from the `orders` table
    $deleteOrderQuery = "DELETE FROM orders WHERE OrderID = :orderId";
    $stmtOrder = $db->prepare($deleteOrderQuery);
    $stmtOrder->bindParam(':orderId', $orderId);
    $stmtOrder->execute();

    // Commit the transaction
    $db->commit();

    // Check if the order was successfully deleted
    if ($stmtOrder->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Order moved to bin successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Order not found or already deleted."]);
    }
} catch (Exception $e) {
    // Rollback the transaction on error
    $db->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
