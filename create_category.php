<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'Database.php';
include_once 'Category.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

$response = array();

// Check if the categoryName and categoryMain are set and valid
if (!empty($data->categoryName) && !empty($data->categoryMain)) {
    // Ensure categoryMain is either 'Food' or 'Drink'
    if ($data->categoryMain !== 'Food' && $data->categoryMain !== 'Drink') {
        $response["success"] = false;
        $response["message"] = "Invalid categoryMain value. It must be 'Food' or 'Drink'.";
        http_response_code(400); // Bad request
    } else {
        $category = new Category($db);
        $category->categoryName = $data->categoryName;
        $category->categoryMain = $data->categoryMain;

        if ($category->create()) {
            $response["success"] = true;
            $response["message"] = "Category created successfully.";
            http_response_code(201); // Created
        } else {
            error_log("Failed to create category: " . json_encode($data));
            $response["success"] = false;
            $response["message"] = "Unable to create category.";
            http_response_code(503); // Service unavailable
        }
    }
} else {
    $response["success"] = false;
    $response["message"] = "Category name or category main is missing.";
    http_response_code(400); // Bad request
}

echo json_encode($response);
?>
