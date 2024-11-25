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

    // Modify the query to include categoryMain from Product.php's read method
    $stmt = $product->read();

    if ($stmt) {
        $num = $stmt->rowCount();
        if ($num > 0) {
            $products_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Check if image data exists and encode it in base64 if it's not null
                $image_data = isset($row['image']) && !empty($row['image']) ? base64_encode($row['image']) : null;

                $product_item = [
                    "productID" => $row['productID'],           // Use proper column names
                    "productName" => $row['productName'],
                    "categoryName" => $row['categoryName'],
                    "categoryMain" => $row['categoryMain'],     // Include categoryMain from the category table
                    "price" => $row['price'],
                    "image" => $image_data                        // Encode image to base64 if available
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

echo json_encode($response);
?>
