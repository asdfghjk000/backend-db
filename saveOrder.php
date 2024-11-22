<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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

$orderData = json_decode(file_get_contents("php://input"), true);

if (!isset($orderData['totalAmount'], $orderData['paymentMethod'], $orderData['paidAmount'], $orderData['change'], $orderData['items'])) {
    echo json_encode(["success" => false, "message" => "Missing required data."]);
    exit();
}

$totalAmount = $orderData['totalAmount'];
$paymentMethod = $orderData['paymentMethod'];
$paidAmount = $orderData['paidAmount'];
$change = $orderData['change'];
$items = $orderData['items'];

$db->beginTransaction();

try {
    // Insert into orders table
    $stmt = $db->prepare("INSERT INTO orders (TotalAmount, PaymentMethod, PaidAmount, ChangeAmount) 
        VALUES (:TotalAmount, :PaymentMethod, :PaidAmount, :ChangeAmount)");
    $stmt->bindValue(':TotalAmount', $totalAmount, PDO::PARAM_INT);
    $stmt->bindValue(':PaymentMethod', $paymentMethod, PDO::PARAM_STR);
    $stmt->bindValue(':PaidAmount', $paidAmount, PDO::PARAM_INT);
    $stmt->bindValue(':ChangeAmount', $change, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting order: " . implode(", ", $stmt->errorInfo()));
    }

    $orderID = $db->lastInsertId();

    // Prepare the query to insert items into order_items table
    $stmt = $db->prepare("INSERT INTO order_items (OrderID, ProductID, ProductName, Price, Quantity) 
                          VALUES (:OrderID, :ProductID, :ProductName, :Price, :Quantity)");

    foreach ($items as $item) {
        // Validate item data
        if (!isset($item['productName'], $item['price'], $item['quantity'])) {
            throw new Exception("Incomplete item data: " . json_encode($item));
        }

        // If productID is provided, use it; otherwise, set it to NULL
        $productID = isset($item['productID']) ? $item['productID'] : null;

        $stmt->bindValue(':OrderID', $orderID, PDO::PARAM_INT);
        $stmt->bindValue(':ProductID', $productID, $productID !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':ProductName', $item['productName'], PDO::PARAM_STR);
        $stmt->bindValue(':Price', $item['price'], PDO::PARAM_INT);
        $stmt->bindValue(':Quantity', $item['quantity'], PDO::PARAM_INT); // Ensure Quantity is bound properly

        if (!$stmt->execute()) {
            throw new Exception("Error inserting order item: " . implode(", ", $stmt->errorInfo()));
        }
    }

    // Commit the transaction
    $db->commit();
    echo json_encode(["success" => true, "message" => "Order saved successfully.", "orderID" => $orderID]);

} catch (Exception $e) {
    // Rollback in case of an error
    $db->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

// Log query for debugging purposes
error_log("Query: " . $stmt->queryString);
error_log("Parameters: " . json_encode([
    ':OrderID' => $orderID,
    ':ProductID' => $productID,
    ':ProductName' => $item['productName'],
    ':Price' => $item['price'],
    ':Quantity' => $item['quantity']
]));
?>
