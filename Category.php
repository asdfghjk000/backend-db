<?php
class Category {
    private $conn;
    private $table_name = "category";

    public $categoryID;
    public $categoryName;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " (categoryName) VALUES (:categoryName)";
            $stmt = $this->conn->prepare($query);

            if (empty($this->categoryName)) {
                throw new Exception("Category name cannot be empty");
            }
            $this->categoryName = htmlspecialchars(strip_tags($this->categoryName));

            $stmt->bindParam(':categoryName', $this->categoryName);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create category error: " . $e->getMessage());
            return false;
        }
    }

    public function read() {
        try {
            $query = "SELECT categoryID, categoryName FROM " . $this->table_name . " ORDER BY categoryName";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Read categories error: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        try {
            if (empty($this->categoryID) || empty($this->categoryName)) {
                throw new Exception("Category ID and name are required");
            }

            $query = "UPDATE " . $this->table_name . " 
                     SET categoryName = :categoryName 
                     WHERE categoryID = :categoryID";

            $stmt = $this->conn->prepare($query);

            $this->categoryName = htmlspecialchars(strip_tags($this->categoryName));
            $this->categoryID = htmlspecialchars(strip_tags($this->categoryID));

            $stmt->bindParam(':categoryName', $this->categoryName);
            $stmt->bindParam(':categoryID', $this->categoryID);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update category error: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        try {
            if (empty($this->categoryID)) {
                throw new Exception("Category ID is required");
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE categoryID = :categoryID";
            $stmt = $this->conn->prepare($query);

            $this->categoryID = htmlspecialchars(strip_tags($this->categoryID));

            $stmt->bindParam(':categoryID', $this->categoryID);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete category error: " . $e->getMessage());
            return false;
        }
    }
}
