<?php
// Add these headers to every PHP file
header("Access-Control-Allow-Origin: *"); // Allow cross-origin requests
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allow necessary HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow necessary headers

class Database {
    private $host = "localhost";
    private $db_name = "esherdraftdb";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Database connection failed."]);
            exit();
        }

        return $this->conn;
    }
}
