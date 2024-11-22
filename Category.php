<?php
class Category {
    private $conn;
    private $table_name = "category";

    public $categoryID;
    public $categoryName;
    public $categoryMain; // New field for category main (Food or Drink)

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new category
    public function create() {
        try {
            // Ensure that categoryMain is either 'Food' or 'Drink'
            if (empty($this->categoryName) || empty($this->categoryMain)) {
                throw new Exception("Category name and category main cannot be empty");
            }
            if ($this->categoryMain !== 'Food' && $this->categoryMain !== 'Drink') {
                throw new Exception("Invalid categoryMain value. It must be 'Food' or 'Drink'");
            }

            $query = "INSERT INTO " . $this->table_name . " (categoryName, categoryMain) 
                      VALUES (:categoryName, :categoryMain)";

            $stmt = $this->conn->prepare($query);

            // Clean inputs
            $this->categoryName = htmlspecialchars(strip_tags($this->categoryName));
            $this->categoryMain = htmlspecialchars(strip_tags($this->categoryMain));

            // Bind values
            $stmt->bindParam(':categoryName', $this->categoryName);
            $stmt->bindParam(':categoryMain', $this->categoryMain);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create category error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Create category error: " . $e->getMessage());
            return false;
        }
    }

    // Read categories
    public function read() {
        try {
            $query = "SELECT categoryID, categoryName, categoryMain FROM " . $this->table_name . " ORDER BY categoryName";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Read categories error: " . $e->getMessage());
            return false;
        }
    }

    // Update a category
    public function update() {
        try {
            if (empty($this->categoryID) || empty($this->categoryName) || empty($this->categoryMain)) {
                throw new Exception("Category ID, name, and category main are required");
            }

            $query = "UPDATE " . $this->table_name . " 
                     SET categoryName = :categoryName, categoryMain = :categoryMain 
                     WHERE categoryID = :categoryID";

            $stmt = $this->conn->prepare($query);

            // Clean inputs
            $this->categoryName = htmlspecialchars(strip_tags($this->categoryName));
            $this->categoryMain = htmlspecialchars(strip_tags($this->categoryMain));
            $this->categoryID = htmlspecialchars(strip_tags($this->categoryID));

            // Bind values
            $stmt->bindParam(':categoryName', $this->categoryName);
            $stmt->bindParam(':categoryMain', $this->categoryMain);
            $stmt->bindParam(':categoryID', $this->categoryID);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update category error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Update category error: " . $e->getMessage());
            return false;
        }
    }

    // Delete a category
    public function delete() {
        try {
            if (empty($this->categoryID)) {
                throw new Exception("Category ID is required");
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE categoryID = :categoryID";
            $stmt = $this->conn->prepare($query);

            // Clean categoryID input
            $this->categoryID = htmlspecialchars(strip_tags($this->categoryID));

            // Bind value
            $stmt->bindParam(':categoryID', $this->categoryID);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete category error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Delete category error: " . $e->getMessage());
            return false;
        }
    }
}
?>
