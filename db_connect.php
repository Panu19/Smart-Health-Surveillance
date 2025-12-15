<?php
// db_connect.php
$host = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password is empty
$dbname = "ecohealth";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "message" => "Database Connection Failed"]));
}

// Set charset to support special characters
$conn->set_charset("utf8mb4");
?>