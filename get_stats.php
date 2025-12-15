<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db_connect.php';

$response = [];

// 1. Counts
$response['cases'] = $conn->query("SELECT COUNT(*) as c FROM health_reports")->fetch_assoc()['c'];
$response['water'] = $conn->query("SELECT COUNT(*) as c FROM water_reports")->fetch_assoc()['c'];
$response['alerts'] = $conn->query("SELECT COUNT(*) as c FROM alerts WHERE is_active=1")->fetch_assoc()['c'];

// 2. Fetch Alerts List
$alerts = [];
$res = $conn->query("SELECT * FROM alerts WHERE is_active=1 ORDER BY created_at DESC");
while($row = $res->fetch_assoc()) {
    $alerts[] = $row;
}
$response['alertsData'] = $alerts;

// 3. Fetch Health Data (For Heatmap/Logs)
$health = [];
$res = $conn->query("SELECT location, symptoms, created_at FROM health_reports ORDER BY created_at DESC LIMIT 10");
while($row = $res->fetch_assoc()) {
    // Convert string back to array for frontend compatibility
    $row['symptoms'] = explode(', ', $row['symptoms']);
    $health[] = $row;
}
$response['healthData'] = $health;

// 4. Fetch Water Data (For Logs)
$water = [];
$res = $conn->query("SELECT location, ph_level as ph, created_at FROM water_reports ORDER BY created_at DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    $water[] = $row;
}
$response['waterData'] = $water;

echo json_encode($response);
$conn->close();
?>