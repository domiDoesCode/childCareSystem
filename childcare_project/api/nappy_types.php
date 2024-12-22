<?php
header('Content-Type: application/json');
require '../config/db.php';

// Handle GET requests to fetch nappy types
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, name FROM nappy_types ORDER BY name";
    $result = $conn->query($query);

    if ($result) {
        $nappyTypes = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'nappy_types' => $nappyTypes]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error fetching nappy types']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
