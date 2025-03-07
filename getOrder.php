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
    // Updated query to include OrderType and items of both restored and normal orders
    $query = "SELECT 
                  o.OrderID AS orderNumber, 
                  o.TotalAmount AS amount, 
                  o.PaymentMethod AS payment, 
                  o.OrderType AS orderType, 
                  o.OrderDate AS date, 
                  GROUP_CONCAT(CONCAT(oi.ProductName, ' x', oi.Quantity) SEPARATOR '|') AS items
              FROM orders o
              LEFT JOIN order_items oi ON o.OrderID = oi.OrderID
              GROUP BY o.OrderID
              ORDER BY o.OrderDate DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $orders = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders[] = [
            "orderNumber" => $row['orderNumber'],
            "items" => isset($row['items']) ? explode('|', $row['items']) : [], // Check if items are returned
            "amount" => $row['amount'],
            "payment" => $row['payment'],
            "orderType" => $row['orderType'], // Include OrderType in response
            "date" => $row['date']
        ];
    }

    // Handle empty results
    if (empty($orders)) {
        echo json_encode(["success" => true, "data" => [], "message" => "No orders found."]);
        exit();
    }

    echo json_encode(["success" => true, "data" => $orders]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
