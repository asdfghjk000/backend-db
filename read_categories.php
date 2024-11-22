<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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
$category = new Category($db);

$stmt = $category->read();
$categories_arr = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Include categoryMain in the response
    $categories_arr[] = [
        "categoryID" => $row['categoryID'],
        "categoryName" => $row['categoryName'],
        "categoryMain" => $row['categoryMain']  // Add categoryMain field
    ];
}

echo json_encode($categories_arr);
