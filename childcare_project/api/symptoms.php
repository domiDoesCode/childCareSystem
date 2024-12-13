<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, name FROM symptoms ORDER BY name";
    $result = $conn->query($query);

    if ($result) {
        $symptoms = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'symptoms' => $symptoms]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error fetching symptoms']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $symptomName = $_POST['name'] ?? '';

    if (!$symptomName) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Symptom name is required']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO symptoms (name) VALUES (?)");
    $stmt->bind_param("s", $symptomName);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Symptom added successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add symptom']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>
