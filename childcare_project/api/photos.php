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

$roleId = $decoded['role'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch child gallery photos
    if (isset($_GET['child_id']) && isset($_GET['type']) && $_GET['type'] === 'gallery') {
        $childId = $_GET['child_id'];

        // Validate child ID
        if (empty($childId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Child ID is required']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT photo, uploaded_at
            FROM child_gallery
            WHERE child_id = ?
            ORDER BY uploaded_at DESC
        ");
        $stmt->bind_param("i", $childId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $photos = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['status' => 'success', 'photos' => $photos]);
        } else {
            echo json_encode(['status' => 'success', 'photos' => []]); // No photos
        }

        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request or missing parameters']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Existing POST logic for uploading profile photos
    $childId = $_POST['child_id'] ?? null;
    if (!$childId || !isset($_FILES['photo'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Child ID and photo are required']);
        exit;
    }

    $photo = $_FILES['photo'];
    $targetDir = '../uploads/children/';
    $fileName = uniqid() . '_' . basename($photo['name']);
    $targetFilePath = $targetDir . $fileName;

    // Validate file upload
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    if (!in_array(strtolower($fileType), $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }

    // Upload file to server
    if (move_uploaded_file($photo['tmp_name'], $targetFilePath)) {
        // Update the child's profile photo in the database
        $stmt = $conn->prepare("UPDATE children SET photo = ? WHERE id = ?");
        $stmt->bind_param("si", $fileName, $childId);
        if ($stmt->execute()) {
            // Add the photo to the gallery table
            $galleryStmt = $conn->prepare("INSERT INTO child_gallery (child_id, photo) VALUES (?, ?)");
            $galleryStmt->bind_param("is", $childId, $fileName);
            $galleryStmt->execute();
            $galleryStmt->close();

            echo json_encode(['status' => 'success', 'photo' => $fileName]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update profile photo']);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>
