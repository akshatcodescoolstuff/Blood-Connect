<?php
include 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Get form data
$org_name = trim($_POST['org_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$head_name = trim($_POST['head_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$org_type = $_POST['org_type'] ?? '';

// Validate required fields
if (empty($org_name) || empty($address) || empty($head_name) || empty($phone) || empty($org_type)) {
    $response['message'] = 'Missing required fields';
    echo json_encode($response);
    exit;
}

// Validate phone number (10 digits)
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $response['message'] = 'Please enter a valid 10-digit phone number';
    echo json_encode($response);
    exit;
}

// Validate email if provided
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address';
    echo json_encode($response);
    exit;
}

// Check if phone already exists
$checkStmt = $conn->prepare("SELECT id FROM organizations WHERE contact = ?");
$checkStmt->bind_param("s", $phone);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $response['message'] = 'This phone number is already registered';
    echo json_encode($response);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Insert into organizations table (using correct column names)
$sql = "INSERT INTO organizations (org_name, location, contact, head_name, email, org_type, verified) 
        VALUES (?, ?, ?, ?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $org_name, $address, $phone, $head_name, $email, $org_type);

if ($stmt->execute()) {
    $org_id = $stmt->insert_id;
    
    // Create user account for organization
    $temp_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    
    $userStmt = $conn->prepare("INSERT INTO users (name, email, password, role, organization_id) VALUES (?, ?, ?, 'organization', ?)");
    $userStmt->bind_param("sssi", $org_name, $email, $hashed_password, $org_id);
    
    if (!$userStmt->execute()) {
        // User insert failed but org insert succeeded - log error but still return success
        error_log("Failed to create user for organization: " . $userStmt->error);
    }
    $userStmt->close();
    
    $response['success'] = true;
    $response['message'] = 'Organization registered successfully! We will contact you within 48 hours.';
} else {
    $response['message'] = 'Database error: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
