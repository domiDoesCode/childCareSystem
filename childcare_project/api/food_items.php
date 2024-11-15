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
    // Fetch all food items
    $query = "SELECT id, name FROM food_items ORDER BY name";
    $result = $conn->query($query);

    if ($result) {
        $foodItems = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'food_items' => $foodItems]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error fetching food items']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add a new food item
    $data = json_decode(file_get_contents('php://input'), true);
    $foodName = $data['name'] ?? null;

    if (!$foodName) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Food name is required']);
        exit;
    }

    // Insert food item
    $stmt = $conn->prepare("INSERT INTO food_items (name) VALUES (?)");
    $stmt->bind_param("s", $foodName);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Food item added']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error adding food item']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
