<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Validate JWT
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

// Check for details request
if (isset($_GET['details']) && $_GET['details'] === 'true') {
    // Fetch detailed diet entries
    $query = "
        SELECT 
            children.name AS child_name,
            meal_types.name AS meal_type,
            food_items.name AS food_item, 
            diet_food_items.diet_id,
            diet.date_recorded 
        FROM diet
        JOIN children ON diet.child_id = children.id
        JOIN meal_types ON diet.meal_type_id = meal_types.id
        JOIN diet_food_items ON diet.id = diet_food_items.diet_id
        JOIN food_items ON diet_food_items.food_item_id = food_items.id
        WHERE DATE(diet.date_recorded) = CURDATE()
    ";

    // Add restriction for parent role
    if ($roleId == 3) { // Parent role
        $query .= " AND children.parent_id = ?";
    }

    $query .= " ORDER BY diet.date_recorded DESC";

    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dietSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $dietSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch summary diet entries (default behavior)
    $query = "
        SELECT 
            meal_types.name AS meal_type, 
            COUNT(*) AS total_entries
        FROM 
            diet
        JOIN 
            meal_types ON diet.meal_type_id = meal_types.id
        WHERE 
            DATE(diet.date_recorded) = CURDATE()
    ";

    // Add restriction for parent role
    if ($roleId == 3) {
        $query .= " AND diet.child_id IN (SELECT id FROM children WHERE parent_id = ?)";
    }

    $query .= " GROUP BY meal_types.name";

    $stmt = $conn->prepare($query);

    if ($roleId == 3) {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dietSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $dietSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
?>
