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
        SELECT time_in, time_out, is_absent
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
    $isAbsent = isset($_POST['is_absent']) ? (bool)$_POST['is_absent'] : null;

    if (!$childId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Child ID is required.']);
        exit;
    }

    try {
        // Check if attendance for the child already exists for today
        $stmt = $conn->prepare("
            SELECT id, time_in, time_out 
            FROM attendance 
            WHERE child_id = ? AND DATE(date) = CURDATE()
        ");
        $stmt->bind_param("i", $childId);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingAttendance = $result->fetch_assoc();

        if ($result->num_rows > 0) {
            // Update existing attendance entry
            $attendanceId = $existingAttendance['id'];

            if (!$timeIn) $timeIn = $existingAttendance['time_in']; // Retain existing time_in
            if (!$timeOut) $timeOut = $existingAttendance['time_out']; // Retain existing time_out

            $stmt = $conn->prepare("
                UPDATE attendance 
                SET time_in = ?, time_out = ?, is_absent = ?, date = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssii", $timeIn, $timeOut, $isAbsent, $attendanceId);
        } else {
            // Insert new attendance entry
            $stmt = $conn->prepare("
                INSERT INTO attendance (child_id, time_in, time_out, is_absent, date) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("issi", $childId, $timeIn, $timeOut, $isAbsent);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Attendance logged successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to log attendance']);
        }

        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update attendance', 'details' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
}
