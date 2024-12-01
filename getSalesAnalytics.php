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

// Query to get sales data, including category details
$stmt = $db->prepare("
    SELECT 
        p.ProductName, 
        SUM(oi.Quantity * oi.Price) AS totalSales,  -- Calculate total sales per product
        SUM(oi.Quantity) AS totalQuantity,          -- Calculate total quantity sold per product
        p.categoryName,                            -- Fetch categoryName from product table
        c.categoryMain                             -- Fetch categoryMain from category table
    FROM orders o
    JOIN order_items oi ON o.OrderID = oi.OrderID
    JOIN product p ON oi.ProductID = p.productID  -- Assuming ProductID exists in order_items
    LEFT JOIN category c ON p.categoryName = c.categoryName  -- Join to get categoryMain from category table
    GROUP BY p.ProductName, p.categoryName, c.categoryMain
");

if ($stmt->execute()) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false, "message" => "Error fetching sales analytics."]);
}
?>
