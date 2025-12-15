<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"));

$location = $conn->real_escape_string($data->location);
$reporter = $conn->real_escape_string($data->reporter);
$ph = (float)$data->ph;
$turbidity = (float)$data->turbidity;

// 1. Determine Safety Status
$status = 'Safe';
if ($ph < 6.5 || $ph > 8.5 || $turbidity > 5) {
    $status = 'Contaminated';
}

// 2. Insert Report
$sql = "INSERT INTO water_reports (reporter_email, location, ph_level, turbidity, status) 
        VALUES ('$reporter', '$location', $ph, $turbidity, '$status')";

if ($conn->query($sql) === TRUE) {
    
    // 3. LOGIC: Create Alert if Contaminated
    if ($status === 'Contaminated') {
        $msg = "Water Contamination Detected! pH: $ph, Turbidity: $turbidity";
        $conn->query("INSERT INTO alerts (alert_level, alert_type, message, location) VALUES ('High', 'Water', '$msg', '$location')");
    }

    echo json_encode(["success" => true, "message" => "Water Report Saved. Status: $status"]);
} else {
    echo json_encode(["success" => false, "message" => "Database Error"]);
}

$conn->close();
?>