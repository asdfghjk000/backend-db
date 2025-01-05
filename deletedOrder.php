<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
    // Query to retrieve deleted orders and their items
    $query = "SELECT 
              do.OrderID AS orderNumber, 
              do.TotalAmount AS amount, 
              do.PaymentMethod AS payment, 
              do.OrderType AS orderType, 
              do.OrderDate AS date, 
              GROUP_CONCAT(CONCAT(doi.ProductName, ' x', doi.Quantity) SEPARATOR '|') AS items
          FROM deleted_orders do
          LEFT JOIN deleted_order_items doi ON do.DeletedOrderID = doi.DeletedOrderID
          GROUP BY do.DeletedOrderID
          ORDER BY do.OrderDate DESC";


    $stmt = $db->prepare($query);
    $stmt->execute();

    $deletedOrders = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $deletedOrders[] = [
            "orderNumber" => $row['orderNumber'],
            "items" => isset($row['items']) ? explode('|', $row['items']) : [],
            "amount" => $row['amount'],
            "payment" => $row['payment'],
            "orderType" => $row['orderType'],
            "date" => $row['date']
        ];
    }

    // Handle empty results
    if (empty($deletedOrders)) {
        echo json_encode(["success" => true, "data" => [], "message" => "No deleted orders found."]);
        exit();
    }

    echo json_encode(["success" => true, "data" => $deletedOrders]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
