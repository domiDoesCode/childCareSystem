<?php
require '../vendor/autoload.php'; // Use Firebase JWT library or similar
use \Firebase\JWT\JWT;

$secretKey = 'your_secret_key';

function generateJWT($userId, $role, $rooms) {
    global $secretKey;
    
    $payload = [
        'iss' => 'childcare.com',
        'iat' => time(),
        'exp' => time() + (60 * 60), // 1 hour expiration
        'data' => [
            'userId' => $userId,
            'role' => $role,
            'rooms' => $rooms
        ]
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}

function validateJWT($token) {
    global $secretKey;
    
    try {
        $decoded = JWT::decode($token, new \Firebase\JWT\Key($secretKey, 'HS256'));
        return (array) $decoded->data;
    } catch (Exception $e) {
        return false; // Invalid token
    }
}

// Only process the Authorization header if this file is accessed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    header('Content-Type: application/json');
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if ($authHeader) {
        list($bearer, $token) = explode(' ', $authHeader);
        $decodedData = validateJWT($token);

        if ($decodedData) {
            echo json_encode(['status' => 'valid', 'data' => $decodedData]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'invalid', 'message' => 'Invalid or expired token']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Authorization header missing']);
    }
}
?>
