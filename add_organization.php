<?php
include 'config.php';

header('Content-Type: text/plain');

// Get form data
$org_name = $_POST['org_name'] ?? '';
$address = $_POST['address'] ?? '';
$head_name = $_POST['head_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$org_type = $_POST['org_type'] ?? '';

// Validate required fields
if (empty($org_name) || empty($address) || empty($head_name) || empty($phone) || empty($org_type)) {
    echo "error: missing required fields";
    exit;
}

// Use prepared statement for security
$sql = "INSERT INTO organizations (org_name, location, contact) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $org_name, $address, $phone);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>