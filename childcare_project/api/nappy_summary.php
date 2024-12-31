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
    // Detailed view: Fetch all nappy entries for today
    $query = "
        SELECT 
            children.name AS child_name,
            nappy_types.name AS nappy_type,
            nappy_entries.date_recorded 
        FROM nappy_entries
        JOIN children ON nappy_entries.child_id = children.id
        JOIN nappy_types ON nappy_entries.nappy_type_id = nappy_types.id
        WHERE DATE(nappy_entries.date_recorded) = CURDATE()
    ";

    // Restrict data for parents
    if ($roleId == 3) {
        $query .= " AND children.parent_id = ?";
    }

    $query .= " ORDER BY nappy_entries.date_recorded DESC";
    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $nappySummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $nappySummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} else {
    // Summary view: Count of entries grouped by nappy type
    $query = "
        SELECT 
            nappy_types.name AS nappy_type, 
            COUNT(*) AS total_entries
        FROM nappy_entries
        JOIN nappy_types ON nappy_entries.nappy_type_id = nappy_types.id
        WHERE DATE(nappy_entries.date_recorded) = CURDATE()
    ";

    // Restrict data for parents
    if ($roleId == 3) {
        $query .= " AND nappy_entries.child_id IN (SELECT id FROM children WHERE parent_id = ?)";
    }

    $query .= " GROUP BY nappy_types.name";
    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $nappySummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $nappySummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
