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
    // Query to get order history with total quantity instead of price
    $query = "SELECT 
                  o.OrderID AS orderNumber, 
                  o.TotalAmount AS amount, 
                  o.ChangeAmount AS changeAmount, 
                  o.PaymentMethod AS payment, 
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
            "items" => isset($row['items']) ? explode('|', $row['items']) : [],
            "amount" => $row['amount'],
            "changeAmount" => $row['changeAmount'],
            "payment" => $row['payment'],
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
