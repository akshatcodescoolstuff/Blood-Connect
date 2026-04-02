<?php
$conn = new mysqli("localhost", "root", "", "blood_connect");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
