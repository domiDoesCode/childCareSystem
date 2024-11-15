<?php
header('Content-Type: application/json'); // Set the content type to JSON

$host = "localhost"; // Hostname
$user = "root";      // Default XAMPP username
$password = "";      // Default XAMPP password is blank
$database = "childcare_db";  // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
