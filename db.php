<?php
$servername = "localhost";
$username = "root";
$password = "root";
$db = 'bank';

// Create connection
$conn = new mysqli($servername, $username, $password, $db);
// Check connection
if ($conn->connect_error) {
    echo "disconnect";
    die("Connection failed: " . $conn->connect_error);
}
?>