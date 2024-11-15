<?php
header('Content-Type: application/json');
require '../config/db.php'; // Database connection
require '../vendor/autoload.php'; // JWT library
require 'validate_jwt.php'; // JWT functions

use \Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        exit;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user data based on email
    $stmt = $conn->prepare("SELECT id, email, password, role_id FROM USERS WHERE email = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if user exists and password is correct
    if ($user && password_verify($password, $user['password'])) {
        $userId = $user['id'];
        $roleId = $user['role_id'];

        // Fetch room IDs based on role
        $rooms = [];
        if ($roleId == 3) { // Replace 3 with actual parent role ID
            $roomStmt = $conn->prepare("SELECT r.id FROM ROOMS r JOIN CHILDREN c ON r.id = c.room_id WHERE c.parent_id = ?");
            $roomStmt->bind_param("i", $userId);
            $roomStmt->execute();
            $roomResult = $roomStmt->get_result();
            while ($row = $roomResult->fetch_assoc()) {
                $rooms[] = (int)$row['id'];
            }
        } else {
            $roomStmt = $conn->prepare("SELECT id FROM ROOMS");
            $roomStmt->execute();
            $roomResult = $roomStmt->get_result();
            while ($row = $roomResult->fetch_assoc()) {
                $rooms[] = (int)$row['id'];
            }
        }

        // Generate JWT with user and room info
        $jwt = generateJWT($userId, $roleId, $rooms);
        
        echo json_encode([
            'status' => 'success',
            'token' => $jwt
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>
