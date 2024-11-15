<?php
header('Content-Type: application/json');
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, name FROM meal_types ORDER BY name";
    $result = $conn->query($query);

    if ($result) {
        $mealTypes = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'meal_types' => $mealTypes]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error fetching meal types']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
$conn->close();
?>
