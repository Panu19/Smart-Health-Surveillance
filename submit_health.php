<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->location) || !isset($data->symptoms)) {
    echo json_encode(["success" => false, "message" => "Missing required data"]);
    exit();
}

$age = (int)$data->age;
$gender = $conn->real_escape_string($data->gender);
$location = $conn->real_escape_string($data->location);
$reporter = $conn->real_escape_string($data->reporter);
// Frontend sends symptoms as array, convert to comma string for SQL
$symptoms = is_array($data->symptoms) ? implode(', ', $data->symptoms) : $data->symptoms;
$symptoms_esc = $conn->real_escape_string($symptoms);

// 1. Insert Report
$sql = "INSERT INTO health_reports (reporter_email, patient_age, gender, location, symptoms) 
        VALUES ('$reporter', $age, '$gender', '$location', '$symptoms_esc')";

if ($conn->query($sql) === TRUE) {
    
    // 2. LOGIC: Check for Outbreak (More than 2 cases in same location in last 24h)
    $check_sql = "SELECT COUNT(*) as count FROM health_reports 
                  WHERE location = '$location' 
                  AND created_at >= NOW() - INTERVAL 1 DAY";
    
    $result = $conn->query($check_sql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] >= 3) {
        // Check if active alert already exists
        $alert_check = "SELECT id FROM alerts WHERE location='$location' AND alert_type='Outbreak' AND is_active=1";
        if ($conn->query($alert_check)->num_rows == 0) {
            $msg = "Outbreak Warning: " . $row['count'] . " recent cases in " . $location;
            $conn->query("INSERT INTO alerts (alert_level, alert_type, message, location) VALUES ('Critical', 'Outbreak', '$msg', '$location')");
        }
    }

    echo json_encode(["success" => true, "message" => "Health Report Submitted"]);
} else {
    echo json_encode(["success" => false, "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>