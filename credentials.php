<?php
// Include the database connection file
include_once 'database.php';

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Instantiate the Database class
$database = new Database();
$db = $database->getConnection();

// Query to fetch all users
$query = "SELECT Username, Role FROM users";
$stmt = $db->prepare($query);
$stmt->execute();

// Check if there are any records
if ($stmt->rowCount() > 0) {
    // Fetch all users
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send a successful response with user data
    echo json_encode([
        "success" => true,
        "message" => "Users retrieved successfully.",
        "users" => $users
    ]);
} else {
    // If no users found, send an error response
    echo json_encode([
        "success" => false,
        "message" => "No users found."
    ]);
}
?>
