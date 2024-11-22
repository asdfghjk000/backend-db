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

if (!empty($data->categoryName)) {
    $category = new Category($db);
    $category->categoryName = $data->categoryName;

    if ($category->create()) {
        $response["success"] = true;
        $response["message"] = "Category created successfully.";
        http_response_code(201);
    } else {
        error_log("Failed to create category: " . json_encode($data));
        $response["success"] = false;
        $response["message"] = "Unable to create category.";
        http_response_code(503);
    }
} else {
    $response["success"] = false;
    $response["message"] = "Category name is missing.";
    http_response_code(400);
}

echo json_encode($response);
