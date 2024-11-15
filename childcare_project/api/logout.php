<?php
header('Content-Type: application/json');

// Return a successful logout response
echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
exit();
?>
