<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include_once 'Database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit();
}

// Total Sales
if (isset($_GET['total_sales'])) {
    $stmt = $db->prepare("SELECT SUM(TotalAmount) AS total_sales FROM orders WHERE PaymentStatus = 'Paid'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result);
}

// Average Order Value
if (isset($_GET['average_order_value'])) {
    $stmt = $db->prepare("SELECT AVG(TotalAmount) AS average_order_value FROM orders WHERE PaymentStatus = 'Paid'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result);
}

// Most Popular Products
if (isset($_GET['top_products'])) {
    $stmt = $db->prepare("SELECT oi.ProductName, SUM(oi.Quantity) AS total_quantity
                          FROM order_items oi
                          JOIN orders o ON oi.OrderID = o.OrderID
                          WHERE o.PaymentStatus = 'Paid'
                          GROUP BY oi.ProductName
                          ORDER BY total_quantity DESC");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
}
