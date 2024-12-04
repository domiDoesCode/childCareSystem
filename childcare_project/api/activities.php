<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'error' => 'Authorization header missing']);
    exit;
}

list($bearer, $token) = explode(' ', $authHeader);
$decoded = validateJWT($token);

if (!$decoded) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'error' => 'Invalid or expired token']);
    exit;
}

$userId = $decoded['userId'] ?? null;
$roleId = $decoded['role'] ?? null;
$childId = $_GET['child_id'] ?? null;

// Validate Child ID
if (!$childId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'Child ID is required']);
    exit;
}

// Handle GET requests to fetch existing activity entries
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $conn->prepare("
        SELECT 
            activities.date_recorded, 
            activity_types.name AS activity_type, 
            GROUP_CONCAT(activity_definitions.name SEPARATOR ', ') AS activities
        FROM activities
        JOIN activity_types ON activities.activity_type_id = activity_types.id
        JOIN activities_activity ON activities.id = activities_activity.activities_id
        JOIN activity_definitions ON activities_activity.activity_id = activity_definitions.id
        WHERE activities.child_id = ?
        GROUP BY activities.id
        ORDER BY activities.date_recorded DESC
    ");
    $stmt->bind_param("i", $childId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $activitiesData = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'activities' => $activitiesData]);
    } else {
        echo json_encode(['status' => 'success', 'activities' => []]); // No data case
    }

    $stmt->close();
}
// Handle POST requests to add new activity entries
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && ($roleId == 1 || $roleId == 2)) {
    $childId = $_POST['child_id'] ?? null;
    $activityTypeId = $_POST['activity_type_id'] ?? null;
    $activityIds = $_POST['activity_ids'] ?? []; // This should be an array

    // Debugging: Log incoming POST data
    error_log("POST Data: " . print_r($_POST, true));

    if (!$childId || !$activityTypeId || empty($activityIds)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => 'Child ID, activity type, and activities are required']);
        exit;
    }

    // Start transaction to ensure consistency
    $conn->begin_transaction();
    try {
        // Insert into the activities table
        $stmt = $conn->prepare("INSERT INTO activities (child_id, activity_type_id, date_recorded) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $childId, $activityTypeId);
        $stmt->execute();
        $activitiesId = $stmt->insert_id; // Get the inserted activities ID
        $stmt->close();

        // Insert into activities_activity table
        $stmt = $conn->prepare("INSERT INTO activities_activity (activities_id, activity_id) VALUES (?, ?)");
        foreach ($activityIds as $activityId) {
            $activityId = (int)$activityId; // Ensure activityId is an integer
            $stmt->bind_param("ii", $activitiesId, $activityId);
            $stmt->execute();
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Activity entry added successfully']);
    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        error_log("Error adding activity entry: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'error' => 'Failed to add activity entry', 'details' => $e->getMessage()]);
    }
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'error' => 'Unauthorized or invalid request method']);
}

$conn->close();
