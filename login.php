<?php
// Include the database connection file
include_once 'database.php';

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Instantiate the Database class
$database = new Database();
$db = $database->getConnection();

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->Username) && !empty($data->Password)) {
    $username = trim(htmlspecialchars(strip_tags($data->Username)));
    $password = trim(htmlspecialchars(strip_tags($data->Password)));

    // Check if the username exists in the database
    $query = "SELECT Password, Role FROM users WHERE Username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compare plain text password (if passwords are stored as plain text)
        if ($password === $user['Password']) { // Use plain text comparison here
            echo json_encode([
                "success" => true,
                "message" => "Login successful.",
                "user" => [
                    "Username" => $username,
                    "Role" => $user['Role']
                ]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Invalid credentials. Password mismatch."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid credentials. User not found."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Username and password are required."
    ]);
}
?>
