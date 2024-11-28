<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS request for preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once 'Database.php';
include_once 'Product.php';

$response = ["success" => false, "message" => "Something went wrong."];

$database = new Database();
$db = $database->getConnection();

if ($db) {
    $product = new Product($db);

    // Get data from POST
    $productID = isset($_POST['productID']) ? $_POST['productID'] : null;
    $productName = isset($_POST['productName']) ? $_POST['productName'] : null;
    $categoryName = isset($_POST['categoryName']) ? $_POST['categoryName'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null; // Adding status
    $image = null;

    // Check if an image was uploaded and process it
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    // Validate input parameters
    if ($productID && $productName && $categoryName && $price && $status) { // Ensure status is provided
        // Update product in the database
        if ($product->update($productID, $productName, $categoryName, $price, $status, $image)) {
            $response['success'] = true;
            $response['message'] = 'Product updated successfully.';
        } else {
            $response['message'] = 'Failed to update product. Please ensure the product exists.';
        }
    } else {
        $response['message'] = 'Invalid input parameters. Ensure all required fields are provided, including status.';
    }
} else {
    $response['message'] = 'Failed to connect to the database.';
}

// Output the response
echo json_encode($response);
?>
