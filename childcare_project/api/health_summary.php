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
    // Detailed view: Fetch all health entries for today
    $query = "
        SELECT 
            children.name AS child_name,
            health_entries.temperature,
            GROUP_CONCAT(DISTINCT symptoms.name SEPARATOR ', ') AS symptoms,
            GROUP_CONCAT(DISTINCT medications.name SEPARATOR ', ') AS medications,
            health_entries.date_recorded 
        FROM health_entries
        JOIN children ON health_entries.child_id = children.id
        LEFT JOIN health_symptoms ON health_entries.id = health_symptoms.health_entry_id
        LEFT JOIN symptoms ON health_symptoms.symptom_id = symptoms.id
        LEFT JOIN health_medications ON health_entries.id = health_medications.health_entry_id
        LEFT JOIN medications ON health_medications.medication_id = medications.id
        WHERE DATE(health_entries.date_recorded) = CURDATE()
    ";

    // Restrict data for parents
    if ($roleId == 3) {
        $query .= " AND children.parent_id = ?";
    }

    $query .= " GROUP BY health_entries.id ORDER BY health_entries.date_recorded DESC";
    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $healthSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $healthSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} else {
    // Summary view: Count of entries and temperature ranges
    $query = "
        SELECT 
            COUNT(*) AS total_entries,
            MIN(temperature) AS min_temperature,
            MAX(temperature) AS max_temperature
        FROM health_entries
        WHERE DATE(date_recorded) = CURDATE()
    ";

    // Restrict data for parents
    if ($roleId == 3) {
        $query .= " AND child_id IN (SELECT id FROM children WHERE parent_id = ?)";
    }

    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $healthSummary = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'summary' => $healthSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
