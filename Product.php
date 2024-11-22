<?php

error_reporting(E_ERROR | E_PARSE); // Suppress warnings and notices

class Product {
    private $conn;
    private $table_name = "product";

    public $productID;
    public $categoryName;
    public $categoryMain; // Added the categoryMain field
    public $productName;
    public $image;
    public $price;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (categoryName, productName, image, price) VALUES (:categoryName, :productName, :image, :price)";

        $stmt = $this->conn->prepare($query);

        // Clean and bind data
        $stmt->bindParam(':categoryName', htmlspecialchars(strip_tags($this->categoryName)));
        $stmt->bindParam(':productName', htmlspecialchars(strip_tags($this->productName)));
        $stmt->bindParam(':image', $this->image, PDO::PARAM_LOB);
        $stmt->bindParam(':price', htmlspecialchars(strip_tags($this->price)));

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read all products
    public function read() {
        // Modify the query to include categoryMain by joining the product table with the category table
        $query = "SELECT p.productID, p.productName, p.categoryName, p.price, p.image, c.categoryMain
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.categoryName = c.categoryName";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a product
    public function update($productID, $productName, $categoryName, $price, $image = null) {
        $query = "UPDATE " . $this->table_name . " SET categoryName = :categoryName, productName = :productName, price = :price";

        // Only add image to update query if it's provided
        if ($image !== null) {
            $query .= ", image = :image";
        }

        $query .= " WHERE productID = :productID";

        $stmt = $this->conn->prepare($query);

        // Clean and bind data
        $stmt->bindParam(':categoryName', htmlspecialchars(strip_tags($categoryName)));
        $stmt->bindParam(':productName', htmlspecialchars(strip_tags($productName)));
        $stmt->bindParam(':price', htmlspecialchars(strip_tags($price)));
        $stmt->bindParam(':productID', htmlspecialchars(strip_tags($productID)));

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
