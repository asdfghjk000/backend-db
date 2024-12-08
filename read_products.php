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

    // Modify the query to include status from Product.php's read method
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
                    "status" => $row['status'],                  // Include the status field
                    "image" => $image_data                        // Encode image to base64 if available
                ];

                $products_arr[] = $product_item;
            }

            // Return success and data if products found
            $response['success'] = true;
            $response['data'] = $products_arr;
        } else {
            // Handle case when no products are found
            $response['message'] = "No products found.";
        }
    } else {
        // Handle case when the query execution fails
        $response['message'] = "Failed to execute query to fetch products.";
    }
} else {
    // Handle case when database connection fails
    $response['message'] = "Failed to connect to the database.";
}

// Output the response as JSON
echo json_encode($response);
?>
