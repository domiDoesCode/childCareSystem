<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Validate JWT
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authorization header missing']);
    exit;
}

list($bearer, $token) = explode(' ', $authHeader);
$decoded = validateJWT($token);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
    exit;
}

$userId = $decoded['userId'] ?? null;
$roleId = $decoded['role'] ?? null;

if (isset($_GET['details']) && $_GET['details'] === 'true') {
    // Detailed view: Fetch all sleep entries for today
    $query = "
        SELECT 
            children.name AS child_name,
            sleeping_entries.sleep_start,
            sleeping_entries.sleep_end,
            sleeping_entries.duration
        FROM sleeping_entries
        JOIN children ON sleeping_entries.child_id = children.id
        WHERE DATE(sleeping_entries.sleep_start) = CURDATE()
    ";

    // Restrict data for parents
    if ($roleId == 3) {
        $query .= " AND children.parent_id = ?";
    }

    $query .= " ORDER BY sleeping_entries.sleep_start DESC";
    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $sleepSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $sleepSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} else {
    // Summary view: Count of sleep entries grouped by duration
    $query = "
        SELECT 
            children.name AS child_name,
            COUNT(*) AS total_entries,
            SUM(sleeping_entries.duration) AS total_duration
        FROM sleeping_entries
        JOIN children ON sleeping_entries.child_id = children.id
        WHERE DATE(sleeping_entries.sleep_start) = CURDATE()
    ";

    // Restrict data for parents
    if ($roleId == 3) {
        $query .= " AND sleeping_entries.child_id IN (SELECT id FROM children WHERE parent_id = ?)";
    }

    $query .= " GROUP BY children.name";
    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $sleepSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $sleepSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
