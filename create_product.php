<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['productName'], $_POST['categoryName'], $_POST['price'], $_POST['status'])) {
        $product->productName = $_POST['productName'];
        $product->categoryName = $_POST['categoryName'];
        $product->price = $_POST['price'];
        $product->status = $_POST['status'];

        // Validate and process uploaded image
        if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            // Validate file type (JPEG/PNG only)
            $allowedTypes = ['image/jpeg', 'image/png'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Invalid image type. Only JPEG and PNG are allowed."]);
                exit;
            }

            // Limit file size to 2MB
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Image size exceeds 2MB limit."]);
                exit;
            }

            $product->image = file_get_contents($_FILES['image']['tmp_name']);
        } else {
            $product->image = null; // No image uploaded
        }

        // Create the product
        if ($product->create()) {
            $lastId = $db->lastInsertId();
            if ($lastId) {
                http_response_code(201);
                $createdProduct = [
                    "productID" => $lastId,
                    "productName" => $product->productName,
                    "categoryName" => $product->categoryName,
                    "price" => $product->price,
                    "status" => $product->status,
                    "image" => $product->image ? 'data:image/jpeg;base64,' . base64_encode($product->image) : null
                ];
                echo json_encode(["success" => true, "message" => "Product created successfully.", "data" => $createdProduct]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Unable to retrieve the last inserted product ID."]);
            }
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
?>
