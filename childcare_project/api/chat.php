<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Validate JWT
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization header missing']);
    exit;
}

list($bearer, $token) = explode(' ', $authHeader);
$decoded = validateJWT($token);
if (!$decoded) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

$userId = $decoded['userId'] ?? null;
$roleId = $decoded['role'] ?? null;

// Handle GET requests to fetch chat messages
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $roomId = $_GET['room_id'] ?? null;
    $parentId = $_GET['parent_id'] ?? null;
    $type = $_GET['type'] ?? null; // "room" or "private"

    if (!$roomId) {
        http_response_code(400);
        echo json_encode(['error' => 'Room ID is required']);
        exit;
    }

    if ($type === 'room') {
        // Fetch room chat messages
        $stmt = $conn->prepare("
            SELECT room_chat.message, room_chat.photo, room_chat.sent_at, users.email AS sender_name
            FROM room_chat
            JOIN users ON room_chat.sender_id = users.id
            WHERE room_chat.room_id = ?
            ORDER BY room_chat.sent_at ASC
        ");
        $stmt->bind_param("i", $roomId);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request parameters']);
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'success', 'data' => []]);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST requests to send messages
    $type = $_POST['type'] ?? null; // "room" or "private"
    $roomId = $_POST['room_id'] ?? null;
    $message = $_POST['message'] ?? null;
    $photo = $_FILES['photo']['name'] ?? null;

    if (!$roomId) {
        http_response_code(400);
        echo json_encode(['error' => 'Room ID is required']);
        exit;
    }

    if (!$message && !$photo) {
        http_response_code(400);
        echo json_encode(['error' => 'Message or photo is required']);
        exit;
    }

    // Handle photo upload
    $photoPath = null;
    if ($photo) {
        $targetDir = "../uploads/chat/";
        $photoName = time() . "_" . basename($_FILES['photo']['name']);
        $photoPath = $targetDir . $photoName;

        // Validate and move the uploaded file
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to upload photo']);
            exit;
        }

        $photoPath = basename($photoPath); // Store only the filename in the database
    }

    if ($type === 'room') {
        $stmt = $conn->prepare("
            INSERT INTO room_chat (room_id, sender_id, message, photo)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $roomId, $userId, $message, $photoPath);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request parameters']);
        exit;
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Message sent']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send message']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
