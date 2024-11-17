<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Retrieve JWT token and validate
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

// Handle GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mealTypeId = $_GET['meal_type_id'] ?? null;

    if ($mealTypeId) {
        $stmt = $conn->prepare("
            SELECT fi.id, fi.name 
            FROM food_items fi
            INNER JOIN food_item_meal_type fimt ON fi.id = fimt.food_item_id
            WHERE fimt.meal_type_id = ?
        ");
        $stmt->bind_param("i", $mealTypeId);
    } else {
        $stmt = $conn->prepare("SELECT id, name FROM food_items");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $foodItems = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'food_items' => $foodItems]);
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foodName = $_POST['name'] ?? '';
    $mealTypeId = $_POST['meal_type_id'] ?? null;

    if (!$foodName) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => 'Food name and meal type are required']);
        exit;
    }

    // Start a transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // Insert the food item into the food_items table
        $stmt = $conn->prepare("INSERT INTO food_items (name) VALUES (?)");
        $stmt->bind_param("s", $foodName);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert food item: " . $stmt->error);
        }
        $foodItemId = $stmt->insert_id;
        $stmt->close();

        // If a meal type is specified, add a single link
        if ($mealTypeId) {
            $stmtLink = $conn->prepare("INSERT INTO food_item_meal_type (food_item_id, meal_type_id) VALUES (?, ?)");
            $stmtLink->bind_param("ii", $foodItemId, $mealTypeId);
            if (!$stmtLink->execute()) {
                throw new Exception("Failed to insert food item and meal type link: " . $stmtLink->error);
            }
            $stmtLink->close();
        } else {
            // If no meal type is specified, link the item to all meal types
            $stmtMealTypes = $conn->prepare("SELECT id FROM meal_types");
            $stmtMealTypes->execute();
            $mealTypesResult = $stmtMealTypes->get_result();
            $stmtMealTypes->close();

            if ($mealTypesResult->num_rows > 0) {
                $stmtLinkAll = $conn->prepare("INSERT INTO food_item_meal_type (food_item_id, meal_type_id) VALUES (?, ?)");
                while ($mealType = $mealTypesResult->fetch_assoc()) {
                    $mealTypeId = $mealType['id'];
                    $stmtLinkAll->bind_param("ii", $foodItemId, $mealTypeId);
                    if (!$stmtLinkAll->execute()) {
                        throw new Exception("Failed to link food item to meal type: " . $stmtLinkAll->error);
                    }
                }
                $stmtLinkAll->close();
            } else {
                throw new Exception("No meal types found to link the food item");
            }
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Food item added successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>
