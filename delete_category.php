<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
$response = array();

if (isset($_GET['id'])) {
    $category->categoryID = $_GET['id'];

    if ($category->delete()) {
        $response["success"] = true;
        $response["message"] = "Category deleted successfully.";
        http_response_code(200);
    } else {
        $response["success"] = false;
        $response["message"] = "Unable to delete category.";
        http_response_code(503);
    }
} else {
    $response["success"] = false;
    $response["message"] = "Category ID is missing.";
    http_response_code(400);
}

echo json_encode($response);
