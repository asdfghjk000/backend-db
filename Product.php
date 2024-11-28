<?php

error_reporting(E_ERROR | E_PARSE); // Suppress warnings and notices

class Product {
    private $conn;
    private $table_name = "product"; // Table name for the product

    // Define properties for the Product class
    public $productID;
    public $categoryName;
    public $categoryMain; // Added the categoryMain field
    public $productName;
    public $image;
    public $price;
    public $status; // Added status field for product status

    // Constructor to initialize database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (categoryName, productName, image, price, status) 
                  VALUES (:categoryName, :productName, :image, :price, :status)";
        
        $stmt = $this->conn->prepare($query);

        // Clean and bind data
        $stmt->bindParam(':categoryName', htmlspecialchars(strip_tags($this->categoryName)));
        $stmt->bindParam(':productName', htmlspecialchars(strip_tags($this->productName)));
        $stmt->bindParam(':image', $this->image, PDO::PARAM_LOB);
        $stmt->bindParam(':price', htmlspecialchars(strip_tags($this->price)));
        $stmt->bindParam(':status', htmlspecialchars(strip_tags($this->status))); // Bind status

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read all products
    public function read() {
        $query = "SELECT p.productID, p.productName, p.categoryName, p.price, p.image, p.status, c.categoryMain
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.categoryName = c.categoryName";  // Joining category table for categoryMain

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a product
    public function update($productID, $productName, $categoryName, $price, $status, $image = null) {
        $query = "UPDATE " . $this->table_name . " SET categoryName = :categoryName, productName = :productName, 
                  price = :price, status = :status"; // Update the status column
        
        // Add image to query only if provided
        if ($image !== null) {
            $query .= ", image = :image";
        }

        $query .= " WHERE productID = :productID"; // Condition to update based on productID

        $stmt = $this->conn->prepare($query);

        // Clean and bind data
        $stmt->bindParam(':categoryName', htmlspecialchars(strip_tags($categoryName)));
        $stmt->bindParam(':productName', htmlspecialchars(strip_tags($productName)));
        $stmt->bindParam(':price', htmlspecialchars(strip_tags($price)));
        $stmt->bindParam(':status', htmlspecialchars(strip_tags($status)));  // Bind status
        $stmt->bindParam(':productID', htmlspecialchars(strip_tags($productID))); // Bind productID

        // If image is provided, bind the image parameter
        if ($image !== null) {
            $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
        }

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete a product
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE productID = :productID";
        $stmt = $this->conn->prepare($query);

        // Clean and bind data
        $stmt->bindParam(':productID', htmlspecialchars(strip_tags($this->productID)));

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
