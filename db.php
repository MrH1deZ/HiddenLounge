<?php
$servername = "localhost";
$username = "fxinvrev_tester";
$password = "Tester123@!Test";
$dbname = "fxinvrev_tester";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>