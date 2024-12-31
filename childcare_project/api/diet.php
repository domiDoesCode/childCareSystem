<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Retrieve and validate JWT
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

// Additional validation for Parent role
if ($roleId == 3) { // Parent role
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count
        FROM child_parent_links
        WHERE child_id = ? AND parent_id = ?
    ");
    $stmt->bind_param("ii", $childId, $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result['count'] == 0) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'error' => 'Access denied: This child is not linked to your account']);
        exit;
    }
}

// Handle GET requests to fetch existing diet entries
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("
        SELECT 
            diet.date_recorded, 
            meal_types.name AS meal_type, 
            GROUP_CONCAT(food_items.name SEPARATOR ', ') AS food_items
        FROM diet
        JOIN meal_types ON diet.meal_type_id = meal_types.id
        JOIN diet_food_items ON diet.id = diet_food_items.diet_id
        JOIN food_items ON diet_food_items.food_item_id = food_items.id
        WHERE diet.child_id = ?
        GROUP BY diet.id
        ORDER BY diet.date_recorded DESC
    ");
    $stmt->bind_param("i", $childId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dietData = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'diet' => $dietData]);
    } else {
        echo json_encode(['status' => 'success', 'diet' => []]); // No data case
    }

    $stmt->close();
}
// Handle POST requests to add new diet entries
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!in_array($roleId, [1, 2])) { // Only Admin (1) and Caregiver (2) can add diet entries
        http_response_code(403);
        echo json_encode(['status' => 'error', 'error' => 'Access denied: You are not allowed to add entries']);
        exit;
    }

    $childId = $_POST['child_id'] ?? null;
    $mealTypeId = $_POST['meal_type_id'] ?? null;
    $foodItemIds = $_POST['food_item_ids'] ?? []; // This should be an array

    if (!$childId || !$mealTypeId || empty($foodItemIds)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => 'Child ID, meal type, and food items are required']);
        exit;
    }

    // Start transaction to ensure consistency
    $conn->begin_transaction();
    try {
        // Insert into the diet table
        $stmt = $conn->prepare("INSERT INTO diet (child_id, meal_type_id, date_recorded) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $childId, $mealTypeId);
        $stmt->execute();
        $dietId = $stmt->insert_id; // Get the inserted diet ID
        $stmt->close();

        // Insert into diet_food_items table
        $stmt = $conn->prepare("INSERT INTO diet_food_items (diet_id, food_item_id) VALUES (?, ?)");
        foreach ($foodItemIds as $foodItemId) {
            $foodItemId = (int)$foodItemId; // Ensure foodItemId is an integer
            $stmt->bind_param("ii", $dietId, $foodItemId);
            $stmt->execute();
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Diet entry added successfully']);
    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        error_log("Error adding diet entry: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'error' => 'Failed to add diet entry', 'details' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'error' => 'Invalid request method']);
}

$conn->close();
