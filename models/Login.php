<?php
class Login {
    private $conn;
    private $table = "users";

    // User properties
    public $username;
    public $password;
    public $logged_in_user = null;

    public function __construct($db) {
        $this->conn = $db;
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Login function with session management
    public function login() {
        // Prepare query
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(':username', $this->username);
        
        try {
            // Execute query
            $stmt->execute();
            
            // Check if user exists
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (assuming passwords are hashed)
                if(password_verify($this->password, $row['password'])) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['logged_in'] = true;
                    
                    // Store user data in class property (without password)
                    unset($row['password']);
                    $this->logged_in_user = $row;
                    
                    return true;
                }
            }
            
            // If we get here, login failed
            return false;
            
        } catch(PDOException $e) {
            // Handle database error
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Get current user data
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            if (!$this->logged_in_user) {
                // If we haven't loaded user data yet, fetch it
                $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                $stmt->execute();
                $this->logged_in_user = $stmt->fetch(PDO::FETCH_ASSOC);
                unset($this->logged_in_user['password']);
            }
            return $this->logged_in_user;
        }
        return null;
    }

    // Logout function
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Reset logged_in_user
        $this->logged_in_user = null;
        
        return true;
    }
}