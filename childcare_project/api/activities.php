<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization header missing']);
    exit;
}

list($bearer, $token) = explode(' ', $authHeader);
$decoded = validateJWT($token);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

$childId = $_GET['child_id'] ?? null;
if (!$childId) {
    http_response_code(400);
    echo json_encode(['error' => 'Child ID is required']);
    exit;
}

$stmt = $conn->prepare("SELECT activity, duration, date_recorded FROM ACTIVITIES WHERE child_id = ? ORDER BY date_recorded DESC");
$stmt->bind_param("i", $childId);
$stmt->execute();
$result = $stmt->get_result();
$activitiesData = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['activities' => $activitiesData]);
$conn->close();
?>
