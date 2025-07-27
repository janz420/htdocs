<?php
class Sales {
    private $conn;
    private $table = "purchased";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSalesData($filter = []) {
        $sql = "SELECT p.*, m.food_name, m.category_id, c.category_name 
                FROM " . $this->table . " p 
                JOIN menu m ON p.item_id = m.food_id 
                JOIN category c ON m.category_id = c.category_id";
        
        $where = [];
        $params = [];
        
        if (!empty($filter['item_id'])) {
            $where[] = "p.item_id = ?";
            $params[] = $filter['item_id'];
        }
        if (!empty($filter['size'])) {
            $where[] = "p.item_size = ?";
            $params[] = $filter['size'];
        }
        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $where[] = "p.purchase_date BETWEEN ? AND ?";
            $params[] = $filter['start_date'];
            $params[] = $filter['end_date'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        return $stmt;
    }

    public function getWeeklyIncome() {
        $sql = "SELECT 
                    DATE_FORMAT(purchase_date, '%Y-%m-%d') as day,
                    SUM(item_subtotal) as daily_total
                FROM " . $this->table . "
                WHERE purchase_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY day
                ORDER BY day";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function getMonthlyIncome() {
        $sql = "SELECT 
                    DATE_FORMAT(purchase_date, '%Y-%m') as month,
                    SUM(item_subtotal) as monthly_total
                FROM " . $this->table . "
                WHERE purchase_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function getItems() {
        $sql = "SELECT * FROM menu";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}
?>