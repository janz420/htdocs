<?php
require_once '../models/Login.php';
require_once '../config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Login object
$login = new Login($db);

// Perform logout
$login->logout();

// Redirect to login page
header("Location: login.php");
exit;
?>