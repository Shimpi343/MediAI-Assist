<?php
$host = "localhost";
$user = "root";
$password = "040301@Shilki1"; // Your MySQL password
$dbname = "doctor_db";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


