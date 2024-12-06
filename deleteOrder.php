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

    // First, delete associated order items to maintain referential integrity
    $deleteItemsQuery = "DELETE FROM order_items WHERE OrderID = :orderId";
    $stmtItems = $db->prepare($deleteItemsQuery);
    $stmtItems->bindParam(':orderId', $orderId);
    $stmtItems->execute();

    // Then, delete the main order record
    $deleteOrderQuery = "DELETE FROM orders WHERE OrderID = :orderId";
    $stmtOrder = $db->prepare($deleteOrderQuery);
    $stmtOrder->bindParam(':orderId', $orderId);
    $stmtOrder->execute();

    // Check if any rows were affected
    if ($stmtOrder->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Order deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Order not found or already deleted."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}