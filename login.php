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

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user data into the database
    $query = "INSERT INTO users (Username, Password) VALUES (:username, :password)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $hashedPassword);

    if ($stmt->execute()) {
        // Retrieve the assigned role for the newly registered user
        $roleQuery = "SELECT Role FROM users WHERE Username = :username LIMIT 1";
        $roleStmt = $db->prepare($roleQuery);
        $roleStmt->bindParam(":username", $username);
        $roleStmt->execute();
        $role = $roleStmt->fetch(PDO::FETCH_ASSOC);

        // Check if the role is fetched successfully
        if ($role && isset($role['Role'])) {
            echo json_encode([
                "success" => true,
                "message" => "User registered successfully.",
                "user" => ["Role" => $role['Role']]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "User registered but role retrieval failed. Please contact support."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error registering user."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Username and password are required."
    ]);
}
?>
