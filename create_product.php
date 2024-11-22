<?php
header("Content-Type: application/json; charset=UTF-8");
// Add these headers to every PHP file
header("Access-Control-Allow-Origin: *"); // Allow cross-origin requests
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow necessary HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow necessary headers

include_once 'Database.php';
include_once 'Product.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    error_log("Database connection failed.");
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection error."]);
    exit;
}

$product = new Product($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['productName'], $_POST['categoryName'], $_POST['price'])) {
        $product->productName = $_POST['productName'];
        $product->categoryName = $_POST['categoryName'];
        $product->price = $_POST['price'];

        // Check if an image is uploaded
        if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
            // Get the file content of the image
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            // Set the image as a BLOB field in the database
            $product->image = $imageData;
        } else {
            $product->image = null; // No image selected
        }

        // Create the product in the database
        if ($product->create()) {
            http_response_code(201);
            // Retrieve the last inserted product to ensure consistent output
            $createdProduct = [
                "productID" => $db->lastInsertId(), // Assuming PDO is used
                "productName" => $product->productName,
                "categoryName" => $product->categoryName,
                "price" => $product->price,
                // Convert image to base64 and return as a Data URL
                "image" => $product->image ? 'data:image/jpeg;base64,' . base64_encode($product->image) : null
            ];
            echo json_encode(["success" => true, "message" => "Product created successfully.", "data" => $createdProduct]);
        } else {
            error_log("Product creation failed.");
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Unable to create product."]);
        }
    } else {
        error_log("Invalid input: Missing fields.");
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid input. Missing fields."]);
    }
} else {
    error_log("Method not allowed. Only POST requests are accepted.");
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
}
