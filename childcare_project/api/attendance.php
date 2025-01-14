<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Validate JWT
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
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

// Extract child ID and user role
$userId = $decoded['userId'];
$roleId = $decoded['role'];
$childId = $_GET['child_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch attendance for a child
    if (!$childId) {
        http_response_code(400);
        echo json_encode(['error' => 'Child ID is required']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT time_in, time_out 
        FROM attendance 
        WHERE child_id = ? AND date = CURDATE()
    ");
    $stmt->bind_param('i', $childId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attendance = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'attendance' => $attendance]);
    } else {
        echo json_encode(['status' => 'success', 'attendance' => null]); // No attendance logged yet
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log or update attendance
    $childId = $_POST['child_id'] ?? null;
    $timeIn = $_POST['time_in'] ?? null;
    $timeOut = $_POST['time_out'] ?? null;

    if (!$childId || (!$timeIn && !$timeOut)) {
        http_response_code(400);
        echo json_encode(['error' => 'Child ID and at least one time (Time In or Time Out) are required']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO attendance (child_id, date, time_in, time_out)
        VALUES (?, CURDATE(), ?, ?)
        ON DUPLICATE KEY UPDATE 
        time_in = COALESCE(VALUES(time_in), time_in),
        time_out = COALESCE(VALUES(time_out), time_out)
    ");
    $stmt->bind_param('iss', $childId, $timeIn, $timeOut);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Attendance logged successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to log attendance']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
