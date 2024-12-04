<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Retrieve and validate JWT token
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch activities by activity type if specified, otherwise fetch all activities
    $activityTypeId = $_GET['activity_type_id'] ?? null;

    if ($activityTypeId) {
        $stmt = $conn->prepare("
            SELECT ad.id, ad.name 
            FROM activity_definitions ad
            JOIN activity_type_links atl ON ad.id = atl.activity_id
            WHERE atl.activity_type_id = ?
        ");
        $stmt->bind_param("i", $activityTypeId);
    } else {
        $stmt = $conn->prepare("SELECT id, name FROM activity_definitions ORDER BY name");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $activities = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'activities' => $activities]);
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new activity and link it to activity types
    $activityName = $_POST['name'] ?? '';
    $activityTypeId = $_POST['activity_type_id'] ?? null;

    if (!$activityName) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Activity name is required']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Insert the activity into activity_definitions
        $stmt = $conn->prepare("INSERT INTO activity_definitions (name) VALUES (?)");
        $stmt->bind_param("s", $activityName);
        $stmt->execute();
        $activityId = $stmt->insert_id;
        $stmt->close();

        // Link the activity to the specified activity type or to all types
        if ($activityTypeId) {
            $stmtLink = $conn->prepare("INSERT INTO activity_type_links (activity_id, activity_type_id) VALUES (?, ?)");
            $stmtLink->bind_param("ii", $activityId, $activityTypeId);
            $stmtLink->execute();
            $stmtLink->close();
        } else {
            // Link to all activity types
            $stmtFetchTypes = $conn->prepare("SELECT id FROM activity_types");
            $stmtFetchTypes->execute();
            $resultTypes = $stmtFetchTypes->get_result();
            $stmtFetchTypes->close();

            if ($resultTypes->num_rows > 0) {
                $stmtLinkAll = $conn->prepare("INSERT INTO activity_type_links (activity_id, activity_type_id) VALUES (?, ?)");
                while ($type = $resultTypes->fetch_assoc()) {
                    $activityTypeId = $type['id'];
                    $stmtLinkAll->bind_param("ii", $activityId, $activityTypeId);
                    $stmtLinkAll->execute();
                }
                $stmtLinkAll->close();
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Activity added successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
