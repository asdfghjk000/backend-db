<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'Database.php';
include_once 'Product.php';

$response = ["success" => false, "data" => []];

$database = new Database();
$db = $database->getConnection();

if ($db) {
    $product = new Product($db);
    $stmt = $product->read();

    if ($stmt) {
        $num = $stmt->rowCount();
        if ($num > 0) {
            $products_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Debugging: Display the raw data from the database
                // Uncomment the line below to see the raw output
                // var_dump($row);

                // Ensure the 'Image' field is properly handled if it's null or empty
                $image_data = isset($row['Image']) && !empty($row['Image']) ? base64_encode($row['Image']) : null;

                $product_item = [
                    "productID" => $row['ProductID'],
                    "productName" => $row['ProductName'],
                    "categoryName" => $row['CategoryName'],
                    "price" => $row['Price'],
                    "image" => $image_data
                ];

                $products_arr[] = $product_item;
            }

            $response['success'] = true;
            $response['data'] = $products_arr;
        } else {
            $response['message'] = "No products found.";
        }
    } else {
        $response['message'] = "Failed to execute query to fetch products.";
    }
} else {
    $response['message'] = "Failed to connect to the database.";
}

echo json_encode($response); // Ensure this is the only output