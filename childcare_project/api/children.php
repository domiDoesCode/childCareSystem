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

$userId = $decoded['userId'];
$roleId = $decoded['role'];

if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

$roomId = (int)$_GET['room_id'];

try {
    if ($roleId == 3) { // Replace 3 with actual parent role ID
        $stmt = $conn->prepare("SELECT id, name, date_of_birth FROM CHILDREN WHERE room_id = ? AND parent_id = ?");
        $stmt->bind_param("ii", $roomId, $userId);
    } else {
        $stmt = $conn->prepare("SELECT id, name, date_of_birth FROM CHILDREN WHERE room_id = ?");
        $stmt->bind_param("i", $roomId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $children = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['children' => $children]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query error', 'details' => $e->getMessage()]);
}

$conn->close();
?>
