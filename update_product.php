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
    
    $productID = isset($_POST['productID']) ? $_POST['productID'] : null;
    $productName = isset($_POST['productName']) ? $_POST['productName'] : null;
    $categoryName = isset($_POST['categoryName']) ? $_POST['categoryName'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    if ($productID && $productName && $categoryName && $price) {
        if ($product->update($productID, $productName, $categoryName, $price, $image)) {
            $response['success'] = true;
            $response['message'] = 'Product updated successfully';
        } else {
            $response['message'] = 'Failed to update product';
        }
    } else {
        $response['message'] = 'Invalid input parameters';
    }
} else {
    $response['message'] = 'Failed to connect to the database';
}

echo json_encode($response);
