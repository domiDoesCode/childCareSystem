<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Validate JWT
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'error' => 'Authorization header missing']);
    exit;
}

list($bearer, $token) = explode(' ', $authHeader);
$decoded = validateJWT($token);
if (!$decoded) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'error' => 'Invalid token']);
    exit;
}

$childId = $_GET['child_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Validate Child ID
    if (!$childId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => 'Child ID is required']);
        exit;
    }

    // Fetch recent nappy entries with nappy type name
    $stmt = $conn->prepare("
        SELECT 
            nappy_entries.date_recorded, 
            nappy_types.name AS nappy_type
        FROM 
            nappy_entries
        LEFT JOIN 
            nappy_types ON nappy_entries.nappy_type_id = nappy_types.id
        WHERE 
            nappy_entries.child_id = ?
        ORDER BY 
            nappy_entries.date_recorded DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $childId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $nappyData = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'nappy' => $nappyData]);
    } else {
        echo json_encode(['status' => 'success', 'nappy' => []]);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST request: Add a new nappy entry
    $childId = $_POST['child_id'] ?? null;
    $nappyType = $_POST['nappy_type'] ?? null;

    if (!$childId || !$nappyType) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => 'Child ID and nappy type are required']);
        exit;
    }

    // Insert new nappy entry
    $stmt = $conn->prepare("INSERT INTO nappy_entries (child_id, nappy_type_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $childId, $nappyType);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Nappy entry added successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'error' => 'Failed to add nappy entry']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method']);
}

$conn->close();
