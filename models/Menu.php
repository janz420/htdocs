<?php
class Menu {
    private $conn;
    private $table = "menu";

    public $category_id;
    public $food_id;
    public $food_name;
    public $food_regular_price;
    public $food_solo_price;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY category_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE food_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->food_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->food_name = $row['food_name'];
            $this->food_regular_price = $row['food_regular_price'];
            $this->food_solo_price = $row['food_solo_price'];
            // Add other fields you need
        }
    }

    public function readCategories() {
        $query = "SELECT category_id, category_name FROM category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByCategoryName($category_name) {
        $query = "SELECT c.category_id, c.category_name, m.food_id, m.food_name, m.food_regular_price, m.food_solo_price 
                FROM menu m
                JOIN category c ON m.category_id = c.category_id
                WHERE c.category_name = :category_name
                ORDER BY m.food_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_name", $category_name);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET category_id=:category_id, food_name=:food_name, food_regular_price=:food_regular_price, food_solo_price=:food_solo_price";
        
        $stmt = $this->conn->prepare($query);

        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->food_name = htmlspecialchars(strip_tags($this->food_name));
        $this->food_regular_price = htmlspecialchars(strip_tags($this->food_regular_price));
        $this->food_solo_price = htmlspecialchars(strip_tags($this->food_solo_price));

        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":food_name", $this->food_name);
        $stmt->bindParam(":food_regular_price", $this->food_regular_price);
        $stmt->bindParam(":food_solo_price", $this->food_solo_price);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET food_name=:food_name, food_regular_price=:food_regular_price, food_solo_price=:food_solo_price
                  WHERE food_id=:food_id";
        
        $stmt = $this->conn->prepare($query);

        $this->food_name = htmlspecialchars(strip_tags($this->food_name));
        $this->food_regular_price = htmlspecialchars(strip_tags($this->food_regular_price));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->food_id = htmlspecialchars(strip_tags($this->food_id));

        $stmt->bindParam(":food_name", $this->food_name);
        $stmt->bindParam(":food_regular_price", $this->food_regular_price);
        $stmt->bindParam(":food_solo_price", $this->food_solo_price);
        $stmt->bindParam(":food_id", $this->food_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE food_id=:id";
        $stmt = $this->conn->prepare($query);

        $this->food_id = htmlspecialchars(strip_tags($this->food_id));

        $stmt->bindParam(":id", $this->food_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>