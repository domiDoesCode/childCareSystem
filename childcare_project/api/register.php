<?php
session_start();
include '../config/db.php';  // Ensure correct path to the database connection

header('Content-Type: application/json'); // Set header for JSON output

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $defaultRole = 3;  // Default role is 'parent'

    // Ensure the database connection is still open
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL to insert the new user
    $sql = "INSERT INTO users (email, password, role_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if statement preparation failed
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query error: ' . $conn->error]);
        exit;
    }

    // Bind the parameters and execute
    $stmt->bind_param("ssi", $email, $hashedPassword, $defaultRole);
    if ($stmt->execute()) {
        // User registration successful, create session and cookie

        // Fetch the newly created user's ID
        $userId = $stmt->insert_id;

        // Create session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;

        // Generate a session token
        $sessionToken = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $sessionToken;

        // Set a cookie token (valid for 30 days)
        setcookie(
            'cookie_token',
            $sessionToken,
            [
                'expires' => time() + (86400 * 30), // 30 days
                'path' => '/',  // Available site-wide
                'secure' => true, // Send only over HTTPS
                'httponly' => true, // Prevent JavaScript access
                'samesite' => 'Strict' // SameSite policy
            ]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'redirect' => 'portal.html' // Include this in the JSON response
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User registration failed: ' . $stmt->error]);
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
