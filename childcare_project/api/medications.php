<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, name FROM medications ORDER BY name";
    $result = $conn->query($query);

    if ($result) {
        $medications = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'medications' => $medications]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error fetching medications']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicationName = $_POST['name'] ?? '';

    if (!$medicationName) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Medication name is required']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO medications (name) VALUES (?)");
    $stmt->bind_param("s", $medicationName);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Medication added successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add medication']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
