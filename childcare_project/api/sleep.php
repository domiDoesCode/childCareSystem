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

        if ($sleepEnd) {
            // Update sleep_end if a matching sleep_start exists
            $stmt = $conn->prepare("
                SELECT id FROM sleeping_entries
                WHERE child_id = ? AND sleep_end IS NULL AND DATE(sleep_start) = CURDATE()
            ");
            $stmt->bind_param('i', $childId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $sleepEntryId = $result->fetch_assoc()['id'];
                $stmt = $conn->prepare("
                    UPDATE sleeping_entries
                    SET 
                        sleep_end = ?,
                        duration = TIMESTAMPDIFF(MINUTE, sleep_start, ?)
                    WHERE id = ?
                ");
                $stmt->bind_param('ssi', $sleepEnd, $sleepEnd, $sleepEntryId);
            } else {
                // Prevent logging sleep_end without a matching sleep_start
                http_response_code(400);
                echo json_encode(['error' => 'No matching sleep start found for today.']);
                exit;
            }
        } elseif ($sleepStart) {
            // Check for ongoing sleep entry (sleep_start without sleep_end)
            $stmt = $conn->prepare("
            SELECT id FROM sleeping_entries
            WHERE child_id = ? AND sleep_end IS NULL AND DATE(sleep_start) = CURDATE()
            ");
            $stmt->bind_param('i', $childId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Ongoing sleep entry exists, prevent starting a new one
                http_response_code(400);
                echo json_encode(['error' => 'A sleep session is already in progress. Please end it before starting a new one.']);
                exit;
            }

            // Insert a new entry for sleep_start
            $stmt = $conn->prepare("
                INSERT INTO sleeping_entries (child_id, sleep_start)
                VALUES (?, ?)
            ");
            $stmt->bind_param('is', $childId, $sleepStart);
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
