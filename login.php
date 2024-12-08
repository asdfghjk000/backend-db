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
    $username = htmlspecialchars(strip_tags($data->Username));
    $password = htmlspecialchars(strip_tags($data->Password));

    // Check if the user already exists
    $checkQuery = "SELECT Username, Password, Role FROM users WHERE Username = :username LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":username", $username);
    $checkStmt->execute();

    // If user doesn't exist, insert the admin or staff user
    if ($checkStmt->rowCount() == 0) {
        // Determine which user to create based on the username
        if ($username === 'eshercafeadmin' || $username === 'eshercafestaff') {
            $role = ($username === 'eshercafeadmin') ? 'admin' : 'staff';
            
            // Set specific passwords for predefined users
            $predefinedPassword = ($username === 'eshercafeadmin') ? 'adminpos24' : 'kioskstaff2024';
            $hashedPassword = password_hash($predefinedPassword, PASSWORD_BCRYPT); // Use password_hash

            // Insert the user into the database
            $insertQuery = "INSERT INTO users (Username, Password, Role) VALUES (:username, :password, :role)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindParam(":username", $username);
            $insertStmt->bindParam(":password", $hashedPassword);
            $insertStmt->bindParam(":role", $role);

            if ($insertStmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "User created successfully.",
                    "user" => ["Role" => $role]
                ]);
                return; // End the script here if the user is created successfully
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error creating user."
                ]);
                return;
            }
        }
    }

    // If the user exists, validate the password
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['Password'])) { // Use password_verify for validation
        echo json_encode([
            "success" => true,
            "message" => "Login successful.",
            "user" => ["Role" => $user['Role']]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Username or password is incorrect."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Username and password are required."
    ]);
}
?>
