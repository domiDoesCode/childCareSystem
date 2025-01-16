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

$childId = $_POST['child_id'] ?? null;
$sleepStart = $_POST['sleep_start'] ?? null;
$sleepEnd = $_POST['sleep_end'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$childId) {
        http_response_code(400);
        echo json_encode(['error' => 'Child ID is required']);
        exit;
    }

    try {
        // Validate datetime inputs
        $sleepStart = $sleepStart ? date('Y-m-d H:i:s', strtotime($sleepStart)) : null;
        $sleepEnd = $sleepEnd ? date('Y-m-d H:i:s', strtotime($sleepEnd)) : null;

        // Check if an entry exists for today
        $stmt = $conn->prepare("
            SELECT id FROM sleeping_entries 
            WHERE child_id = ? AND DATE(sleep_start) = CURDATE()
        ");
        $stmt->bind_param('i', $childId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing entry
            $sleepEntryId = $result->fetch_assoc()['id'];
            $stmt = $conn->prepare("
                UPDATE sleeping_entries 
                SET 
                    sleep_start = COALESCE(?, sleep_start),
                    sleep_end = COALESCE(?, sleep_end),
                    duration = CASE
                        WHEN sleep_start IS NOT NULL AND COALESCE(?, sleep_end) IS NOT NULL
                        THEN TIMESTAMPDIFF(MINUTE, sleep_start, COALESCE(?, sleep_end))
                        ELSE NULL
                    END
                WHERE id = ?
            ");
            $stmt->bind_param('sssii', $sleepStart, $sleepEnd, $sleepEnd, $sleepEnd, $sleepEntryId);
        } else {
            // Insert a new entry
            $stmt = $conn->prepare("
                INSERT INTO sleeping_entries (child_id, sleep_start, sleep_end, duration) 
                VALUES (?, ?, ?, CASE
                    WHEN ? IS NOT NULL AND ? IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, ?, ?)
                    ELSE NULL
                END)
            ");
            $stmt->bind_param('issssss', $childId, $sleepStart, $sleepEnd, $sleepStart, $sleepEnd, $sleepStart, $sleepEnd);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Sleep entry logged successfully.']);
        } else {
            throw new Exception('Failed to log sleep entry.');
        }
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all sleep entries for the child
    $childId = $_GET['child_id'] ?? null;
    if (!$childId) {
        http_response_code(400);
        echo json_encode(['error' => 'Child ID is required']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT sleep_start, sleep_end, duration 
        FROM sleeping_entries 
        WHERE child_id = ?
        ORDER BY sleep_start DESC
    ");
    $stmt->bind_param('i', $childId);
    $stmt->execute();
    $result = $stmt->get_result();

    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }

    if (count($entries) > 0) {
        echo json_encode(['status' => 'success', 'sleep' => $entries]);
    } else {
        echo json_encode(['status' => 'success', 'sleep' => []]);
    }
    $stmt->close();
}
?>
