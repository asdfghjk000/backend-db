<?php
// Include the database connection file
include_once 'database.php';

// Set headers for CORS and allowed methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Instantiate the Database class
$database = new Database();
$db = $database->getConnection();

try {
    // Handle GET request to fetch all users
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = "SELECT Username, Password, Role FROM users";
        $stmt = $db->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = [
                    "Username" => $row["Username"],
                    "Password" => $row["Password"],
                    "Role" => $row["Role"]
                ];
            }

            echo json_encode([
                "success" => true,
                "users" => $users
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No users found."
            ]);
        }
    }

    // Handle POST request to edit or delete a user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"));

        // Validate if 'action' is set
        if (!empty($data->action)) {
            if ($data->action === 'edit' && !empty($data->originalUsername) && !empty($data->updatedUser)) {
                // Edit user logic
                $query = "UPDATE users SET Username = :newUsername, Password = :password, Role = :role WHERE Username = :originalUsername";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':originalUsername', $data->originalUsername);
                $stmt->bindParam(':newUsername', $data->updatedUser->Username);
                $stmt->bindParam(':password', $data->updatedUser->Password);
                $stmt->bindParam(':role', $data->updatedUser->Role);

                if ($stmt->execute()) {
                    echo json_encode([
                        "success" => true,
                        "message" => "User updated successfully."
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Failed to update user."
                    ]);
                }
            } elseif ($data->action === 'delete' && !empty($data->Username)) {
                // Delete user logic
                $query = "DELETE FROM users WHERE Username = :username";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':username', $data->Username);

                if ($stmt->execute()) {
                    echo json_encode([
                        "success" => true,
                        "message" => "User deleted successfully."
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Failed to delete user."
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Invalid action or missing parameters."
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Action is required."
            ]);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "An error occurred.",
        "error" => $e->getMessage()
    ]);
}
?>
