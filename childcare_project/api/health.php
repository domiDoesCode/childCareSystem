<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

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
$childId = $_GET['child_id'] ?? null;

if (!$childId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Child ID is required']);
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("
        SELECT 
            health_entries.date_recorded, 
            health_entries.temperature, 
            GROUP_CONCAT(DISTINCT symptoms.name SEPARATOR ', ') AS symptoms,
            GROUP_CONCAT(DISTINCT medications.name SEPARATOR ', ') AS medications
        FROM health_entries
        LEFT JOIN health_symptoms ON health_entries.id = health_symptoms.health_entry_id
        LEFT JOIN symptoms ON health_symptoms.symptom_id = symptoms.id
        LEFT JOIN health_medications ON health_entries.id = health_medications.health_entry_id
        LEFT JOIN medications ON health_medications.medication_id = medications.id
        WHERE health_entries.child_id = ?
        GROUP BY health_entries.id
        ORDER BY health_entries.date_recorded DESC
    ");
    $stmt->bind_param("i", $childId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $healthData = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'health' => $healthData]);
    } else {
        echo json_encode(['status' => 'success', 'health' => []]);
    }

    $stmt->close();
} 
// Handle POST requests
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($roleId == 1 || $roleId == 2)) { 
    $childId = $_POST['child_id'] ?? null;
    $temperature = $_POST['temperature'] ?? null;
    $symptomIds = $_POST['symptom_ids'] ?? [];
    $medicationIds = $_POST['medication_ids'] ?? [];

    if (!$childId || !$temperature || (empty($symptomIds) && empty($medicationIds))) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Child ID, temperature, and at least one symptom or medication are required']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Insert into health_entries table
        $stmt = $conn->prepare("INSERT INTO health_entries (child_id, temperature, date_recorded) VALUES (?, ?, NOW())");
        $stmt->bind_param("id", $childId, $temperature);
        $stmt->execute();
        $healthEntryId = $stmt->insert_id;
        $stmt->close();

        // Insert into health_symptoms table
        if (!empty($symptomIds)) {
            $stmt = $conn->prepare("INSERT INTO health_symptoms (health_entry_id, symptom_id) VALUES (?, ?)");
            foreach ($symptomIds as $symptomId) {
                $stmt->bind_param("ii", $healthEntryId, $symptomId);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Insert into health_medications table
        if (!empty($medicationIds)) {
            $stmt = $conn->prepare("INSERT INTO health_medications (health_entry_id, medication_id) VALUES (?, ?)");
            foreach ($medicationIds as $medicationId) {
                $stmt->bind_param("ii", $healthEntryId, $medicationId);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Health entry added successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add health entry', 'details' => $e->getMessage()]);
    }
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or invalid request method']);
}

$conn->close();
