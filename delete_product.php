<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include_once 'Database.php';
include_once 'Product.php';

$response = ["success" => false, "message" => "Something went wrong."];

// Handle OPTIONS request for preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if ($db) {
    $product = new Product($db);

    // Get productID from query parameters (not JSON body)
    $productID = isset($_GET['productID']) ? $_GET['productID'] : null;

    // Validate productID
    if ($productID) {
        $product->productID = $productID;

        // Attempt to delete the product
        if ($product->delete()) {
            $response['success'] = true;
            $response['message'] = 'Product deleted successfully.';
        } else {
            // If deletion fails, it may not exist
            $response['message'] = 'Failed to delete the product. It may not exist.';
        }
    } else {
        // If productID is missing or invalid
        $response['message'] = 'Product ID is missing or invalid.';
    }
} else {
    // If database connection fails
    $response['message'] = 'Failed to connect to the database.';
}

echo json_encode($response);
?>
