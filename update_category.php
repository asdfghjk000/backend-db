<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include_once 'Database.php';
include_once 'Category.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$category = new Category($db);
$data = json_decode(file_get_contents("php://input"));

$response = array();

if (!empty($data->categoryID) && !empty($data->categoryName)) {
    $category->categoryID = $data->categoryID;
    $category->categoryName = $data->categoryName;

    if ($category->update()) {
        $response["success"] = true;
        $response["message"] = "Category updated successfully.";
        http_response_code(200);
    } else {
        $response["success"] = false;
        $response["message"] = "Unable to update category.";
        http_response_code(503);
    }
} else {
    $response["success"] = false;
    $response["message"] = "Category ID or name is missing.";
    http_response_code(400);
}

echo json_encode($response);
