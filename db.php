<?php
$servername = "localhost";
$username = "Username";
$password = "Password";
$dbname = "DBname";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
