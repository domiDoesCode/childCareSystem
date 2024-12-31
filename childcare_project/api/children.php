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

try {
    // Fetch a specific child by ID (e.g., for profile)
    if (isset($_GET['child_id'])) {
        $childId = (int)$_GET['child_id'];
        
        if ($roleId == 3) { // Parent can only fetch their own child
            $stmt = $conn->prepare("
                SELECT 
                    id, name, date_of_birth, allergies, photo 
                FROM children 
                WHERE id = ? AND parent_id = ?
            ");
            $stmt->bind_param("ii", $childId, $userId);
        } else {
            $stmt = $conn->prepare("
                SELECT 
                    id, name, date_of_birth, allergies, photo 
                FROM children 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $childId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $child = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'child' => $child]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Child not found or access denied']);
        }

        $stmt->close();
    } 
    // Fetch all children in a room
    else if (isset($_GET['room_id'])) {
        $roomId = (int)$_GET['room_id'];

        if ($roleId == 3) { // Parent can only fetch their own children
            $stmt = $conn->prepare("
                SELECT id, name, date_of_birth 
                FROM children 
                WHERE room_id = ? AND parent_id = ?
            ");
            $stmt->bind_param("ii", $roomId, $userId);
        } else { // Admins/Caregivers can fetch all children in a room
            $stmt = $conn->prepare("
                SELECT id, name, date_of_birth 
                FROM children 
                WHERE room_id = ?
            ");
            $stmt->bind_param("i", $roomId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $children = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['status' => 'success', 'children' => $children]);
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request. Specify room_id or child_id']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query error', 'details' => $e->getMessage()]);
}

$conn->close();
