<?php
// Include the database connection file and Auth class
include_once 'database.php';
include_once 'auth.php';

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Constants for predefined users
define('PREDEFINED_USERS', [
    'eshercafeadmin' => ['role' => 'admin', 'password' => 'adminpos24'],
    'eshercafestaff' => ['role' => 'staff', 'password' => 'kioskstaff2024'],
    'dreamteam' => ['role' => 'superadmin', 'password' => 'dreamwin']
]);

// Instantiate the Database and Auth classes
$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->Username) && !empty($data->Password)) {
    $username = htmlspecialchars(strip_tags($data->Username));
    $password = htmlspecialchars(strip_tags($data->Password));

    try {
        // Check if the user exists in the database
        $checkQuery = "SELECT Username, Password, Role FROM users WHERE Username = :username LIMIT 1";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(":username", $username);
        $checkStmt->execute();

        // Fetch the user details
        $user = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // User exists, verify the password
            if (password_verify($password, $user['Password'])) {
                $encryptedData = $auth->encrypt(['Role' => $user['Role']]);
                echo json_encode([
                    "success" => true,
                    "message" => "Login successful.",
                    "data" => $encryptedData
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Username or password is incorrect."]);
            }
        } else {
            // User not found, check predefined users
            if (array_key_exists($username, PREDEFINED_USERS)) {
                $predefinedUser = PREDEFINED_USERS[$username];

                // Insert predefined user into the database
                $hashedPassword = password_hash($predefinedUser['password'], PASSWORD_BCRYPT);
                $insertQuery = "INSERT INTO users (Username, Password, Role) VALUES (:username, :password, :role)";
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->bindParam(":username", $username);
                $insertStmt->bindParam(":password", $hashedPassword);
                $insertStmt->bindParam(":role", $predefinedUser['role']);

                if ($insertStmt->execute()) {
                    $encryptedData = $auth->encrypt(['Role' => $predefinedUser['role']]);
                    echo json_encode([
                        "success" => true,
                        "message" => "User created and logged in successfully.",
                        "data" => $encryptedData
                    ]);
                } else {
                    throw new Exception("Error inserting predefined user.");
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Username or password is incorrect."]);
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage()); // Log errors for debugging
        echo json_encode(["status" => "error", "message" => "Internal server error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input."]);
}
?>