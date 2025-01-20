<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Retrieve and validate the JWT from the Authorization header
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization header missing']);
    exit;
}

// Extract the token and validate it
list($bearer, $token) = explode(' ', $authHeader);
$decoded = validateJWT($token);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

// Retrieve rooms from the decoded JWT payload
$rooms = $decoded['rooms']; // JWT payload includes 'rooms' key with accessible room IDs

if (is_array($rooms) && count($rooms) > 0) {
    // Prepare SQL to select only rooms user has access to
    $placeholders = implode(',', array_fill(0, count($rooms), '?'));
    $stmt = $conn->prepare("SELECT id, name FROM ROOMS WHERE id IN ($placeholders)");

    // Dynamically bind room IDs
    $stmt->bind_param(str_repeat('i', count($rooms)), ...$rooms);

    // Execute and fetch results
    $stmt->execute();
    $result = $stmt->get_result();
    $roomList = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['rooms' => $roomList]);
} else {
    echo json_encode(['rooms' => []]); // If no rooms available for user
}

$conn->close();
?>
