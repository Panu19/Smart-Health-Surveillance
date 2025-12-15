<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

// Get JSON input
$input = json_decode(file_get_contents("php://input"));

if (!isset($input->email) || !isset($input->password)) {
    echo json_encode(["success" => false, "message" => "Email and Password required"]);
    exit();
}

$email = $conn->real_escape_string($input->email);
$password = $input->password;

// Check user
$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Remove password from response
    unset($user['password']);
    
    echo json_encode([
        "success" => true,
        "message" => "Login Successful",
        "user" => $user
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid Credentials"]);
}

$conn->close();
?>