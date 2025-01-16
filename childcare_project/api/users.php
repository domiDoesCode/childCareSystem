<?php
header('Content-Type: application/json');
require '../config/db.php';
require 'validate_jwt.php';

// Validate JWT and ensure the user is an admin
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization header missing']);
    exit;
}
list($bearer, $token) = explode(' ', $authHeader);
$decoded = validateJWT($token);
if (!$decoded || $decoded['role'] !== 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all users
    $stmt = $conn->prepare("SELECT id, email, role_id FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status' => 'success', 'users' => $users]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update a user
    $userId = $_POST['user_id'] ?? null;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $roleId = $_POST['role_id'] ?? null;

    if (!$email || !$password || !$role) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    if ($userId) {
        // Update user
        if ($password) {
            $stmt = $conn->prepare("UPDATE users SET email = ?, password = ?, role_id = ? WHERE id = ?");
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bind_param('ssii', $email, $hashedPassword, $roleId, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET email = ?, role_id = ? WHERE id = ?");
            $stmt->bind_param('sii', $email, $roleId, $userId);
        }
    } else {
        // Add new user
        $stmt = $conn->prepare("INSERT INTO users (email, password, role_id) VALUES (?, ?, ?)");
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param('ssi', $email, $hashedPassword, $roleId);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save user']);
    }
}
$conn->close();
?>
