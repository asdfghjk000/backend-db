<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'Database.php';
include_once 'Product.php';

$response = ["success" => false, "message" => "Something went wrong."];

$database = new Database();
$db = $database->getConnection();

if ($db) {
    $product = new Product($db);
    
    // Get productID from the query string
    $productID = isset($_GET['productID']) ? $_GET['productID'] : null;

    if ($productID) {
        $product->productID = $productID;

        if ($product->delete()) {
            $response['success'] = true;
            $response['message'] = 'Product deleted successfully';
        } else {
            $response['message'] = 'Failed to delete product';
        }
    } else {
        $response['message'] = 'Product ID is missing';
    }
} else {
    $response['message'] = 'Failed to connect to the database';
}

echo json_encode($response);
